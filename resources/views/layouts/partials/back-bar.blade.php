@php
    $backUrl = null;
    $backLabel = null;

    if (request()->routeIs('ebooks.show')) {
        $backUrl = route('home');
        $backLabel = 'Về trang chính';
    }
@endphp

@if ($backUrl)
<div class="mx-auto w-full max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
    <a href="{{ $backUrl }}" aria-label="{{ $backLabel }}" title="{{ $backLabel }}" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-beige text-espresso transition hover:bg-sand">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
    </a>
</div>
@endif
