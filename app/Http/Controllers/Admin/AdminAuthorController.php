<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminAuthorController extends Controller
{
    public function index(Request $request): View
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $search = $request->query('search');
        $query = Author::withCount('ebooks');

        if ($search !== null && trim($search) !== '') {
            $keyword = trim($search);
            $query->where('name', 'like', "%{$keyword}%")
                ->orWhere('bio', 'like', "%{$keyword}%");
        }

        $authors = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.authors', compact('authors', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'name.required' => 'Vui lòng nhập tên tác giả.',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('authors/avatars', 'public');
        }

        Author::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::lower(Str::random(4)),
            'bio' => $validated['bio'] ?? null,
            'avatar' => $avatarPath,
        ]);

        return back()->with('success', 'Đã thêm tác giả mới thành công.');
    }

    public function update(Request $request, Author $author): RedirectResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . $author->id,
            'bio' => $validated['bio'] ?? null,
        ];

        if ($request->hasFile('avatar')) {
            if ($author->avatar && !str_starts_with($author->avatar, 'http')) {
                Storage::disk('public')->delete($author->avatar);
            }
            $payload['avatar'] = $request->file('avatar')->store('authors/avatars', 'public');
        }

        $author->update($payload);

        return back()->with('success', 'Đã cập nhật thông tin tác giả.');
    }

    public function destroy(Author $author): RedirectResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        if ($author->avatar && !str_starts_with($author->avatar, 'http')) {
            Storage::disk('public')->delete($author->avatar);
        }

        $author->delete();

        return back()->with('success', 'Đã xóa tác giả cùng các tác phẩm liên quan.');
    }
}
