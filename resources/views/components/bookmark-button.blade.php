@props(['ebook'])

@php
    $isBookmarked = auth()->check() && auth()->user()->bookmarks->contains('id', $ebook->id);
@endphp

<button
    type="button"
    data-bookmark-id="{{ $ebook->id }}"
    data-book-title="{{ $ebook->title }}"
    data-endpoint="{{ route('bookmarks.toggle', $ebook->id) }}"
    aria-pressed="{{ $isBookmarked ? 'true' : 'false' }}"
    class="js-bookmark-toggle inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border transition {{ $isBookmarked ? 'border-rose-300 bg-rose-50/80 text-rose-600' : 'border-beige bg-sand/70 text-mocha hover:bg-sand' }}"
    title="Lưu vào tủ sách yêu thích"
>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition {{ $isBookmarked ? 'fill-current text-rose-600' : '' }}" fill="{{ $isBookmarked ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
    </svg>
</button>
