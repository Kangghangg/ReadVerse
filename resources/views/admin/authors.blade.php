@extends('layouts.readverse')

@section('title', 'Quản trị hệ thống - Quản lý tác giả')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    @include('admin.partials.nav')

    <div class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <div class="rounded-2xl border border-beige bg-white p-6 shadow-sm">
                <h2 class="font-serif text-2xl font-bold text-espresso">Thêm Tác giả Mới</h2>
                <p class="mt-1 text-xs text-mocha">Bổ sung hồ sơ tác giả vào thư viện ReadVerse</p>

                <form action="{{ route('admin.authors.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-espresso">Tên tác giả *</label>
                        <input type="text" name="name" required placeholder="VD: Nguyễn Nhật Ánh..." class="mt-1.5 h-9 w-full rounded-xl border border-beige bg-cream/60 px-3.5 text-xs text-ink focus:border-espresso focus:bg-white focus:ring-espresso">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-espresso">Tiểu sử</label>
                        <textarea name="bio" rows="3" placeholder="Giới thiệu ngắn gọn về tác giả..." class="mt-1.5 w-full rounded-xl border border-beige bg-cream/60 px-3.5 py-2 text-xs text-ink focus:border-espresso focus:bg-white focus:ring-espresso"></textarea>
                    </div>
                    <button type="submit" class="inline-flex h-9 w-full items-center justify-center rounded-xl bg-espresso text-xs font-semibold text-cream shadow-sm transition hover:bg-mocha">
                        Lưu tác giả mới
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <form method="GET" action="{{ route('admin.authors.index') }}" class="flex gap-2 rounded-2xl border border-beige bg-white p-4 shadow-sm">
                <input type="text" name="search" value="{{ $search }}" placeholder="Tìm kiếm theo tên hoặc tiểu sử tác giả..." class="h-9 flex-1 rounded-xl border border-beige bg-cream px-3.5 text-xs text-ink placeholder:text-taupe">
                <button type="submit" class="inline-flex h-9 items-center justify-center rounded-xl bg-espresso px-4 text-xs font-semibold text-cream transition hover:bg-mocha">Tìm kiếm</button>
            </form>

            <div class="mt-4 overflow-hidden rounded-2xl border border-beige bg-white shadow-sm">
                <table class="min-w-full divide-y divide-sand text-left text-sm">
                    <thead class="bg-sand/50 text-xs font-semibold uppercase tracking-wider text-mocha">
                        <tr>
                            <th class="px-5 py-3.5">Tác giả & Tiểu sử</th>
                            <th class="px-5 py-3.5 whitespace-nowrap">Số tác phẩm</th>
                            <th class="px-5 py-3.5 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sand">
                        @forelse($authors as $au)
                            <tr x-data="{ editing: false }">
                                <td class="px-5 py-4 align-middle">
                                    <div x-show="!editing">
                                        <p class="font-serif text-base font-bold text-espresso">{{ $au->name }}</p>
                                        <p class="mt-0.5 line-clamp-2 text-xs text-mocha">{{ $au->bio ?: 'Chưa có thông tin tiểu sử.' }}</p>
                                    </div>
                                    <form x-show="editing" style="display: none;" action="{{ route('admin.authors.update', $au->id) }}" method="POST" class="space-y-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" value="{{ $au->name }}" required class="h-8 w-full rounded-lg border border-beige bg-cream px-3 text-xs font-semibold text-espresso">
                                        <input type="text" name="bio" value="{{ $au->bio }}" placeholder="Tiểu sử..." class="h-8 w-full rounded-lg border border-beige bg-cream px-3 text-xs text-ink">
                                        <div class="flex gap-2">
                                            <button type="submit" class="inline-flex h-7 items-center justify-center rounded-lg bg-espresso px-3 text-[11px] font-semibold text-cream">Lưu</button>
                                            <button type="button" @click="editing = false" class="inline-flex h-7 items-center justify-center rounded-lg border border-beige bg-white px-3 text-[11px] font-semibold text-mocha">Hủy</button>
                                        </div>
                                    </form>
                                </td>
                                <td class="px-5 py-4 align-middle whitespace-nowrap">
                                    <span class="inline-flex items-center whitespace-nowrap rounded-lg border border-beige/80 bg-sand/60 px-2.5 py-1 text-xs font-semibold text-espresso">
                                        {{ $au->ebooks_count ?? 0 }} tác phẩm
                                    </span>
                                </td>
                                <td class="px-5 py-4 align-middle text-right whitespace-nowrap">
                                    <div class="inline-flex items-center justify-end gap-2 whitespace-nowrap">
                                        <button type="button" @click="editing = !editing" class="inline-flex h-8 items-center justify-center rounded-lg border border-beige bg-sand/50 px-3 text-xs font-semibold text-espresso transition hover:bg-sand">
                                            Sửa
                                        </button>
                                        <form action="{{ route('admin.authors.destroy', $au->id) }}" method="POST" class="inline-flex" data-confirm-title="Xóa tác giả" data-confirm="Bạn có chắc chắn muốn xóa tác giả này không?" data-confirm-btn="Xóa">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-8 items-center justify-center rounded-lg border border-beige bg-white px-3 text-xs font-semibold text-mocha transition hover:border-espresso hover:bg-espresso hover:text-cream">
                                                Xóa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-10 text-center text-sm text-mocha">Chưa có tác giả nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">
                {{ $authors->links('vendor.pagination.readverse') }}
            </div>
        </div>
    </div>
</div>
@endsection
