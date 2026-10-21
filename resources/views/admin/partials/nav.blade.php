<div class="mb-8 border-b border-sand pb-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <span class="inline-block rounded-md bg-espresso px-2.5 py-1 text-xs font-semibold uppercase tracking-wider text-cream">Full Admin Suite</span>
            <h1 class="mt-2 font-serif text-3xl font-semibold text-espresso sm:text-4xl">Quản trị hệ thống ReadVerse</h1>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('author.ebooks.create') }}" data-spa-nav="true" class="inline-flex items-center rounded-xl bg-espresso px-4 py-2 text-xs font-semibold text-cream shadow-sm transition hover:bg-mocha">
                + Đăng tải sách mới
            </a>
            <a href="{{ route('home') }}" data-spa-nav="true" class="inline-flex items-center rounded-xl border border-beige bg-cream px-4 py-2 text-xs font-semibold text-espresso transition hover:bg-sand">
                Về thư viện
            </a>
        </div>
    </div>

    <div id="admin-suite-tabs" class="mt-6 flex flex-wrap gap-2">
        <a href="{{ route('admin.ebooks.index') }}" data-ajax-admin-tab="true" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-semibold transition {{ request()->routeIs('admin.ebooks.index') ? 'bg-espresso text-cream shadow-sm' : 'border border-beige bg-sand/40 text-espresso hover:bg-sand' }}">
            <span>Tất cả sách</span>
        </a>
        <a href="{{ route('admin.ebooks.pending') }}" data-ajax-admin-tab="true" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-semibold transition {{ request()->routeIs('admin.ebooks.pending') ? 'bg-espresso text-cream shadow-sm' : 'border border-beige bg-sand/40 text-espresso hover:bg-sand' }}">
            <span>Kiểm duyệt bản thảo</span>
        </a>
        <a href="{{ route('admin.users.index') }}" data-ajax-admin-tab="true" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-semibold transition {{ request()->routeIs('admin.users.*') ? 'bg-espresso text-cream shadow-sm' : 'border border-beige bg-sand/40 text-espresso hover:bg-sand' }}">
            <span>Người dùng</span>
        </a>
        <a href="{{ route('admin.categories.index') }}" data-ajax-admin-tab="true" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-semibold transition {{ request()->routeIs('admin.categories.*') ? 'bg-espresso text-cream shadow-sm' : 'border border-beige bg-sand/40 text-espresso hover:bg-sand' }}">
            <span>Thể loại</span>
        </a>
        <a href="{{ route('admin.authors.index') }}" data-ajax-admin-tab="true" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-semibold transition {{ request()->routeIs('admin.authors.*') ? 'bg-espresso text-cream shadow-sm' : 'border border-beige bg-sand/40 text-espresso hover:bg-sand' }}">
            <span>Tác giả</span>
        </a>
    </div>
</div>
