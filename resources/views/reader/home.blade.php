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
        <a href="{{ route('home', array_filter(['search' => $search, 'author' => $authorId ?? null, 'price_sort' => $priceSort ?? null, 'sort' => $sort ?? null])) }}" data-ajax-catalog-link="true" class="inline-flex h-8 items-center rounded-lg px-4 text-xs font-medium transition {{ empty($categoryId) && empty($favorites) ? 'bg-espresso text-cream' : 'border border-beige text-mocha hover:bg-sand' }}">
            Tất cả
        </a>

        @auth
        <a href="{{ route('home', array_filter(['favorites' => 1, 'category' => $categoryId ?? null, 'search' => $search, 'author' => $authorId ?? null, 'price_sort' => $priceSort ?? null, 'sort' => $sort ?? null])) }}" data-ajax-catalog-link="true" class="inline-flex h-8 items-center gap-1.5 rounded-lg px-4 text-xs font-medium transition {{ !empty($favorites) ? 'bg-rose-600 text-cream shadow-sm' : 'border border-rose-300 bg-rose-50/60 text-rose-700 hover:bg-rose-100/70' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 fill-current" viewBox="0 0 24 24">
                <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <span>Đã thích</span>
            <span id="fav-tab-count" class="ml-0.5 rounded-md px-1.5 py-0.5 text-[10px] font-semibold {{ !empty($favorites) ? 'bg-white/20 text-cream' : 'bg-rose-200/80 text-rose-800' }}">{{ $favoriteCount ?? 0 }}</span>
        </a>
        @else
        <a href="{{ route('login') }}" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-rose-300 bg-rose-50/60 px-4 text-xs font-medium text-rose-700 transition hover:bg-rose-100/70">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <span>Đã thích</span>
        </a>
        @endauth

        @foreach($categories as $category)
        <a href="{{ route('home', array_filter(['category' => $category->id, 'favorites' => !empty($favorites) ? 1 : null, 'search' => $search, 'author' => $authorId ?? null, 'price_sort' => $priceSort ?? null, 'sort' => $sort ?? null])) }}" data-ajax-catalog-link="true" class="inline-flex h-8 items-center rounded-lg px-4 text-xs font-medium transition {{ (int) $categoryId === (int) $category->id ? 'bg-espresso text-cream' : 'border border-beige text-mocha hover:bg-sand' }}">
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
                <div class="mt-auto flex items-center justify-between pt-3">
                    <span class="text-sm font-semibold text-espresso">{{ $ebook->price > 0 ? number_format($ebook->price) . ' đ' : 'Miễn phí' }}</span>
                    <div class="flex items-center gap-1.5">
                        <x-bookmark-button :ebook="$ebook" />
                        <a href="{{ route('ebooks.read', $ebook->slug) }}" class="inline-flex h-8 items-center justify-center rounded-lg border border-espresso px-3.5 text-xs font-medium leading-none text-espresso transition hover:bg-espresso hover:text-cream">Đọc</a>
                    </div>
                </div>
            </div>
        </article>
        @empty
        <div class="col-span-full py-24 text-center text-sm text-mocha">
            @if(!empty($favorites))
                Bạn chưa lưu cuốn sách nào vào mục Đã thích. Hãy bấm biểu tượng trái tim trên sách để lưu lại nhé!
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
