@if ($paginator->hasPages())
<nav role="navigation" aria-label="Phân trang" class="flex items-center justify-center gap-1.5 text-sm">
    @if ($paginator->onFirstPage())
    <span class="rounded-lg border border-sand px-3.5 py-2 text-taupe/60">&lsaquo;</span>
    @else
    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="rounded-lg border border-beige bg-white px-3.5 py-2 text-espresso transition hover:bg-sand">&lsaquo;</a>
    @endif

    @foreach($elements as $element)
    @if (is_string($element))
    <span class="px-2 text-taupe">{{ $element }}</span>
    @endif
    @if (is_array($element))
    @foreach($element as $page => $url)
    @if ($page == $paginator->currentPage())
    <span aria-current="page" class="rounded-lg bg-espresso px-3.5 py-2 font-medium text-cream">{{ $page }}</span>
    @else
    <a href="{{ $url }}" class="rounded-lg border border-beige bg-white px-3.5 py-2 text-espresso transition hover:bg-sand">{{ $page }}</a>
    @endif
    @endforeach
    @endif
    @endforeach

    @if ($paginator->hasMorePages())
    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="rounded-lg border border-beige bg-white px-3.5 py-2 text-espresso transition hover:bg-sand">&rsaquo;</a>
    @else
    <span class="rounded-lg border border-sand px-3.5 py-2 text-taupe/60">&rsaquo;</span>
    @endif
</nav>
@endif
