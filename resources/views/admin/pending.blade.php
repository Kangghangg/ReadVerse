@extends('layouts.readverse')

@section('title', 'Quản trị hệ thống - Phê duyệt bản thảo')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    @include('admin.partials.nav')

    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl border border-beige bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-mocha">Sách đã xuất bản</p>
            <p class="mt-1 font-serif text-3xl font-bold text-espresso">{{ $stats['total_ebooks'] ?? 0 }}</p>
        </div>
        <div class="rounded-2xl border border-beige bg-sand/50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-espresso">Bản thảo chờ duyệt</p>
            <p class="mt-1 font-serif text-3xl font-bold text-espresso">{{ $stats['pending_ebooks'] ?? 0 }}</p>
        </div>
        <div class="rounded-2xl border border-beige bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-mocha">Tổng người dùng</p>
            <p class="mt-1 font-serif text-3xl font-bold text-espresso">{{ $stats['total_users'] ?? 0 }}</p>
        </div>
        <div class="rounded-2xl border border-beige bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-mocha">Danh mục thể loại</p>
            <p class="mt-1 font-serif text-3xl font-bold text-espresso">{{ $stats['total_categories'] ?? 0 }}</p>
        </div>
    </div>

    <div class="mt-8 overflow-hidden rounded-2xl border border-beige bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-sand px-6 py-4">
            <div>
                <h2 class="font-serif text-2xl font-bold text-espresso">Danh sách Bản thảo Chờ phê duyệt ({{ count($ebooks) }})</h2>
                <p class="text-xs text-mocha">Kiểm tra nội dung PDF và phê duyệt xuất bản hoặc gửi phản hồi cho tác giả</p>
            </div>
            <a href="{{ route('author.ebooks.create') }}" class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-xl bg-espresso px-4 text-xs font-semibold text-cream shadow-sm transition hover:bg-mocha">
                Đăng tải sách mới
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-sand text-left text-sm">
                <thead class="bg-sand/50 text-xs font-semibold uppercase tracking-wider text-mocha">
                    <tr>
                        <th class="px-6 py-3.5">Bản thảo</th>
                        <th class="px-6 py-3.5">Người gửi / Tác giả</th>
                        <th class="px-6 py-3.5">Danh mục & Giá</th>
                        <th class="px-6 py-3.5">Tệp đính kèm</th>
                        <th class="px-6 py-3.5 text-right">Quyết định</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sand">
                    @forelse($ebooks as $ebook)
                        <tr>
                            <td class="px-6 py-4 align-middle">
                                <div class="flex items-center gap-3.5">
                                    <img src="{{ $ebook->cover_url }}" alt="{{ $ebook->title }}" class="h-14 w-10 shrink-0 rounded-lg border border-beige/60 object-cover shadow-sm">
                                    <div>
                                        <p class="font-serif text-base font-bold text-espresso">{{ $ebook->title }}</p>
                                        <p class="line-clamp-1 max-w-md text-xs text-mocha">{{ $ebook->description }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap">
                                <p class="font-semibold text-espresso">{{ $ebook->uploader?->name ?? 'Tác giả' }}</p>
                                <p class="mt-0.5 text-xs text-mocha">Bút danh: {{ $ebook->author?->name }}</p>
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap">
                                <span class="inline-flex items-center whitespace-nowrap rounded-lg border border-beige/80 bg-sand/60 px-2.5 py-1 text-xs font-semibold text-espresso">
                                    {{ $ebook->category?->name }}
                                </span>
                                <p class="mt-1.5 text-xs font-bold text-mocha">{{ $ebook->price > 0 ? number_format($ebook->price) . ' đ' : 'Miễn phí' }}</p>
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap">
                                <a href="{{ asset('storage/' . $ebook->file_path) }}" target="_blank" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg border border-beige bg-cream px-3 text-xs font-semibold text-espresso transition hover:bg-sand">
                                    Xem PDF ({{ $ebook->pages }} trang)
                                </a>
                            </td>
                            <td class="px-6 py-4 align-middle text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-2 whitespace-nowrap">
                                    <form action="{{ route('admin.ebooks.approve', $ebook->id) }}" method="POST" class="inline-flex">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg bg-espresso px-3.5 text-xs font-semibold text-cream shadow-sm transition hover:bg-mocha">
                                            Duyệt xuất bản
                                        </button>
                                    </form>

                                    <button
                                        type="button"
                                        onclick="window.rvOpenRejectModal(this)"
                                        data-open-reject-modal="true"
                                        data-reject-action="{{ route('admin.ebooks.reject', $ebook->id) }}"
                                        data-ebook-title="{{ $ebook->title }}"
                                        data-author-name="{{ $ebook->uploader?->name ?? $ebook->author?->name ?? 'Tác giả' }}"
                                        class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg border border-beige bg-white px-3.5 text-xs font-semibold text-mocha transition hover:border-espresso hover:bg-espresso hover:text-cream"
                                    >
                                        Từ chối
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-mocha">
                                Hiện không có bản thảo nào đang chờ duyệt.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('admin.partials.reject-modal')
</div>
@endsection
