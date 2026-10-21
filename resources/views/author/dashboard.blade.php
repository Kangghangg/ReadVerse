@extends('layouts.readverse')

@section('title', 'Cổng tác giả - Quản lý tác phẩm')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between border-b border-sand pb-5">
        <div>
            <h1 class="font-serif text-3xl font-bold text-espresso">Cổng tác giả</h1>
            <p class="text-sm text-mocha">Quản lý và xuất bản sách điện tử lên thư viện</p>
        </div>
        <a href="{{ route('author.ebooks.create') }}" data-spa-nav="true" class="rounded-xl bg-espresso px-4 py-2.5 text-sm font-semibold text-cream shadow hover:bg-mocha">
            + Tải lên sách mới
        </a>
    </div>

    <div id="catalog-container" class="mt-6 overflow-hidden rounded-2xl border border-beige bg-white shadow-sm">
        <table class="min-w-full divide-y divide-sand text-left text-sm">
            <thead class="bg-sand/50 text-xs font-semibold uppercase text-mocha">
                <tr>
                    <th class="px-6 py-4">Tác phẩm</th>
                    <th class="px-6 py-4">Danh mục / Tác giả</th>
                    <th class="px-6 py-4">Tệp PDF & Số trang</th>
                    <th class="px-6 py-4">Trạng thái</th>
                    <th class="px-6 py-4 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-sand">
                @forelse($ebooks as $ebook)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-espresso">{{ $ebook->title }}</div>
                            <div class="text-xs text-mocha">{{ $ebook->price > 0 ? number_format($ebook->price) . ' đ' : 'Miễn phí' }} • {{ $ebook->view_count }} lượt xem</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-espresso font-medium">{{ $ebook->category?->name }}</div>
                            <div class="text-xs text-mocha">{{ $ebook->author?->name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ asset('storage/' . $ebook->file_path) }}" target="_blank" class="text-xs font-semibold text-espresso underline hover:text-mocha">Xem PDF ({{ $ebook->pages }} trang)</a>
                        </td>
                        <td class="px-6 py-4">
                            @if($ebook->status === 'published')
                                <span class="rounded-md bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Đã phát hành</span>
                            @elseif($ebook->status === 'pending')
                                <span class="rounded-md bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Chờ duyệt</span>
                            @else
                                <span class="rounded-md bg-sand px-2.5 py-1 text-xs font-semibold text-espresso">Bản nháp / Từ chối</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('author.ebooks.edit', $ebook->id) }}" data-spa-nav="true" class="text-xs font-semibold text-espresso hover:underline">Sửa</a>
                            <form action="{{ route('author.ebooks.destroy', $ebook->id) }}" method="POST" data-ajax-mutation-form="true" data-confirm-title="Xóa tác phẩm" data-confirm="Bạn có chắc chắn muốn xóa cuốn sách &laquo;{{ $ebook->title }}&raquo; không?" data-confirm-btn="Xóa sách" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">Xoá</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-mocha">Bạn chưa tải lên tác phẩm nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
