@extends('layouts.readverse')

@section('title', $ebook->title . ' - ReadVerse')

@section('content')
@php
    $user = auth()->user();
    $canReadFull = $ebook->isUnlockedBy($user);
    $avgRating = $ebook->reviews->count() > 0 ? number_format($ebook->reviews->avg('rating'), 1) : '0.0';
    $formattedPrice = number_format((float) $ebook->price, 0, ',', '.') . 'đ';
    $transferMemo = 'READVERSE ' . $ebook->id . ($user ? ' U' . $user->id : '');
    $vietQrUrl = 'https://img.vietqr.io/image/MB-0901234567-compact2.png?amount=' . (int) $ebook->price . '&addInfo=' . urlencode($transferMemo) . '&accountName=' . urlencode('THU VIEN READVERSE');
    $shouldAutoOpenModal = (bool) session('open_checkout_modal') || request()->boolean('checkout');
@endphp

<div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
    <div id="checkout-success-banner" class="mb-6 hidden rounded-2xl border border-beige bg-sand/80 px-5 py-4 text-sm font-medium text-espresso shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-espresso" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span id="checkout-success-text">Thanh toán thành công! Sách đã được mở khóa.</span>
            </div>
            <a href="{{ route('ebooks.read', $ebook->slug) }}" style="background-color: #2563eb; color: #ffffff;" class="shrink-0 rounded-lg px-3.5 py-1.5 text-xs font-bold shadow-sm transition hover:opacity-90">
                Đọc ngay →
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-10 md:grid-cols-3">
        <div class="md:col-span-1">
            <div class="overflow-hidden rounded-2xl border border-beige bg-white shadow-lg">
                <img src="{{ $ebook->cover_url }}" alt="{{ $ebook->title }}" class="w-full object-cover">
            </div>

            <div id="ebook-action-container" class="mt-6 flex flex-col gap-3">
                @if($canReadFull)
                <a
                    id="read-now-btn"
                    href="{{ route('ebooks.read', $ebook->slug) }}"
                    style="background-color: #2563eb; color: #ffffff;"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl py-3.5 px-5 text-center text-sm font-bold text-white shadow-md transition hover:opacity-95"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>Đọc ngay</span>
                </a>
                @else
                <button
                    id="buy-now-btn"
                    type="button"
                    data-open-qr-modal="true"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-espresso py-3.5 px-5 text-center text-sm font-bold text-cream shadow-md transition hover:bg-mocha focus:outline-none focus:ring-2 focus:ring-mocha/40"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Mua sách ngay - {{ $formattedPrice }}</span>
                </button>
                @endif
            </div>
        </div>

        <div class="md:col-span-2 flex flex-col">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-block rounded-full bg-sand px-3 py-1 text-xs font-semibold text-espresso">
                    {{ $ebook->category?->name }}
                </span>
                <span id="unlocked-badge" class="{{ ($canReadFull && $ebook->price > 0) ? 'inline-block' : 'hidden' }} rounded-full bg-sand px-3 py-1 text-xs font-semibold text-espresso">
                    Đã mở khóa
                </span>
            </div>

            <h1 class="mt-3 font-serif text-3xl font-bold text-espresso sm:text-4xl">{{ $ebook->title }}</h1>
            <p class="mt-2 text-base text-mocha">Tác giả: <strong class="text-espresso">{{ $ebook->author?->name }}</strong></p>

            <div class="mt-6 flex flex-wrap items-center gap-6 border-y border-sand py-4 text-sm text-mocha">
                <div>Số trang: <strong class="text-espresso">{{ $ebook->pages }}</strong></div>
                <div>Lượt xem: <strong class="text-espresso">{{ $ebook->view_count }}</strong></div>
                <div>Đánh giá: <strong id="avg-rating-display" class="text-espresso">{{ $avgRating }}</strong>/5 (<span id="reviews-count-display">{{ $ebook->reviews->count() }}</span>)</div>
                <div>Giá: <strong class="text-espresso">{{ $ebook->price > 0 ? $formattedPrice : 'Miễn phí' }}</strong></div>
            </div>

            <div class="mt-6">
                <h2 class="font-serif text-xl font-bold text-espresso">Mô tả tác phẩm</h2>
                <div class="mt-3 text-sm leading-relaxed text-ink whitespace-pre-line">{{ $ebook->description }}</div>
            </div>

            <div class="mt-10 border-t border-sand pt-8">
                <h2 class="font-serif text-xl font-bold text-espresso">Thông tin tác giả</h2>
                <div class="mt-3 flex items-start gap-4 rounded-xl border border-beige bg-white p-4">
                    <div class="flex-1 text-sm text-mocha">
                        <h3 class="font-bold text-espresso">{{ $ebook->author?->name }}</h3>
                        <p class="mt-1">{{ $ebook->author?->bio ?? 'Chưa có thông tin tiểu sử tác giả.' }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-10 border-t border-sand pt-8">
                <h2 class="font-serif text-2xl font-bold text-espresso">Đánh giá & Bình luận từ Độc giả</h2>

                @auth
                <form id="ajax-review-form" action="{{ route('reviews.store', $ebook->id) }}" method="POST" class="mt-4 rounded-2xl border border-beige bg-sand/40 p-5">
                    @csrf
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <label class="text-xs font-semibold uppercase tracking-wider text-espresso">Chọn mức đánh giá sao:</label>
                        <div class="w-full sm:w-60">
                            <select name="rating" class="w-full rounded-xl border border-beige bg-cream px-3.5 py-2 text-sm font-semibold text-espresso">
                                <option value="5">5 Sao — Tuyệt vời</option>
                                <option value="4">4 Sao — Rất hay</option>
                                <option value="3">3 Sao — Khá ổn</option>
                                <option value="2">2 Sao — Trung bình</option>
                                <option value="1">1 Sao — Cần cải thiện</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <textarea name="comment" rows="3" required placeholder="Chia sẻ cảm nhận của bạn về cuốn sách này..." class="w-full rounded-xl border border-beige bg-cream px-4 py-2.5 text-sm text-ink placeholder:text-taupe focus:border-espresso focus:ring-espresso"></textarea>
                    </div>
                    <div class="mt-3 flex items-center justify-between">
                        <span id="ajax-review-status" class="text-xs font-medium text-espresso"></span>
                        <button type="submit" id="ajax-review-submit" class="rounded-xl bg-espresso px-5 py-2.5 text-xs font-semibold text-cream transition hover:bg-mocha">
                            Gửi đánh giá
                        </button>
                    </div>
                </form>
                @else
                <div class="mt-4 rounded-xl border border-beige bg-sand/40 p-4 text-sm text-mocha">
                    Vui lòng <a href="{{ route('login') }}" class="font-semibold text-espresso underline">Đăng nhập</a> để gửi đánh giá và bình luận cho cuốn sách này.
                </div>
                @endauth

                <div id="reviews-list" class="mt-6 space-y-4">
                    @forelse($ebook->reviews->sortByDesc('updated_at') as $rev)
                    <div id="review-item-{{ $rev->id }}" data-user-id="{{ $rev->user_id }}" class="rounded-2xl border border-beige bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-sm text-espresso">{{ $rev->user?->name ?? 'Độc giả' }}</span>
                                <span class="rounded-md bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-800">{{ $rev->rating }} ★</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-taupe">{{ $rev->updated_at->diffForHumans() }}</span>
                                @if(auth()->check() && (auth()->user()->isAdmin() || (int) auth()->id() === (int) $rev->user_id))
                                <form action="{{ route('reviews.destroy', $rev->id) }}" method="POST" data-ajax-review-delete="true" data-confirm-title="Xóa bình luận đánh giá" data-confirm="Bạn có chắc chắn muốn xóa đánh giá này của mình không?" data-confirm-btn="Xóa đánh giá" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">Xóa</button>
                                </form>
                                @endif
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-ink">{{ $rev->comment }}</p>
                    </div>
                    @empty
                    <p id="no-reviews-placeholder" class="py-6 text-center text-sm text-mocha">Chưa có đánh giá nào cho cuốn sách này.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@if(!$canReadFull && $ebook->price > 0)
<div
    id="qr-checkout-modal"
    data-auto-open="{{ $shouldAutoOpenModal ? 'true' : 'false' }}"
    data-unlock-url="{{ route('ebooks.unlock', $ebook->id) }}"
    data-read-url="{{ route('ebooks.read', $ebook->slug) }}"
    data-is-authenticated="{{ auth()->check() ? 'true' : 'false' }}"
    data-login-url="{{ route('login') }}"
    class="fixed inset-0 z-50 hidden items-center justify-center p-4"
    style="background-color: rgba(27, 21, 16, 0.72); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);"
>
    <div class="relative w-full max-w-lg overflow-hidden rounded-3xl border border-beige bg-cream p-6 shadow-2xl transition-all sm:p-7">
        <div class="flex items-start justify-between border-b border-sand pb-4">
            <div>
                <span class="inline-block rounded-md bg-espresso px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-cream">Direct Checkout • VietQR / MoMo</span>
                <h3 class="mt-1.5 font-serif text-2xl font-bold text-espresso">Thanh toán mở khóa sách</h3>
            </div>
            <button
                type="button"
                data-close-qr-modal="true"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-beige bg-sand/60 text-espresso transition hover:bg-sand"
                aria-label="Đóng cửa sổ"
            >
                ✕
            </button>
        </div>

        <div class="mt-5 flex items-center gap-4 rounded-2xl border border-beige bg-white p-3.5 shadow-sm">
            <img src="{{ $ebook->cover_url }}" alt="{{ $ebook->title }}" class="h-20 w-14 shrink-0 rounded-lg object-cover shadow-sm">
            <div class="min-w-0 flex-1">
                <p class="truncate font-serif text-lg font-bold text-espresso">{{ $ebook->title }}</p>
                <p class="text-xs text-mocha">Tác giả: {{ $ebook->author?->name }}</p>
                <div class="mt-2 flex items-center justify-between">
                    <span class="text-xs font-medium text-taupe">Số tiền thanh toán:</span>
                    <span class="text-base font-extrabold text-rose-600">{{ $formattedPrice }}</span>
                </div>
            </div>
        </div>

        <div class="mt-5 flex flex-col items-center rounded-2xl border border-beige bg-white p-5 text-center shadow-sm">
            <div class="relative flex h-52 w-52 items-center justify-center overflow-hidden rounded-xl border border-sand bg-white p-2 shadow-inner">
                <img
                    src="{{ $vietQrUrl }}"
                    alt="Mã QR Thanh Toán"
                    class="h-full w-full object-contain"
                    onerror="this.style.display='none'; document.getElementById('fallback-qr-svg').classList.remove('hidden');"
                >
                <svg id="fallback-qr-svg" class="hidden h-44 w-44 text-espresso" viewBox="0 0 120 120" fill="currentColor">
                    <rect x="8" y="8" width="32" height="32" fill="none" stroke="currentColor" stroke-width="6"/>
                    <rect x="18" y="18" width="12" height="12"/>
                    <rect x="80" y="8" width="32" height="32" fill="none" stroke="currentColor" stroke-width="6"/>
                    <rect x="90" y="18" width="12" height="12"/>
                    <rect x="8" y="80" width="32" height="32" fill="none" stroke="currentColor" stroke-width="6"/>
                    <rect x="18" y="90" width="12" height="12"/>
                    <rect x="48" y="12" width="8" height="8"/>
                    <rect x="62" y="12" width="8" height="16"/>
                    <rect x="48" y="28" width="8" height="12"/>
                    <rect x="12" y="48" width="16" height="8"/>
                    <rect x="36" y="48" width="24" height="8"/>
                    <rect x="68" y="48" width="12" height="12"/>
                    <rect x="88" y="48" width="20" height="8"/>
                    <rect x="48" y="64" width="16" height="16"/>
                    <rect x="72" y="68" width="12" height="8"/>
                    <rect x="92" y="64" width="16" height="20"/>
                    <rect x="48" y="88" width="12" height="20"/>
                    <rect x="68" y="84" width="16" height="12"/>
                    <rect x="68" y="102" width="24" height="8"/>
                    <rect x="98" y="92" width="12" height="18"/>
                </svg>
            </div>

            <div class="mt-3 space-y-1 text-xs text-mocha">
                <p class="font-semibold text-espresso">Hướng dẫn thanh toán nhanh:</p>
                <p>1. Mở ứng dụng Ngân hàng (VietQR) hoặc Ví MoMo và quét mã QR phía trên.</p>
                <p>2. Nội dung chuyển khoản: <strong class="rounded bg-sand px-1.5 py-0.5 font-mono text-espresso">{{ $transferMemo }}</strong></p>
                <p>3. Sau khi quét mã, bấm nút <strong>"Xác nhận đã thanh toán"</strong> bên dưới để mở khóa sách tức thì.</p>
            </div>
        </div>

        <p id="qr-checkout-error" class="mt-3 hidden text-center text-xs font-semibold text-rose-600"></p>

        <div class="mt-5 flex items-center gap-3">
            <button
                type="button"
                data-close-qr-modal="true"
                class="w-1/3 rounded-xl border border-beige bg-sand/60 py-3 text-xs font-semibold text-espresso transition hover:bg-sand"
            >
                Để sau
            </button>
            <button
                id="confirm-qr-payment-btn"
                type="button"
                style="background-color: #2563eb; color: #ffffff;"
                class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl py-3 px-4 text-xs font-bold text-white shadow-md transition hover:opacity-95 disabled:opacity-60"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span id="confirm-qr-payment-text">Xác nhận đã thanh toán</span>
            </button>
        </div>
    </div>
</div>
@endif
@endsection
