<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCategoryController extends Controller
{
    public function index(Request $request): View
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $search = $request->query('search');
        $query = Category::withCount('ebooks');

        if ($search !== null && trim($search) !== '') {
            $keyword = trim($search);
            $query->where('name', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%");
        }

        $categories = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.categories', compact('categories', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Vui lòng nhập tên thể loại.',
            'name.unique' => 'Tên thể loại này đã tồn tại.',
        ]);

        Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::lower(Str::random(4)),
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Đã thêm thể loại mới thành công.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Đã cập nhật thể loại thành công.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        if ($category->ebooks()->count() > 0) {
            return back()->with('error', 'Không thể xóa thể loại đang chứa sách. Hãy chuyển hoặc xóa sách trước.');
        }

        $category->delete();

        return back()->with('success', 'Đã xóa thể loại thành công.');
    }
}
