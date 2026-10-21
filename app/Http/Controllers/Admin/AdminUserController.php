<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $search = $request->query('search');
        $role = $request->query('role');
        $status = $request->query('status');

        $query = User::withCount(['ebooks', 'reviews', 'bookmarks']);

        if ($role && in_array($role, ['admin', 'author', 'reader'], true)) {
            $query->where('role', $role);
        }

        if ($status && in_array($status, ['active', 'banned'], true)) {
            $query->where('status', $status);
        }

        if ($search !== null && trim($search) !== '') {
            $keyword = trim($search);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users', compact('users', 'search', 'role', 'status'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'role' => ['required', 'in:reader,author,admin'],
        ]);

        if ((int) $user->id === (int) auth()->id() && $validated['role'] !== 'admin') {
            return back()->with('error', 'Bạn không thể tự hạ quyền Quản trị viên của chính mình.');
        }

        $user->update(['role' => $validated['role']]);

        return back()->with('success', "Đã cập nhật vai trò của {$user->name} thành {$validated['role']}.");
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        if ((int) $user->id === (int) auth()->id()) {
            return back()->with('error', 'Bạn không thể tự khóa tài khoản của chính mình.');
        }

        $newStatus = $user->isBanned() ? 'active' : 'banned';
        $user->update(['status' => $newStatus]);

        $msg = $newStatus === 'banned'
            ? "Đã khóa tài khoản {$user->name}."
            : "Đã mở khóa tài khoản {$user->name}.";

        return back()->with('success', $msg);
    }
}
