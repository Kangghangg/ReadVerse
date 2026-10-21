@extends('layouts.readverse')

@section('title', 'Quản lý Người dùng - ReadVerse Admin')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    @include('admin.partials.nav')

    <div id="catalog-container">
        <form method="GET" action="{{ route('admin.users.index') }}" data-ajax-catalog-form="true" class="mb-6 flex flex-col gap-3 rounded-2xl border border-beige bg-sand/50 p-4 lg:flex-row lg:items-center">
            <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-12">
                <div class="sm:col-span-6">
                    <input type="text" name="search" value="{{ $search }}" data-ajax-search-input="true" autocomplete="off" placeholder="Tìm theo họ tên hoặc email..." class="w-full rounded-xl border border-beige bg-cream px-4 py-2.5 text-sm text-espresso placeholder:text-taupe focus:border-espresso focus:outline-none focus:ring-1 focus:ring-espresso">
                </div>
                <div class="sm:col-span-3">
                    <select name="role" class="w-full rounded-xl border border-beige bg-cream px-3.5 py-2.5 text-sm text-espresso focus:border-espresso focus:outline-none focus:ring-1 focus:ring-espresso">
                        <option value="">Tất cả vai trò</option>
                        <option value="reader" {{ $role === 'reader' ? 'selected' : '' }}>Độc giả (Reader)</option>
                        <option value="author" {{ $role === 'author' ? 'selected' : '' }}>Tác giả (Author)</option>
                        <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                    </select>
                </div>
                <div class="sm:col-span-3">
                    <select name="status" class="w-full rounded-xl border border-beige bg-cream px-3.5 py-2.5 text-sm text-espresso focus:border-espresso focus:outline-none focus:ring-1 focus:ring-espresso">
                        <option value="">Mọi trạng thái</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                        <option value="banned" {{ $status === 'banned' ? 'selected' : '' }}>Đã khóa (Banned)</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end lg:shrink-0">
                <button
                    type="button"
                    data-reset-catalog-filter="true"
                    data-reset-url="{{ route('admin.users.index') }}"
                    title="Khôi phục bộ lọc về mặc định"
                    aria-label="Khôi phục bộ lọc về mặc định"
                    style="border-radius: 9999px;"
                    class="group inline-flex h-10 w-10 shrink-0 items-center justify-center bg-espresso text-cream shadow-sm transition hover:bg-mocha focus:outline-none focus:ring-2 focus:ring-mocha/40"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-300 group-hover:-rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </div>
        </form>

        <div id="catalog-grid-wrapper" class="rounded-2xl border border-beige bg-white shadow-sm transition-opacity duration-150">
            <table class="min-w-full divide-y divide-sand text-left text-sm">
                <thead class="bg-sand/50 text-xs font-semibold uppercase text-mocha">
                    <tr>
                        <th class="px-6 py-4">Người dùng</th>
                        <th class="px-6 py-4">Hoạt động</th>
                        <th class="px-6 py-4">Chuyển đổi vai trò</th>
                        <th class="px-6 py-4">Trạng thái</th>
                        <th class="px-6 py-4 text-right">Khóa / Mở khóa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sand">
                    @forelse($users as $userItem)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-espresso">{{ $userItem->name }}</div>
                            <div class="text-xs text-mocha">{{ $userItem->email }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs text-mocha">
                            {{ $userItem->ebooks_count }} sách đăng • {{ $userItem->reviews_count }} đánh giá • {{ $userItem->bookmarks_count }} yêu thích
                        </td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.users.role', $userItem->id) }}" method="POST" data-ajax-mutation-form="true" class="inline-block">
                                @csrf
                                @method('PATCH')
                                <select name="role" data-auto-submit="true" data-compact-select="true" class="rounded-lg border border-beige bg-cream pl-3 pr-8 py-1.5 text-xs font-medium text-espresso">
                                    <option value="reader" {{ $userItem->role === 'reader' ? 'selected' : '' }}>Reader</option>
                                    <option value="author" {{ $userItem->role === 'author' ? 'selected' : '' }}>Author</option>
                                    <option value="admin" {{ $userItem->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                            </form>
                        </td>
                        <td class="px-6 py-4">
                            @if($userItem->isBanned())
                                <span class="rounded-md bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800">Banned (Đã khóa)</span>
                            @else
                                <span class="rounded-md bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Active (Hoạt động)</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if((int) $userItem->id !== (int) auth()->id())
                                @if($userItem->isBanned())
                                <form action="{{ route('admin.users.toggle-status', $userItem->id) }}" method="POST" data-ajax-mutation-form="true" class="inline">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-600">Mở khóa</button>
                                </form>
                                @else
                                <form action="{{ route('admin.users.toggle-status', $userItem->id) }}" method="POST" data-ajax-mutation-form="true" data-confirm-title="Khóa tài khoản người dùng" data-confirm="Bạn có chắc chắn muốn khóa tài khoản của &laquo;{{ $userItem->name }}&raquo; ({{ $userItem->email }}) không?" data-confirm-btn="Khóa tài khoản" class="inline">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-500">Khóa tài khoản</button>
                                </form>
                                @endif
                            @else
                            <span class="text-xs italic text-mocha">Tài khoản của bạn</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-mocha">Không tìm thấy người dùng phù hợp.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="catalog-pagination" class="mt-6">
            {{ $users->links('vendor.pagination.readverse') }}
        </div>
    </div>
</div>
@endsection
