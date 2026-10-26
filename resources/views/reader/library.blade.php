@extends('layouts.readverse')

@section('title', 'Tủ sách cá nhân & Lịch sử đọc - ReadVerse')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" x-data="{ tab: '{{ $activeTab }}' }">
    <div class="border-b border-sand pb-6">
        <h1 class="font-serif text-3xl font-semibold text-espresso sm:text-4xl">Tủ sách cá nhân & Lịch sử đọc</h1>
        <p class="mt-1 text-sm text-mocha">Theo dõi tiến độ đọc sách, danh sách yêu thích và các tác phẩm thuộc sở hữu của bạn</p>

        <div class="mt-6 flex flex-wrap gap-2.5">
            <button
                type="button"
                @click="tab = 'reading'"
                :class="tab === 'reading' ? 'bg-espresso text-cream shadow-sm' : 'border border-beige bg-sand/40 text-espresso hover:bg-sand'"
                class="inline-flex h-9 items-center gap-2 whitespace-nowrap rounded-xl px-4 text-xs font-semibold transition"
            >
                <span>Sách đang đọc</span>
                <span class="rounded-md bg-white/20 px-1.5 py-0.5 text-[10px]">{{ count($readingHistories) }}</span>
            </button>

            <button
                type="button"
                @click="tab = 'favorites'"
                :class="tab === 'favorites' ? 'bg-espresso text-cream shadow-sm' : 'border border-beige bg-sand/40 text-espresso hover:bg-sand'"
                class="inline-flex h-9 items-center gap-2 whitespace-nowrap rounded-xl px-4 text-xs font-semibold transition"
            >
                <span>Sách yêu thích</span>
                <span class="rounded-md bg-white/20 px-1.5 py-0.5 text-[10px]">{{ count($favoriteEbooks) }}</span>
            </button>

            <button
                type="button"
                @click="tab = 'my_books'"
                :class="tab === 'my_books' ? 'bg-espresso text-cream shadow-sm' : 'border border-beige bg-sand/40 text-espresso hover:bg-sand'"
                class="inline-flex h-9 items-center gap-2 whitespace-nowrap rounded-xl px-4 text-xs font-semibold transition"
            >
                <span>Sách của tôi</span>
                <span class="rounded-md bg-white/20 px-1.5 py-0.5 text-[10px]">{{ count($myBooks) }}</span>
            </button>
        </div>
    </div>

    <div x-show="tab === 'reading'" class="mt-8">
        @if(count($readingHistories) > 0)
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($readingHistories as $item)
            @if($item->ebook)
            <div class="flex gap-4 rounded-2xl border border-beige bg-white p-4 shadow-sm">
                <a href="{{ route('ebooks.show', $item->ebook->slug) }}" class="w-24 shrink-0 overflow-hidden rounded-xl bg-sand">
                    <img src="{{ $item->ebook->cover_url }}" alt="{{ $item->ebook->title }}" class="h-36 w-full object-cover">
                </a>
                <div class="flex flex-1 flex-col">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-taupe">{{ $item->ebook->category?->name }}</span>
                    <h3 class="mt-0.5 line-clamp-2 font-serif text-lg font-semibold text-espresso">
                        <a href="{{ route('ebooks.show', $item->ebook->slug) }}" class="hover:text-mocha">{{ $item->ebook->title }}</a>
                    </h3>
                    <p class="text-xs text-mocha">{{ $item->ebook->author?->name }}</p>

                    <div class="mt-3">
                        <div class="flex items-center justify-between text-xs font-medium text-espresso">
                            <span>Trang cuối: <strong>{{ $item->last_page }}</strong>{{ $item->ebook->pages > 0 ? ' / ' . $item->ebook->pages : '' }}</span>
                            <span>{{ number_format((float) $item->progress_percentage, 0) }}%</span>
                        </div>
                        <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-sand">
                            <div class="h-full bg-espresso transition-all" style="width: {{ min(100, max(2, (float) $item->progress_percentage)) }}%;"></div>
                        </div>
                    </div>

                    <div class="mt-auto pt-3 flex items-center justify-between">
                        <span class="text-[11px] text-taupe">Cập nhật {{ $item->updated_at->diffForHumans() }}</span>
                        <a href="{{ route('ebooks.read', ['slug' => $item->ebook->slug, 'page' => $item->last_page]) }}" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg bg-espresso px-3.5 text-xs font-semibold text-cream transition hover:bg-mocha">
                            <span>Đọc tiếp (Tr. {{ $item->last_page }})</span>
                        </a>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>
        @else
        <div class="rounded-2xl border border-beige bg-sand/30 py-16 text-center text-sm text-mocha">
            Bạn chưa có lịch sử đọc sách nào.
        </div>
        @endif
    </div>

    <div x-show="tab === 'favorites'" style="display: none;" class="mt-8">
        @if(count($favoriteEbooks) > 0)
        <div class="grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            @foreach($favoriteEbooks as $ebook)
            <article class="group flex flex-col">
                <a href="{{ route('ebooks.show', $ebook->slug) }}" class="relative block aspect-[3/4] overflow-hidden rounded-xl bg-sand shadow-sm ring-1 ring-beige/40 transition duration-300 group-hover:-translate-y-1 group-hover:shadow-xl">
                    <img src="{{ $ebook->cover_url }}" alt="{{ $ebook->title }}" class="h-full w-full object-cover">
                </a>
                <div class="flex flex-1 flex-col pt-3">
                    <span class="text-[10px] font-semibold uppercase tracking-[0.18em] text-taupe">{{ $ebook->category?->name }}</span>
                    <h3 class="mt-1 line-clamp-2 font-serif text-lg font-semibold leading-snug text-espresso">
                        <a href="{{ route('ebooks.show', $ebook->slug) }}" class="hover:text-mocha">{{ $ebook->title }}</a>
                    </h3>
                    <p class="mt-0.5 text-xs text-mocha">{{ $ebook->author?->name }}</p>
                    <div class="mt-auto flex items-center justify-between pt-3">
                        <span class="text-sm font-semibold text-espresso">{{ $ebook->price > 0 ? number_format($ebook->price) . ' đ' : 'Miễn phí' }}</span>
                        <div class="flex items-center gap-1.5">
                            <x-bookmark-button :ebook="$ebook" />
                            <a href="{{ route('ebooks.read', $ebook->slug) }}" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg border border-espresso px-3.5 text-xs font-medium leading-none text-espresso transition hover:bg-espresso hover:text-cream">Đọc</a>
                        </div>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
        @else
        <div class="rounded-2xl border border-beige bg-sand/30 py-16 text-center text-sm text-mocha">
            Danh sách yêu thích đang trống.
        </div>
        @endif
    </div>

    <div x-show="tab === 'my_books'" style="display: none;" class="mt-8">
        @if(count($myBooks) > 0)
        <div class="grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            @foreach($myBooks as $ebook)
            <article class="group flex flex-col">
                <a href="{{ route('ebooks.show', $ebook->slug) }}" class="relative block aspect-[3/4] overflow-hidden rounded-xl bg-sand shadow-sm ring-1 ring-beige/40 transition duration-300 group-hover:-translate-y-1 group-hover:shadow-xl">
                    <img src="{{ $ebook->cover_url }}" alt="{{ $ebook->title }}" class="h-full w-full object-cover">
                    <span class="absolute left-2 top-2 rounded-md bg-espresso/90 px-2 py-0.5 text-[10px] font-semibold text-cream">Đã sở hữu</span>
                </a>
                <div class="flex flex-1 flex-col pt-3">
                    <span class="text-[10px] font-semibold uppercase tracking-[0.18em] text-taupe">{{ $ebook->category?->name }}</span>
                    <h3 class="mt-1 line-clamp-2 font-serif text-lg font-semibold leading-snug text-espresso">
                        <a href="{{ route('ebooks.show', $ebook->slug) }}" class="hover:text-mocha">{{ $ebook->title }}</a>
                    </h3>
                    <p class="mt-0.5 text-xs text-mocha">{{ $ebook->author?->name }}</p>
                    <div class="mt-auto flex items-center justify-between pt-3">
                        <span class="text-xs font-semibold text-mocha">Đã mở khóa</span>
                        <a href="{{ route('ebooks.read', $ebook->slug) }}" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg bg-espresso px-3.5 text-xs font-medium leading-none text-cream transition hover:bg-mocha">Đọc ngay</a>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
        @else
        <div class="rounded-2xl border border-beige bg-sand/30 py-16 text-center text-sm text-mocha">
            Bạn chưa mở khóa hoặc tải lên cuốn sách nào.
        </div>
        @endif
    </div>
</div>
@endsection
