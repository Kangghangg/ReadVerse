<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminOrderController extends Controller
{
    public function index(Request $request): View
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $search = $request->query('search');
        $paymentMethod = $request->query('payment_method');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = Order::with(['user', 'ebook.category', 'ebook.author']);

        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($search !== null && trim($search) !== '') {
            $keyword = trim($search);
            $query->where(function ($q) use ($keyword) {
                $q->where('order_code', 'like', "%{$keyword}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$keyword}%")->orWhere('email', 'like', "%{$keyword}%"))
                    ->orWhereHas('ebook', fn ($eq) => $eq->withTrashed()->where('title', 'like', "%{$keyword}%"));
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        $totalRevenue = (float) Order::where('status', 'completed')->sum('amount');
        $totalOrders = Order::count();
        $uniqueBuyers = Order::distinct('user_id')->count('user_id');
        $avgOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders) : 0;

        $stats = [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'unique_buyers' => $uniqueBuyers,
            'avg_order_value' => $avgOrderValue,
        ];

        // Du lieu Bieu do 1: Doanh thu 10 ngay co giao dich gan nhat
        $allCompleted = Order::with('ebook.category')
            ->where('status', 'completed')
            ->orderBy('created_at')
            ->get();

        $dailyMap = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('d/m');
            $dailyMap[$d] = ['revenue' => 0, 'count' => 0];
        }
        foreach ($allCompleted as $ord) {
            $d = $ord->created_at ? $ord->created_at->format('d/m') : now()->format('d/m');
            if (!isset($dailyMap[$d])) {
                $dailyMap[$d] = ['revenue' => 0, 'count' => 0];
            }
            $dailyMap[$d]['revenue'] += (float) $ord->amount;
            $dailyMap[$d]['count'] += 1;
        }
        $dailyMap = array_slice($dailyMap, -10, 10, true);

        $chartDailyLabels = array_keys($dailyMap);
        $chartDailyRevenue = array_values(array_map(fn ($v) => $v['revenue'], $dailyMap));
        $chartDailyCount = array_values(array_map(fn ($v) => $v['count'], $dailyMap));

        // Du lieu Bieu do 2: Doanh thu theo Danh muc the loai
        $catMap = [];
        foreach ($allCompleted as $ord) {
            $catName = $ord->ebook?->category?->name ?? 'Khác';
            $catMap[$catName] = ($catMap[$catName] ?? 0) + (float) $ord->amount;
        }
        if (empty($catMap)) {
            $catMap = ['Chưa có dữ liệu' => 0];
        }
        arsort($catMap);
        $chartCategoryLabels = array_keys($catMap);
        $chartCategoryValues = array_values($catMap);

        return view('admin.orders', compact(
            'orders',
            'stats',
            'search',
            'paymentMethod',
            'dateFrom',
            'dateTo',
            'chartDailyLabels',
            'chartDailyRevenue',
            'chartDailyCount',
            'chartCategoryLabels',
            'chartCategoryValues'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $fileName = 'ReadVerse_BaoCao_DoanhThu_' . now()->format('Ymd_His') . '.csv';
        $orders = Order::with(['user', 'ebook.category', 'ebook.author'])
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            // Ghi UTF-8 BOM de Microsoft Excel hien thi tieng Viet co dau chuan 100%
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'STT',
                'Mã Đơn Hàng',
                'Ngày Giao Dịch',
                'Tên Độc Giả',
                'Email Độc Giả',
                'Tên Sách Điện Tử',
                'Tác Giả',
                'Thể Loại',
                'Phương Thức Thanh Toán',
                'Số Tiền (VNĐ)',
                'Trạng Thái',
            ]);

            $total = 0;
            foreach ($orders as $idx => $order) {
                $total += (float) $order->amount;
                fputcsv($handle, [
                    $idx + 1,
                    $order->order_code,
                    $order->created_at?->format('d/m/Y H:i:s'),
                    $order->user?->name ?? 'Khách hàng',
                    $order->user?->email ?? 'N/A',
                    $order->ebook?->title ?? 'Sách đã xóa',
                    $order->ebook?->author?->name ?? 'ReadVerse',
                    $order->ebook?->category?->name ?? 'Tổng hợp',
                    strtoupper($order->payment_method ?? 'VIETQR'),
                    number_format((float) $order->amount, 0, '', ''),
                    $order->status === 'completed' ? 'Thành công' : $order->status,
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, [
                '', 'TỔNG CỘNG DOANH THU', '', '', '', '', '', '',
                count($orders) . ' đơn hàng',
                number_format($total, 0, '', ''),
                'VNĐ',
            ]);

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
