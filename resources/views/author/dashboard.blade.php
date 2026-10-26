@extends('layouts.readverse')

@section('title', 'Cổng tác giả - Quản lý tác phẩm')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-sand pb-5">
        <div>
            <h1 class="font-serif text-3xl font-bold text-espresso">Cổng tác giả</h1>
            <p class="mt-0.5 text-sm text-mocha">Quản lý bản thảo, theo dõi phản hồi phê duyệt và xuất bản sách điện tử lên thư viện</p>
        </div>
        <a href="{{ route('author.ebooks.create') }}" data-spa-nav="true" class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-xl bg-espresso px-4 text-xs font-semibold text-cream shadow-sm transition hover:bg-mocha">
            Tải lên sách mới
        </a>
    </div>

    <div id="catalog-container" class="mt-6 overflow-hidden rounded-2xl border border-beige bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-sand text-left text-sm">
                <thead class="bg-sand/50 text-xs font-semibold uppercase tracking-wider text-mocha">
                    <tr>
                        <th class="px-6 py-4">Tác phẩm & Phản hồi duyệt</th>
                        <th class="px-6 py-4">Danh mục / Tác giả</th>
                        <th class="px-6 py-4">Tệp PDF & Số trang</th>
                        <th class="px-6 py-4">Trạng thái</th>
                        <th class="px-6 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sand">
                    @forelse($ebooks as $ebook)
                        <tr>
                            <td class="px-6 py-4 align-middle">
                                <div class="font-serif text-base font-bold text-espresso">{{ $ebook->title }}</div>
                                <div class="mt-0.5 text-xs text-mocha">{{ $ebook->price > 0 ? number_format($ebook->price) . ' đ' : 'Miễn phí' }} • {{ $ebook->view_count }} lượt xem</div>

                                @if($ebook->status === 'draft' && ($ebook->rejection_reason || $ebook->rejection_note))
                                    <div class="mt-3 rounded-2xl border border-beige bg-cream/80 p-3.5 text-xs text-ink">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <span class="font-bold text-espresso">Lý do cần chỉnh sửa: {{ $ebook->rejection_reason ?? 'Cần bổ sung thông tin bản thảo' }}</span>
                                            @if($ebook->rejected_at)
                                                <span class="text-[11px] text-taupe">{{ $ebook->rejected_at->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                        @if($ebook->rejection_note)
                                            <p class="mt-1.5 text-mocha leading-relaxed">
                                                <strong class="text-espresso">Góp ý từ Quản trị viên:</strong> {{ $ebook->rejection_note }}
                                            </p>
                                        @endif
                                        <div class="mt-2.5">
                                            <a href="{{ route('author.ebooks.edit', $ebook->id) }}" class="inline-flex h-7 items-center justify-center rounded-lg bg-espresso px-3 text-[11px] font-semibold text-cream transition hover:bg-mocha">
                                                Chỉnh sửa & Gửi duyệt lại
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap">
                                <span class="inline-flex items-center whitespace-nowrap rounded-lg border border-beige/80 bg-sand/60 px-2.5 py-1 text-xs font-semibold text-espresso">
                                    {{ $ebook->category?->name }}
                                </span>
                                <div class="mt-1.5 text-xs text-mocha">{{ $ebook->author?->name }}</div>
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap">
                                <a href="{{ asset('storage/' . $ebook->file_path) }}" target="_blank" class="text-xs font-semibold text-espresso underline hover:text-mocha">Xem PDF ({{ $ebook->pages }} trang)</a>
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap">
                                @if($ebook->status === 'published')
                                    <span class="inline-flex items-center whitespace-nowrap rounded-lg border border-espresso/20 bg-sand px-3 py-1 text-xs font-semibold text-espresso">
                                        Đã phát hành
                                    </span>
                                @elseif($ebook->status === 'pending')
                                    <span class="inline-flex items-center whitespace-nowrap rounded-lg border border-mocha/30 bg-cream px-3 py-1 text-xs font-semibold text-mocha">
                                        Chờ duyệt
                                    </span>
                                @else
                                    <span class="inline-flex items-center whitespace-nowrap rounded-lg border border-beige bg-white px-3 py-1 text-xs font-semibold text-taupe">
                                        Cần chỉnh sửa
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 align-middle text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-2 whitespace-nowrap">
                                    <a href="{{ route('author.ebooks.edit', $ebook->id) }}" data-spa-nav="true" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg border border-beige bg-sand/50 px-3 text-xs font-semibold text-espresso transition hover:bg-sand">
                                        Sửa
                                    </a>
                                    <form action="{{ route('author.ebooks.destroy', $ebook->id) }}" method="POST" class="inline-flex" data-confirm-title="Xóa tác phẩm" data-confirm="Bạn có chắc chắn muốn xóa cuốn sách này không?" data-confirm-btn="Xóa">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg border border-beige bg-white px-3 text-xs font-semibold text-mocha transition hover:border-espresso hover:bg-espresso hover:text-cream">
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-mocha">
                                Bạn chưa đăng tải tác phẩm nào. Hãy bấm nút "Tải lên sách mới" để bắt đầu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
