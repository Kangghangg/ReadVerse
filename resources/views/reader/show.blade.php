@extends('layouts.readverse')

@section('title', $ebook->title . ' - ReadVerse')

@section('content')
@php
    $user = auth()->user();
    $canReadFull = $ebook->isUnlockedBy($user);
    $inCart = $ebook->isInCartOf($user);
    $avgRating = $ebook->reviews->count() > 0 ? number_format($ebook->reviews->avg('rating'), 1) : '0.0';
    $formattedPrice = number_format((float) $ebook->price, 0, ',', '.') . ' đ';
@endphp

<div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
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
                        class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-espresso px-5 text-center text-sm font-semibold text-cream shadow-sm transition hover:bg-mocha"
                    >
                        <span>Đọc sách ngay</span>
                    </a>
                @else
                    @auth
                        @if($inCart)
                            <a
                                href="{{ route('cart.index') }}"
                                class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-mocha px-5 text-center text-sm font-semibold text-cream shadow-sm transition hover:bg-espresso"
                            >
                                <span>Đã có trong giỏ hàng — Xem giỏ hàng</span>
                            </a>
                        @else
                            <form action="{{ route('cart.add', $ebook->id) }}" method="POST" data-ajax-cart-form="true">
                                @csrf
                                <button
                                    type="submit"
                                    class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-espresso px-5 text-center text-sm font-semibold text-cream shadow-sm transition hover:bg-mocha"
                                >
                                    <span>Thêm vào giỏ hàng — {{ $formattedPrice }}</span>
                                </button>
                            </form>
                        @endif
                        <a
                            href="{{ route('cart.index') }}"
                            class="inline-flex h-10 w-full items-center justify-center rounded-xl border border-beige bg-sand/50 px-4 text-center text-xs font-semibold text-espresso transition hover:bg-sand"
                        >
                            <span>Đi tới Giỏ hàng & Thanh toán</span>
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-espresso px-5 text-center text-sm font-semibold text-cream shadow-sm transition hover:bg-mocha"
                        >
                            <span>Đăng nhập để thêm vào giỏ ({{ $formattedPrice }})</span>
                        </a>
                    @endauth
                @endif
            </div>
        </div>

        <div class="md:col-span-2 flex flex-col">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center whitespace-nowrap rounded-lg border border-beige/80 bg-sand/70 px-3 py-1 text-xs font-semibold text-espresso">
                    {{ $ebook->category?->name }}
                </span>
                <span id="unlocked-badge" class="{{ ($canReadFull && $ebook->price > 0) ? 'inline-flex' : 'hidden' }} items-center whitespace-nowrap rounded-lg border border-espresso/20 bg-sand px-3 py-1 text-xs font-semibold text-espresso">
                    Đã sở hữu
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
                        <label class="text-xs font-semibold uppercase tracking-wider text-espresso">Chọn mức đánh giá:</label>
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
                        <button type="submit" id="ajax-review-submit" class="inline-flex h-9 items-center justify-center rounded-xl bg-espresso px-5 text-xs font-semibold text-cream transition hover:bg-mocha">
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
                                <span class="rounded-md border border-beige bg-sand/60 px-2 py-0.5 text-xs font-bold text-espresso">{{ $rev->rating }} / 5</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-taupe">{{ $rev->updated_at->diffForHumans() }}</span>
                                @if(auth()->check() && (auth()->user()->isAdmin() || (int) auth()->id() === (int) $rev->user_id))
                                <form action="{{ route('reviews.destroy', $rev->id) }}" method="POST" data-ajax-review-delete="true" data-confirm-title="Xóa bình luận đánh giá" data-confirm="Bạn có chắc chắn muốn xóa đánh giá này của mình không?" data-confirm-btn="Xóa đánh giá" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-mocha hover:text-espresso hover:underline">Xóa</button>
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
@endsection
