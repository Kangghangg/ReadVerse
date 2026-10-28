<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Author;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.ebooks.pending');
        }

        $profile = $user->profile()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'bio' => $user->isAuthor() ? 'Tác giả trên nền tảng sách điện tử ReadVerse.' : 'Độc giả yêu sách tại ReadVerse.',
                'phone' => '',
                'address' => '',
                'website' => '',
            ]
        );

        $recentOrders = Order::with('ebook.author')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();

        if ($user->isAuthor()) {
            $authorEbooks = $user->ebooks()->get();
            $stats = [
                'total_uploaded' => $authorEbooks->count(),
                'published_count' => $authorEbooks->where('status', 'published')->count(),
                'pending_count' => $authorEbooks->where('status', 'pending')->count(),
                'rejected_count' => $authorEbooks->where('status', 'draft')->count(),
                'total_views' => (int) $authorEbooks->sum('view_count'),
                'unlocked_count' => $user->unlockedEbooks()->count(),
            ];
        } else {
            $stats = [
                'reading_count' => $user->readingHistories()->count(),
                'favorites_count' => $user->bookmarks()->where('status', 'published')->count(),
                'unlocked_count' => $user->unlockedEbooks()->count(),
                'reviews_count' => $user->reviews()->count(),
            ];
        }

        return view('profile.edit', compact('user', 'profile', 'stats', 'recentOrders'));
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.ebooks.pending');
        }

        $validated = $request->validated();
        $oldName = $user->name;

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $newAvatarPath = null;

        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            Storage::disk('public')->makeDirectory('avatars');

            $avatarFile = $request->file('avatar');
            $ext = strtolower($avatarFile->getClientOriginalExtension()) ?: 'jpg';
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'], true)) {
                $ext = 'jpg';
            }
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $ext;
            $newAvatarPath = $avatarFile->storeAs('avatars', $filename, 'public');
        } elseif (!empty($validated['avatar_base64']) && preg_match('/^data:image\/(\w+);base64,/', $validated['avatar_base64'], $matches)) {
            $ext = strtolower($matches[1] ?? 'jpg');
            if ($ext === 'jpeg') {
                $ext = 'jpg';
            }
            if (!in_array($ext, ['jpg', 'png', 'webp', 'gif'], true)) {
                $ext = 'jpg';
            }
            $base64Data = substr($validated['avatar_base64'], strpos($validated['avatar_base64'], ',') + 1);
            $binary = base64_decode($base64Data, true);
            if ($binary !== false && strlen($binary) > 0 && strlen($binary) <= 10 * 1024 * 1024) {
                Storage::disk('public')->makeDirectory('avatars');
                $relPath = 'avatars/avatar_' . $user->id . '_' . time() . '.' . $ext;
                Storage::disk('public')->put($relPath, $binary);
                $newAvatarPath = $relPath;
            }
        }

        if ($newAvatarPath) {
            if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
                Storage::disk('public')->delete($user->avatar);
                $oldPublicFile = public_path('storage/' . ltrim($user->avatar, '/'));
                if (is_file($oldPublicFile) && !is_link(public_path('storage'))) {
                    @unlink($oldPublicFile);
                }
            }

            $user->avatar = $newAvatarPath;
            $this->syncToPublicStorage($newAvatarPath);
        }

        $user->save();

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'website' => $validated['website'] ?? null,
                'bio' => $validated['bio'] ?? null,
            ]
        );

        if ($user->isAuthor()) {
            $authorRecord = Author::whereIn('name', array_unique([$oldName, $user->name]))->first();
            if ($authorRecord) {
                $authorUpdate = ['name' => $user->name];
                if (!empty($validated['bio'])) {
                    $authorUpdate['bio'] = $validated['bio'];
                }
                if ($user->avatar) {
                    $authorUpdate['avatar'] = $user->avatar;
                }
                $authorRecord->update($authorUpdate);
            }
        }

        return Redirect::route('profile.edit')
            ->with('status', 'profile-updated')
            ->with('success', 'Đã cập nhật hồ sơ cá nhân thành công.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    protected function syncToPublicStorage(string $relativePath): void
    {
        $publicStorageRoot = public_path('storage');
        if (is_link($publicStorageRoot)) {
            return;
        }

        $sourcePath = storage_path('app/public/' . ltrim($relativePath, '/\\'));
        if (!is_file($sourcePath)) {
            return;
        }

        $destPath = $publicStorageRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relativePath, '/\\'));
        $destDir = dirname($destPath);

        if (!is_dir($destDir)) {
            @mkdir($destDir, 0775, true);
        }

        @copy($sourcePath, $destPath);
    }
}
