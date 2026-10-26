<?php

namespace App\Http\Controllers\Reader;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Ebook;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $rawItems = CartItem::with(['ebook.author', 'ebook.category'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $cartItems = collect();
        foreach ($rawItems as $item) {
            if (!$item->ebook || $item->ebook->status !== 'published' || $item->ebook->isUnlockedBy($user)) {
                $item->delete();
                continue;
            }
            $cartItems->push($item);
        }

        $totalAmount = (float) $cartItems->sum(fn ($item) => (float) $item->ebook->price);
        $transferMemo = 'READVERSE CART U' . $user->id . ' ' . now()->format('Hi');
        $vietQrUrl = 'https://img.vietqr.io/image/MB-0901234567-compact2.png?amount=' . (int) $totalAmount . '&addInfo=' . urlencode($transferMemo) . '&accountName=' . urlencode('THU VIEN READVERSE');

        return view('reader.cart', compact(
            'cartItems',
            'totalAmount',
            'transferMemo',
            'vietQrUrl'
        ));
    }

    public function add(Request $request, int $ebookId): JsonResponse|RedirectResponse
    {
        $ebook = Ebook::where('status', 'published')->findOrFail($ebookId);
        $user = $request->user();

        if ($ebook->isUnlockedBy($user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'already_unlocked' => true,
                    'read_url' => route('ebooks.read', $ebook->slug),
                ]);
            }
            return redirect()->route('ebooks.read', $ebook->slug);
        }

        CartItem::firstOrCreate([
            'user_id' => $user->id,
            'ebook_id' => $ebook->id,
        ]);

        $cartCount = CartItem::where('user_id', $user->id)->count();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'in_cart' => true,
                'ebook_id' => $ebook->id,
                'cart_count' => $cartCount,
                'cart_url' => route('cart.index'),
                'message' => "Đã thêm \"{$ebook->title}\" vào giỏ hàng!",
            ]);
        }

        return redirect()->route('cart.index')
            ->with('success', "Đã thêm \"{$ebook->title}\" vào giỏ hàng của bạn.");
    }

    public function remove(Request $request, int $ebookId): JsonResponse|RedirectResponse
    {
        CartItem::where('user_id', $request->user()->id)
            ->where('ebook_id', $ebookId)
            ->delete();

        return redirect()->route('cart.index')
            ->with('success', 'Đã xóa sách khỏi giỏ hàng.');
    }

    public function clear(Request $request): RedirectResponse
    {
        CartItem::where('user_id', $request->user()->id)->delete();

        return redirect()->route('cart.index')
            ->with('success', 'Đã làm trống giỏ hàng.');
    }

    public function checkout(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $cartItems = CartItem::with('ebook')->where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $batchPrefix = 'RV-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(3));
        $unlockedIds = [];
        $idx = 1;

        foreach ($cartItems as $item) {
            if (!$item->ebook) {
                continue;
            }
            $ebook = $item->ebook;
            $unlockedIds[] = $ebook->id;

            Order::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'ebook_id' => $ebook->id,
                    'status' => 'completed',
                ],
                [
                    'order_code' => $batchPrefix . '-' . str_pad((string) $idx, 2, '0', STR_PAD_LEFT),
                    'amount' => $ebook->price,
                    'payment_method' => $request->input('payment_method', 'qr_transfer'),
                ]
            );
            $idx++;
        }

        if (!empty($unlockedIds)) {
            $user->unlockedEbooks()->syncWithoutDetaching($unlockedIds);
        }

        CartItem::where('user_id', $user->id)->delete();

        return redirect(url('/my-library?tab=my_books'))
            ->with('success', 'Thanh toán thành công! Đã mở khóa ' . count($unlockedIds) . ' tác phẩm vào Tủ sách của bạn.');
    }
}
