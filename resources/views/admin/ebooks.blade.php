@extends('layouts.readverse')

@section('title', 'Quản lý tất cả sách - ReadVerse Admin')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    @include('admin.partials.nav')

    <div id="catalog-container">
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 mb-8">
            <div class="rounded-2xl border border-beige bg-sand/40 p-4">
                <p class="text-xs font-semibold uppercase text-mocha">Tổng số sách</p>
                <p class="mt-1 font-serif text-3xl font-bold text-espresso">{{ $stats['total_ebooks'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-beige bg-sand/40 p-4">
                <p class="text-xs font-semibold uppercase text-mocha">Đã xuất bản</p>
                <p class="mt-1 font-serif text-3xl font-bold text-emerald-700">{{ $stats['published_ebooks'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-beige bg-sand/40 p-4">
                <p class="text-xs font-semibold uppercase text-mocha">Chờ duyệt</p>
                <p class="mt-1 font-serif text-3xl font-bold text-amber-700">{{ $stats['pending_ebooks'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-beige bg-sand/40 p-4">
                <p class="text-xs font-semibold uppercase text-mocha">Từ chối / Nháp</p>
                <p class="mt-1 font-serif text-3xl font-bold text-rose-700">{{ $stats['draft_ebooks'] ?? 0 }}</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.ebooks.index') }}" data-ajax-catalog-form="true" class="mb-6 flex flex-col gap-3 rounded-2xl border border-beige bg-sand/50 p-4 lg:flex-row lg:items-center">
            <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-12">
                <div class="sm:col-span-5">
                    <input type="text" name="search" value="{{ $search }}" data-ajax-search-input="true" autocomplete="off" placeholder="Tìm kiếm tên sách, tác giả, người đăng..." class="w-full rounded-xl border border-beige bg-cream px-4 py-2.5 text-sm text-espresso placeholder:text-taupe focus:border-espresso focus:outline-none focus:ring-1 focus:ring-espresso">
                </div>
                <div class="sm:col-span-4">
                    <select name="category" class="w-full rounded-xl border border-beige bg-cream px-3.5 py-2.5 text-sm text-espresso focus:border-espresso focus:outline-none focus:ring-1 focus:ring-espresso">
                        <option value="">Tất cả thể loại</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (int) $categoryId === (int) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-3">
                    <select name="status" class="w-full rounded-xl border border-beige bg-cream px-3.5 py-2.5 text-sm text-espresso focus:border-espresso focus:outline-none focus:ring-1 focus:ring-espresso">
                        <option value="">Mọi trạng thái</option>
                        <option value="published" {{ $status === 'published' ? 'selected' : '' }}>Đã xuất bản</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                        <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Từ chối / Bản nháp</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end lg:shrink-0">
                <button
                    type="button"
                    data-reset-catalog-filter="true"
                    data-reset-url="{{ route('admin.ebooks.index') }}"
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
                        <th class="px-5 py-4">Tác phẩm</th>
                        <th class="px-5 py-4">Tác giả / Thể loại</th>
                        <th class="px-5 py-4">Giá & Lượt xem</th>
                        <th class="px-5 py-4">Trạng thái</th>
                        <th class="px-5 py-4 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sand">
                    @forelse($ebooks as $ebook)
                    <tr>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $ebook->cover_url }}" alt="{{ $ebook->title }}" class="h-14 w-10 rounded object-cover shadow-sm">
                                <div>
                                    <a href="{{ route('ebooks.show', $ebook->slug) }}" data-spa-nav="true" class="font-semibold text-espresso hover:underline">{{ $ebook->title }}</a>
                                    <p class="text-xs text-mocha">Người đăng: {{ $ebook->uploader?->name ?? 'Hệ thống' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-medium text-espresso">{{ $ebook->author?->name }}</p>
                            <p class="text-xs text-mocha">{{ $ebook->category?->name }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-espresso">{{ $ebook->price > 0 ? number_format($ebook->price) . ' đ' : 'Miễn phí' }}</p>
                            <p class="text-xs text-mocha">{{ $ebook->view_count }} lượt đọc • {{ $ebook->reviews_count ?? 0 }} đánh giá</p>
                        </td>
                        <td class="px-5 py-4">
                            @if($ebook->status === 'published')
                                <span class="rounded-md bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Đã xuất bản</span>
                            @elseif($ebook->status === 'pending')
                                <span class="rounded-md bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Chờ duyệt</span>
                            @else
                                <span class="rounded-md bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800">Từ chối / Nháp</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right space-x-1">
                            @if($ebook->status !== 'published')
                            <form action="{{ route('admin.ebooks.approve', $ebook->id) }}" method="POST" data-ajax-mutation-form="true" class="inline">
                                @csrf
                                <button type="submit" class="rounded-lg bg-emerald-700 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-emerald-600">Duyệt</button>
                            </form>
                            @endif
                            @if($ebook->status !== 'draft')
                            <form action="{{ route('admin.ebooks.reject', $ebook->id) }}" method="POST" data-ajax-mutation-form="true" data-confirm-title="Gỡ / Từ chối xuất bản" data-confirm="Bạn có chắc chắn muốn gỡ hoặc từ chối phát hành cuốn sách &laquo;{{ $ebook->title }}&raquo; không?" data-confirm-btn="Xác nhận gỡ" class="inline">
                                @csrf
                                <button type="submit" class="rounded-lg bg-amber-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-amber-500">Gỡ / Từ chối</button>
                            </form>
                            @endif
                            <a href="{{ route('author.ebooks.edit', $ebook->id) }}" data-spa-nav="true" class="inline-block rounded-lg border border-beige bg-cream px-2.5 py-1.5 text-xs font-semibold text-espresso hover:bg-sand">Sửa</a>
                            <form action="{{ route('admin.ebooks.destroy', $ebook->id) }}" method="POST" data-ajax-mutation-form="true" data-confirm-title="Xóa vĩnh viễn sách" data-confirm="Bạn có chắc chắn muốn xóa vĩnh viễn cuốn sách &laquo;{{ $ebook->title }}&raquo; khỏi hệ thống? Thao tác này không thể hoàn tác." data-confirm-btn="Xóa vĩnh viễn" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-rose-500">Xóa</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-mocha">Không tìm thấy sách nào khớp với bộ lọc.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="catalog-pagination" class="mt-6">
            {{ $ebooks->links('vendor.pagination.readverse') }}
        </div>
    </div>
</div>
@endsection
