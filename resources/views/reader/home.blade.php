@extends('layouts.readverse')

@section('title', 'ReadVerse')

@section('content')
@php
    $bgCandidates = ['images/Background.JPEG', 'images/Background.jpg', 'images/Background.jpeg', 'images/Background.png', 'images/banner.jpg'];
    $bgPath = 'images/Background.JPEG';
    foreach ($bgCandidates as $candidate) {
        if (file_exists(public_path($candidate))) {
            $bgPath = $candidate;
            break;
        }
    }
    $bgVersion = file_exists(public_path($bgPath)) ? filemtime(public_path($bgPath)) : time();
    $currentUser = auth()->user();
@endphp
<section class="relative z-30 isolate flex min-h-[440px] items-center justify-center bg-espresso sm:min-h-[520px]">
    <img src="{{ asset($bgPath) }}?v={{ $bgVersion }}" alt="ReadVerse Banner" class="pointer-events-none absolute inset-0 z-0 h-full w-full select-none object-cover object-center">
    <div class="rv-hero-vignette absolute inset-0 z-10"></div>
    <div class="relative z-20 w-full max-w-3xl px-4 text-center">
        <h1 class="font-serif text-4xl font-semibold tracking-wide text-cream drop-shadow-[0_4px_16px_rgba(27,21,16,0.85)] sm:text-6xl">Thư viện ReadVerse</h1>
        <form id="hero-search-form" method="GET" action="{{ route('home') }}" data-ajax-catalog-form="true" class="relative z-50 mx-auto mt-10 flex max-w-xl items-center gap-2 rounded-xl bg-cream/95 p-1.5 shadow-2xl ring-1 ring-beige/50 backdrop-blur-sm">
            @if(!empty($categoryId))
            <input type="hidden" name="category" value="{{ $categoryId }}">
            @endif
            @if(!empty($authorId))
            <input type="hidden" name="author" value="{{ $authorId }}">
            @endif
            @if(!empty($priceSort))
            <input type="hidden" name="price_sort" value="{{ $priceSort }}">
            @endif
            @if(!empty($sort))
            <input type="hidden" name="sort" value="{{ $sort }}">
            @endif
            @if(!empty($favorites))
            <input type="hidden" name="favorites" value="1">
            @endif
            <div class="flex-1">
                <input id="hero-search-input" type="text" name="search" value="{{ $search }}" data-live-search="true" autocomplete="off" placeholder="Tên sách hoặc tác giả" class="w-full border-0 bg-transparent px-4 py-2.5 text-sm text-ink placeholder:text-taupe focus:ring-0">
            </div>
            <button type="submit" class="rounded-lg bg-espresso px-6 py-2.5 text-sm font-medium text-cream transition hover:bg-mocha">Tìm</button>
        </form>
    </div>
</section>

<div id="catalog-container" class="relative z-10 mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center gap-2 border-b border-sand pb-8">
        <a href="{{ route('home', array_filter(['search' => $search, 'author' => $authorId ?? null, 'price_sort' => $priceSort ?? null, 'sort' => $sort ?? null])) }}" data-ajax-catalog-link="true" class="inline-flex h-8 items-center whitespace-nowrap rounded-lg px-4 text-xs font-medium transition {{ empty($categoryId) && empty($favorites) ? 'bg-espresso text-cream' : 'border border-beige text-mocha hover:bg-sand' }}">
            Tất cả
        </a>

        @auth
        <a href="{{ route('home', array_filter(['favorites' => 1, 'category' => $categoryId ?? null, 'search' => $search, 'author' => $authorId ?? null, 'price_sort' => $priceSort ?? null, 'sort' => $sort ?? null])) }}" data-ajax-catalog-link="true" class="inline-flex h-8 items-center gap-1.5 whitespace-nowrap rounded-lg px-4 text-xs font-medium transition {{ !empty($favorites) ? 'bg-mocha text-cream shadow-sm' : 'border border-beige bg-sand/40 text-espresso hover:bg-sand' }}">
            <span>Đã thích</span>
            <span id="fav-tab-count" class="ml-0.5 rounded-md px-1.5 py-0.5 text-[10px] font-semibold {{ !empty($favorites) ? 'bg-white/20 text-cream' : 'bg-sand text-espresso' }}">{{ $favoriteCount ?? 0 }}</span>
        </a>
        @else
        <a href="{{ route('login') }}" class="inline-flex h-8 items-center gap-1.5 whitespace-nowrap rounded-lg border border-beige bg-sand/40 px-4 text-xs font-medium text-espresso transition hover:bg-sand">
            <span>Đã thích</span>
        </a>
        @endauth

        @foreach($categories as $category)
        <a href="{{ route('home', array_filter(['category' => $category->id, 'favorites' => !empty($favorites) ? 1 : null, 'search' => $search, 'author' => $authorId ?? null, 'price_sort' => $priceSort ?? null, 'sort' => $sort ?? null])) }}" data-ajax-catalog-link="true" class="inline-flex h-8 items-center whitespace-nowrap rounded-lg px-4 text-xs font-medium transition {{ (int) $categoryId === (int) $category->id ? 'bg-espresso text-cream' : 'border border-beige text-mocha hover:bg-sand' }}">
            {{ $category->name }}
        </a>
        @endforeach
    </div>

    <x-catalog-filter
        :categories="$categories"
        :authors="$authors ?? collect()"
        :categoryId="$categoryId"
        :authorId="$authorId ?? null"
        :search="$search"
        :sort="$sort ?? 'latest'"
        :priceSort="$priceSort ?? null"
        :favorites="$favorites ?? false"
    />

    <div id="catalog-grid-wrapper" class="mt-10 grid grid-cols-2 gap-x-6 gap-y-10 transition-opacity duration-200 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
        @forelse($ebooks as $ebook)
        @php
            $cardUnlocked = $ebook->isUnlockedBy($currentUser);
            $cardInCart = !$cardUnlocked && $ebook->isInCartOf($currentUser);
        @endphp
        <article class="group flex flex-col">
            <a href="{{ route('ebooks.show', $ebook->slug) }}" class="relative block aspect-[3/4] overflow-hidden rounded-xl bg-sand shadow-sm ring-1 ring-beige/40 transition duration-300 group-hover:-translate-y-1 group-hover:shadow-xl">
                <img src="{{ $ebook->cover_url }}" alt="{{ $ebook->title }}" loading="lazy" class="h-full w-full object-cover">
            </a>
            <div class="flex flex-1 flex-col pt-3">
                <span class="text-[10px] font-semibold uppercase tracking-[0.18em] text-taupe">{{ $ebook->category?->name }}</span>
                <h3 class="mt-1 line-clamp-2 font-serif text-lg font-semibold leading-snug text-espresso">
                    <a href="{{ route('ebooks.show', $ebook->slug) }}" class="transition hover:text-mocha">{{ $ebook->title }}</a>
                </h3>
                <p class="mt-0.5 text-xs text-mocha">{{ $ebook->author?->name }}</p>
                <div class="mt-auto flex items-center justify-between gap-2 pt-3">
                    <span class="whitespace-nowrap text-sm font-semibold text-espresso">{{ $ebook->price > 0 ? number_format($ebook->price) . ' đ' : 'Miễn phí' }}</span>
                    <div class="flex items-center gap-1.5">
                        <x-bookmark-button :ebook="$ebook" />
                        @if($cardUnlocked)
                            <a href="{{ route('ebooks.read', $ebook->slug) }}" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg border border-espresso px-3 text-xs font-medium leading-none text-espresso transition hover:bg-espresso hover:text-cream">Đọc</a>
                        @elseif($cardInCart)
                            <a href="{{ route('cart.index') }}" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg border border-beige bg-sand px-2.5 text-[11px] font-semibold leading-none text-espresso transition hover:bg-beige">Trong giỏ</a>
                        @elseif($currentUser)
                            <form action="{{ route('cart.add', $ebook->id) }}" method="POST" data-ajax-cart-form="true" data-compact-cart="true" class="inline-flex">
                                @csrf
                                <button type="submit" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg bg-espresso px-2.5 text-[11px] font-semibold leading-none text-cream transition hover:bg-mocha">Thêm giỏ</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg bg-espresso px-2.5 text-[11px] font-semibold leading-none text-cream transition hover:bg-mocha">Thêm giỏ</a>
                        @endif
                    </div>
                </div>
            </div>
        </article>
        @empty
        <div class="col-span-full py-24 text-center text-sm text-mocha">
            @if(!empty($favorites))
                Bạn chưa lưu cuốn sách nào vào mục Đã thích.
            @else
                Không tìm thấy sách phù hợp.
            @endif
        </div>
        @endforelse
    </div>

    <div id="catalog-pagination" class="mt-14">
        {{ $ebooks->appends(request()->query())->links('vendor.pagination.readverse') }}
    </div>
</div>
@endsection
