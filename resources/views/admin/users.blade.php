@extends('layouts.readverse')

@section('title', 'Quản trị hệ thống - Quản lý người dùng')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    @include('admin.partials.nav')

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-serif text-3xl font-bold text-espresso">Quản lý Người dùng ({{ $users->total() }})</h1>
            <p class="mt-0.5 text-xs text-mocha">Phân quyền vai trò (Reader / Author / Admin) và quản lý trạng thái hoạt động tài khoản</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.users.index') }}" class="mt-5 grid grid-cols-1 gap-3 rounded-2xl border border-beige bg-white p-4 shadow-sm sm:grid-cols-4">
        <input type="text" name="search" value="{{ $search }}" placeholder="Tìm theo tên hoặc email..." class="h-9 rounded-xl border border-beige bg-cream px-3.5 text-xs text-ink placeholder:text-taupe">
        <select name="role" class="h-9 rounded-xl border border-beige bg-cream px-3.5 text-xs font-medium text-espresso">
            <option value="">Tất cả vai trò</option>
            <option value="admin" @selected($role === 'admin')>Quản trị viên (Admin)</option>
            <option value="author" @selected($role === 'author')>Tác giả (Author)</option>
            <option value="reader" @selected($role === 'reader')>Độc giả (Reader)</option>
        </select>
        <select name="status" class="h-9 rounded-xl border border-beige bg-cream px-3.5 text-xs font-medium text-espresso">
            <option value="">Tất cả trạng thái</option>
            <option value="active" @selected($status === 'active')>Đang hoạt động</option>
            <option value="banned" @selected($status === 'banned')>Đã khóa</option>
        </select>
        <div class="flex gap-2">
            <button type="submit" class="inline-flex h-9 flex-1 items-center justify-center rounded-xl bg-espresso px-4 text-xs font-semibold text-cream transition hover:bg-mocha">Lọc dữ liệu</button>
            <a href="{{ route('admin.users.index') }}" class="inline-flex h-9 items-center justify-center rounded-xl border border-beige bg-cream px-3.5 text-xs font-semibold text-espresso transition hover:bg-sand">Đặt lại</a>
        </div>
    </form>

    <div class="mt-6 overflow-visible rounded-2xl border border-beige bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-sand text-left text-sm">
                <thead class="bg-sand/50 text-xs font-semibold uppercase tracking-wider text-mocha">
                    <tr>
                        <th class="px-6 py-3.5">Người dùng</th>
                        <th class="px-6 py-3.5">Hoạt động</th>
                        <th class="px-6 py-3.5">Chuyển đổi vai trò</th>
                        <th class="px-6 py-3.5">Trạng thái</th>
                        <th class="px-6 py-3.5 text-right">Khóa / Mở khóa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sand">
                    @forelse($users as $u)
                        <tr>
                            <td class="px-6 py-4 align-middle">
                                <p class="font-serif text-base font-bold text-espresso">{{ $u->name }}</p>
                                <p class="text-xs text-mocha">{{ $u->email }}</p>
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-xs text-mocha">
                                {{ $u->ebooks_count ?? 0 }} sách đăng • {{ $u->reviews_count ?? 0 }} đánh giá • {{ $u->bookmarks_count ?? 0 }} yêu thích
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap">
                                <form action="{{ route('admin.users.update_role', $u->id) }}" method="POST" class="inline-flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select
                                        name="role"
                                        onchange="this.form.submit()"
                                        @disabled((int) $u->id === (int) auth()->id())
                                        class="h-8 rounded-xl border border-beige bg-cream px-3 py-1 text-xs font-semibold text-espresso focus:border-espresso focus:ring-espresso disabled:opacity-60"
                                    >
                                        <option value="reader" @selected($u->role === 'reader')>Reader</option>
                                        <option value="author" @selected($u->role === 'author')>Author</option>
                                        <option value="admin" @selected($u->role === 'admin')>Admin</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap">
                                @if($u->isBanned())
                                    <span class="inline-flex items-center whitespace-nowrap rounded-lg border border-beige bg-cream px-3 py-1 text-xs font-semibold text-taupe">
                                        Đã khóa
                                    </span>
                                @else
                                    <span class="inline-flex items-center whitespace-nowrap rounded-lg border border-espresso/20 bg-sand px-3 py-1 text-xs font-semibold text-espresso">
                                        Đang hoạt động
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 align-middle text-right whitespace-nowrap">
                                @if((int) $u->id === (int) auth()->id())
                                    <span class="text-xs italic text-taupe">Tài khoản của bạn</span>
                                @else
                                    <form action="{{ route('admin.users.toggle_status', $u->id) }}" method="POST" class="inline-flex">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg px-3.5 text-xs font-semibold transition {{ $u->isBanned() ? 'bg-espresso text-cream hover:bg-mocha' : 'border border-beige bg-white text-mocha hover:border-espresso hover:bg-espresso hover:text-cream' }}"
                                        >
                                            {{ $u->isBanned() ? 'Mở khóa tài khoản' : 'Khóa tài khoản' }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-mocha">Không tìm thấy người dùng phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $users->links('vendor.pagination.readverse') }}
    </div>
</div>
@endsection
