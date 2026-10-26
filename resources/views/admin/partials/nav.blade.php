@php
    $pendingNavCount = \App\Models\Ebook::where('status', 'pending')->count();
    $trashedNavCount = \Illuminate\Support\Facades\Schema::hasColumn('ebooks', 'deleted_at')
        ? \App\Models\Ebook::onlyTrashed()->count()
        : 0;
    $ordersNavCount = \Illuminate\Support\Facades\Schema::hasTable('orders')
        ? \App\Models\Order::count()
        : 0;
@endphp

<div class="rounded-2xl border border-beige bg-white p-5 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-sand pb-4">
        <div>
            <span class="text-[11px] font-semibold uppercase tracking-widest text-taupe">ReadVerse Administration</span>
            <h2 class="mt-0.5 font-serif text-2xl font-bold text-espresso">Hệ thống Quản trị Thư viện</h2>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('author.ebooks.create') }}" class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-xl bg-espresso px-4 text-xs font-semibold text-cream shadow-sm transition hover:bg-mocha">
                Đăng tải sách mới
            </a>
            <a href="{{ route('admin.orders.export') }}" class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-xl border border-beige bg-cream px-4 text-xs font-semibold text-espresso transition hover:bg-sand">
                Xuất báo cáo doanh thu
            </a>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-2">
        <a
            href="{{ route('admin.ebooks.pending') }}"
            class="inline-flex h-9 items-center justify-center gap-2 whitespace-nowrap rounded-xl px-4 text-xs font-semibold transition {{ request()->routeIs('admin.ebooks.pending') ? 'bg-espresso text-cream shadow-sm' : 'border border-beige bg-cream/60 text-espresso hover:bg-sand' }}"
        >
            <span>Duyệt bản thảo</span>
            @if($pendingNavCount > 0)
                <span class="inline-flex h-5 min-w-[20px] items-center justify-center rounded-full px-1.5 text-[10px] font-bold {{ request()->routeIs('admin.ebooks.pending') ? 'bg-cream text-espresso' : 'bg-sand text-espresso' }}">{{ $pendingNavCount }}</span>
            @endif
        </a>

        <a
            href="{{ route('admin.ebooks.index') }}"
            class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-xl px-4 text-xs font-semibold transition {{ request()->routeIs('admin.ebooks.index') && request('status') !== 'trashed' ? 'bg-espresso text-cream shadow-sm' : 'border border-beige bg-cream/60 text-espresso hover:bg-sand' }}"
        >
            <span>Kho sách</span>
        </a>

        <a
            href="{{ route('admin.categories.index') }}"
            class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-xl px-4 text-xs font-semibold transition {{ request()->routeIs('admin.categories.*') ? 'bg-espresso text-cream shadow-sm' : 'border border-beige bg-cream/60 text-espresso hover:bg-sand' }}"
        >
            <span>Thể loại</span>
        </a>

        <a
            href="{{ route('admin.authors.index') }}"
            class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-xl px-4 text-xs font-semibold transition {{ request()->routeIs('admin.authors.*') ? 'bg-espresso text-cream shadow-sm' : 'border border-beige bg-cream/60 text-espresso hover:bg-sand' }}"
        >
            <span>Tác giả</span>
        </a>

        <a
            href="{{ route('admin.users.index') }}"
            class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-xl px-4 text-xs font-semibold transition {{ request()->routeIs('admin.users.*') ? 'bg-espresso text-cream shadow-sm' : 'border border-beige bg-cream/60 text-espresso hover:bg-sand' }}"
        >
            <span>Người dùng</span>
        </a>

        <a
            href="{{ route('admin.orders.index') }}"
            class="inline-flex h-9 items-center justify-center gap-2 whitespace-nowrap rounded-xl px-4 text-xs font-semibold transition {{ request()->routeIs('admin.orders.*') ? 'bg-espresso text-cream shadow-sm' : 'border border-beige bg-cream/60 text-espresso hover:bg-sand' }}"
        >
            <span>Đơn hàng & Doanh thu</span>
            <span class="inline-flex h-5 min-w-[20px] items-center justify-center rounded-full px-1.5 text-[10px] font-bold {{ request()->routeIs('admin.orders.*') ? 'bg-cream text-espresso' : 'bg-sand text-espresso' }}">{{ $ordersNavCount }}</span>
        </a>

        <a
            href="{{ route('admin.ebooks.index', ['status' => 'trashed']) }}"
            class="inline-flex h-9 items-center justify-center gap-2 whitespace-nowrap rounded-xl px-4 text-xs font-semibold transition {{ request()->routeIs('admin.ebooks.index') && request('status') === 'trashed' ? 'bg-mocha text-cream shadow-sm' : 'border border-beige bg-cream/60 text-mocha hover:bg-sand hover:text-espresso' }}"
        >
            <span>Thùng rác</span>
            @if($trashedNavCount > 0)
                <span class="inline-flex h-5 min-w-[20px] items-center justify-center rounded-full px-1.5 text-[10px] font-bold {{ request()->routeIs('admin.ebooks.index') && request('status') === 'trashed' ? 'bg-cream text-espresso' : 'bg-sand text-espresso' }}">{{ $trashedNavCount }}</span>
            @endif
        </a>
    </div>
</div>
