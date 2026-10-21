@props([
    'categories' => collect(),
    'authors' => collect(),
    'categoryId' => null,
    'authorId' => null,
    'search' => null,
    'sort' => 'latest',
    'priceSort' => null,
    'favorites' => false,
])

<form method="GET" action="{{ route('home') }}" data-ajax-catalog-form="true" class="mt-6 flex flex-col gap-3 rounded-2xl border border-beige bg-sand/50 p-4 lg:flex-row lg:items-center">
    @if(!empty($search))
        <input type="hidden" name="search" value="{{ $search }}">
    @endif
    @if(!empty($favorites))
        <input type="hidden" name="favorites" value="1">
    @endif

    <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <select name="category" class="w-full rounded-xl border border-beige bg-cream px-3 py-2.5 text-sm text-espresso focus:border-espresso focus:outline-none focus:ring-1 focus:ring-espresso">
                <option value="">Tất cả thể loại</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ (int) $categoryId === (int) $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }} ({{ $cat->ebooks_count ?? 0 }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <select name="author" class="w-full rounded-xl border border-beige bg-cream px-3 py-2.5 text-sm text-espresso focus:border-espresso focus:outline-none focus:ring-1 focus:ring-espresso">
                <option value="">Tất cả tác giả</option>
                @foreach($authors as $authorItem)
                    <option value="{{ $authorItem->id }}" {{ (int) $authorId === (int) $authorItem->id ? 'selected' : '' }}>
                        {{ $authorItem->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <select name="price_sort" class="w-full rounded-xl border border-beige bg-cream px-3 py-2.5 text-sm text-espresso focus:border-espresso focus:outline-none focus:ring-1 focus:ring-espresso">
                <option value="" {{ empty($priceSort) ? 'selected' : '' }}>Sắp xếp giá: Mặc định</option>
                <option value="price_asc" {{ $priceSort === 'price_asc' ? 'selected' : '' }}>Giá: Thấp → Cao</option>
                <option value="price_desc" {{ $priceSort === 'price_desc' ? 'selected' : '' }}>Giá: Cao → Thấp</option>
                <option value="free" {{ $priceSort === 'free' ? 'selected' : '' }}>Sách Miễn phí (0đ)</option>
            </select>
        </div>

        <div>
            <select name="sort" class="w-full rounded-xl border border-beige bg-cream px-3 py-2.5 text-sm text-espresso focus:border-espresso focus:outline-none focus:ring-1 focus:ring-espresso">
                <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>Mới nhất</option>
                <option value="views_desc" {{ $sort === 'views_desc' ? 'selected' : '' }}>Đọc nhiều nhất</option>
            </select>
        </div>
    </div>

    <div class="flex items-center justify-end lg:shrink-0">
        <button
            type="button"
            data-reset-catalog-filter="true"
            data-reset-url="{{ route('home') }}"
            title="Khôi phục bộ lọc về mặc định"
            aria-label="Khôi phục bộ lọc về mặc định"
            style="border-radius: 9999px;"
            class="group inline-flex h-10 w-10 shrink-0 items-center justify-center bg-espresso text-cream shadow-sm transition hover:bg-mocha focus:outline-none focus:ring-2 focus:ring-mocha/40"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-300 group-hover:-rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        </button>
    </div>
</form>
