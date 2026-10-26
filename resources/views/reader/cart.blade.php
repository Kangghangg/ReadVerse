@extends('layouts.readverse')

@section('title', 'Giỏ hàng của bạn - ReadVerse')

@section('content')
@php
    $formattedTotal = number_format((float) $totalAmount, 0, ',', '.') . ' đ';
@endphp

<div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-sand pb-6">
        <div>
            <span class="text-xs font-semibold uppercase tracking-widest text-taupe">ReadVerse Bookstore</span>
            <h1 class="mt-1 font-serif text-3xl font-bold text-espresso sm:text-4xl">Giỏ hàng của bạn</h1>
            <p class="mt-1 text-sm text-mocha">Quản lý các tác phẩm đã chọn và thanh toán mở khóa trực tuyến</p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('home') }}" class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-xl border border-beige bg-white px-4 text-xs font-semibold text-espresso shadow-sm transition hover:bg-sand">
                Tiếp tục chọn sách
            </a>
            @if(count($cartItems) > 0)
            <form action="{{ route('cart.clear') }}" method="POST" data-confirm-title="Làm trống giỏ hàng" data-confirm="Bạn có chắc chắn muốn xóa toàn bộ sách trong giỏ hàng không?" data-confirm-btn="Xóa tất cả">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-xl border border-beige bg-cream px-4 text-xs font-semibold text-mocha transition hover:border-espresso hover:bg-espresso hover:text-cream">
                    Làm trống giỏ
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(count($cartItems) > 0)
        <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                @foreach($cartItems as $item)
                    @php $ebook = $item->ebook; @endphp
                    @if($ebook)
                    <div class="flex flex-col gap-4 rounded-2xl border border-beige bg-white p-4 shadow-sm transition sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-4">
                            <a href="{{ route('ebooks.show', $ebook->slug) }}" class="h-28 w-20 shrink-0 overflow-hidden rounded-xl border border-beige/60 bg-sand shadow-sm">
                                <img src="{{ $ebook->cover_url }}" alt="{{ $ebook->title }}" class="h-full w-full object-cover">
                            </a>
                            <div>
                                <span class="inline-flex items-center whitespace-nowrap rounded-md border border-beige/70 bg-sand/60 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-espresso">
                                    {{ $ebook->category?->name ?? 'Tổng hợp' }}
                                </span>
                                <h3 class="mt-1 font-serif text-xl font-bold text-espresso">
                                    <a href="{{ route('ebooks.show', $ebook->slug) }}" class="hover:text-mocha">{{ $ebook->title }}</a>
                                </h3>
                                <p class="text-xs text-mocha">Tác giả: <strong class="text-espresso">{{ $ebook->author?->name }}</strong> • {{ $ebook->pages }} trang</p>
                                <p class="mt-2 text-base font-bold text-espresso">{{ number_format((float) $ebook->price, 0, ',', '.') }} đ</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 border-t border-sand pt-3 sm:border-0 sm:pt-0">
                            <form action="{{ route('cart.remove', $ebook->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-xl border border-beige bg-cream px-3.5 text-xs font-semibold text-mocha transition hover:border-espresso hover:bg-espresso hover:text-cream">
                                    Bỏ khỏi giỏ
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>

            <div class="lg:col-span-1">
                <div class="sticky top-24 rounded-3xl border border-beige bg-white p-6 shadow-sm">
                    <h2 class="font-serif text-2xl font-bold text-espresso">Tóm tắt đơn hàng</h2>

                    <div class="mt-4 space-y-3 border-y border-sand py-4 text-sm text-mocha">
                        <div class="flex items-center justify-between">
                            <span>Số lượng tác phẩm:</span>
                            <strong class="text-espresso">{{ count($cartItems) }} cuốn</strong>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Quyền lợi:</span>
                            <strong class="text-espresso">Đọc trực tuyến & Tải PDF</strong>
                        </div>
                        <div class="flex items-center justify-between pt-2 text-base">
                            <span class="font-semibold text-espresso">Tổng thanh toán:</span>
                            <strong class="text-xl font-extrabold text-espresso">{{ $formattedTotal }}</strong>
                        </div>
                    </div>

                    <button
                        type="button"
                        onclick="document.getElementById('cart-qr-modal').classList.remove('hidden'); document.getElementById('cart-qr-modal').classList.add('flex');"
                        class="mt-6 inline-flex h-11 w-full items-center justify-center rounded-xl bg-espresso px-5 text-sm font-semibold text-cream shadow-sm transition hover:bg-mocha"
                    >
                        <span>Tiến hành thanh toán ({{ $formattedTotal }})</span>
                    </button>
                </div>
            </div>
        </div>

        <div
            id="cart-qr-modal"
            class="fixed inset-0 z-50 hidden items-center justify-center p-4"
            style="background-color: rgba(27, 21, 16, 0.72); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);"
        >
            <div class="relative w-full max-w-lg overflow-hidden rounded-3xl border border-beige bg-cream p-6 shadow-2xl sm:p-7">
                <div class="flex items-start justify-between border-b border-sand pb-4">
                    <div>
                        <span class="inline-block rounded-md bg-sand px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-espresso">Thanh toán trực tuyến</span>
                        <h3 class="mt-1.5 font-serif text-2xl font-bold text-espresso">Thanh toán Giỏ hàng ({{ count($cartItems) }} sách)</h3>
                    </div>
                    <button
                        type="button"
                        onclick="document.getElementById('cart-qr-modal').classList.add('hidden'); document.getElementById('cart-qr-modal').classList.remove('flex');"
                        class="inline-flex h-8 items-center justify-center rounded-lg border border-beige bg-white px-3 text-xs font-semibold text-mocha transition hover:bg-sand hover:text-espresso"
                    >
                        Đóng
                    </button>
                </div>

                <div class="mt-4 max-h-32 space-y-2 overflow-y-auto rounded-2xl border border-beige bg-white p-3.5 text-xs">
                    @foreach($cartItems as $item)
                        @if($item->ebook)
                        <div class="flex items-center justify-between gap-2">
                            <span class="truncate font-semibold text-espresso">{{ $item->ebook->title }}</span>
                            <span class="shrink-0 font-bold text-mocha">{{ number_format((float) $item->ebook->price, 0, ',', '.') }} đ</span>
                        </div>
                        @endif
                    @endforeach
                    <div class="flex items-center justify-between border-t border-sand pt-2 text-sm font-bold text-espresso">
                        <span>Tổng cộng:</span>
                        <span>{{ $formattedTotal }}</span>
                    </div>
                </div>

                <div class="mt-4 flex flex-col items-center rounded-2xl border border-beige bg-white p-5 text-center shadow-sm">
                    <div class="relative flex h-52 w-52 items-center justify-center overflow-hidden rounded-xl border border-sand bg-white p-2 shadow-inner">
                        <img src="{{ $vietQrUrl }}" alt="Mã QR Thanh Toán Giỏ Hàng" class="h-full w-full object-contain">
                    </div>
                    <div class="mt-3 space-y-1 text-xs text-mocha">
                        <p>1. Mở ứng dụng Ngân hàng hoặc Ví điện tử và quét mã QR phía trên.</p>
                        <p>2. Nội dung chuyển khoản: <strong class="rounded bg-sand px-1.5 py-0.5 font-mono text-espresso">{{ $transferMemo }}</strong></p>
                        <p>3. Bấm xác nhận bên dưới để mở khóa toàn bộ <strong>{{ count($cartItems) }} tác phẩm</strong>.</p>
                    </div>
                </div>

                <form action="{{ route('cart.checkout') }}" method="POST" class="mt-5 flex items-center gap-3">
                    @csrf
                    <button
                        type="button"
                        onclick="document.getElementById('cart-qr-modal').classList.add('hidden'); document.getElementById('cart-qr-modal').classList.remove('flex');"
                        class="inline-flex h-10 w-1/3 items-center justify-center rounded-xl border border-beige bg-white text-xs font-semibold text-espresso transition hover:bg-sand"
                    >
                        Để sau
                    </button>
                    <button
                        type="submit"
                        class="inline-flex h-10 flex-1 items-center justify-center rounded-xl bg-espresso px-4 text-xs font-semibold text-cream shadow-sm transition hover:bg-mocha"
                    >
                        <span>Xác nhận đã thanh toán ({{ $formattedTotal }})</span>
                    </button>
                </form>
            </div>
        </div>
    @else
        <div class="mt-10 rounded-3xl border border-beige bg-white py-16 px-6 text-center shadow-sm">
            <h2 class="font-serif text-2xl font-bold text-espresso">Giỏ hàng của bạn đang trống</h2>
            <p class="mx-auto mt-1 max-w-md text-sm text-mocha">Hãy khám phá kho tàng sách điện tử trên ReadVerse và thêm những cuốn sách bạn yêu thích vào giỏ hàng.</p>
            <div class="mt-6 flex justify-center gap-3">
                <a href="{{ route('home') }}" class="inline-flex h-9 items-center justify-center rounded-xl bg-espresso px-5 text-xs font-semibold text-cream shadow-sm transition hover:bg-mocha">
                    Khám phá thư viện sách
                </a>
                <a href="{{ url('/my-library') }}" class="inline-flex h-9 items-center justify-center rounded-xl border border-beige bg-sand/50 px-5 text-xs font-semibold text-espresso transition hover:bg-sand">
                    Tủ sách của tôi
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
