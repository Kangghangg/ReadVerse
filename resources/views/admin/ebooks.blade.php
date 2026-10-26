@extends('layouts.readverse')

@section('title', 'Quản trị hệ thống - Kho sách')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    @include('admin.partials.nav')

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            @if($status === 'trashed')
                <h1 class="font-serif text-3xl font-bold text-espresso">Thùng rác ({{ $stats['trashed_ebooks'] ?? 0 }})</h1>
                <p class="mt-0.5 text-xs text-mocha">Danh sách các tác phẩm đã tạm xóa khỏi thư viện. Bạn có thể khôi phục lại hoặc xóa vĩnh viễn.</p>
            @else
                <h1 class="font-serif text-3xl font-bold text-espresso">Quản lý Kho sách ({{ $stats['total_ebooks'] ?? 0 }})</h1>
                <p class="mt-0.5 text-xs text-mocha">Quản lý thông tin, trạng thái xuất bản và nội dung các tác phẩm trên hệ thống</p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            @if($status === 'trashed')
                <a href="{{ route('admin.ebooks.index') }}" class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-xl border border-beige bg-white px-4 text-xs font-semibold text-espresso shadow-sm transition hover:bg-sand">
                    Quay về Kho sách
                </a>
            @else
                <a href="{{ route('admin.ebooks.index', ['status' => 'trashed']) }}" class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-xl border border-beige bg-white px-4 text-xs font-semibold text-mocha shadow-sm transition hover:bg-sand hover:text-espresso">
                    Thùng rác ({{ $stats['trashed_ebooks'] ?? 0 }})
                </a>
            @endif
            <a href="{{ route('author.ebooks.create') }}" class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-xl bg-espresso px-4 text-xs font-semibold text-cream shadow-sm transition hover:bg-mocha">
                Thêm sách mới
            </a>
        </div>
    </div>

    <div class="mt-5 flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.ebooks.index') }}" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-xl px-3.5 text-xs font-semibold transition {{ empty($status) ? 'bg-espresso text-cream shadow-sm' : 'border border-beige bg-white text-espresso hover:bg-sand' }}">
            Tất cả ({{ $stats['total_ebooks'] ?? 0 }})
        </a>
        <a href="{{ route('admin.ebooks.index', ['status' => 'published']) }}" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-xl px-3.5 text-xs font-semibold transition {{ $status === 'published' ? 'bg-espresso text-cream shadow-sm' : 'border border-beige bg-white text-espresso hover:bg-sand' }}">
            Đã xuất bản ({{ $stats['published_ebooks'] ?? 0 }})
        </a>
        <a href="{{ route('admin.ebooks.index', ['status' => 'pending']) }}" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-xl px-3.5 text-xs font-semibold transition {{ $status === 'pending' ? 'bg-espresso text-cream shadow-sm' : 'border border-beige bg-white text-espresso hover:bg-sand' }}">
            Chờ duyệt ({{ $stats['pending_ebooks'] ?? 0 }})
        </a>
        <a href="{{ route('admin.ebooks.index', ['status' => 'draft']) }}" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-xl px-3.5 text-xs font-semibold transition {{ $status === 'draft' ? 'bg-espresso text-cream shadow-sm' : 'border border-beige bg-white text-espresso hover:bg-sand' }}">
            Từ chối / Bản nháp ({{ $stats['draft_ebooks'] ?? 0 }})
        </a>
        <a href="{{ route('admin.ebooks.index', ['status' => 'trashed']) }}" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-xl px-3.5 text-xs font-semibold transition {{ $status === 'trashed' ? 'bg-mocha text-cream shadow-sm' : 'border border-beige bg-white text-mocha hover:bg-sand hover:text-espresso' }}">
            Thùng rác ({{ $stats['trashed_ebooks'] ?? 0 }})
        </a>
    </div>

    <form method="GET" action="{{ route('admin.ebooks.index') }}" class="mt-4 grid grid-cols-1 gap-3 rounded-2xl border border-beige bg-white p-4 shadow-sm sm:grid-cols-4">
        <input type="text" name="search" value="{{ $search }}" placeholder="Tìm theo tên sách, tác giả..." class="h-9 rounded-xl border border-beige bg-cream px-3.5 text-xs text-ink placeholder:text-taupe">
        <select name="category" class="h-9 rounded-xl border border-beige bg-cream px-3.5 text-xs font-medium text-espresso">
            <option value="">Tất cả thể loại</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected((int) $categoryId === (int) $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="status" class="h-9 rounded-xl border border-beige bg-cream px-3.5 text-xs font-medium text-espresso">
            <option value="">Tất cả trạng thái</option>
            <option value="published" @selected($status === 'published')>Đã xuất bản ({{ $stats['published_ebooks'] ?? 0 }})</option>
            <option value="pending" @selected($status === 'pending')>Chờ duyệt ({{ $stats['pending_ebooks'] ?? 0 }})</option>
            <option value="draft" @selected($status === 'draft')>Từ chối / Bản nháp ({{ $stats['draft_ebooks'] ?? 0 }})</option>
            <option value="trashed" @selected($status === 'trashed')>Thùng rác ({{ $stats['trashed_ebooks'] ?? 0 }})</option>
        </select>
        <div class="flex gap-2">
            <button type="submit" class="inline-flex h-9 flex-1 items-center justify-center rounded-xl bg-espresso px-4 text-xs font-semibold text-cream transition hover:bg-mocha">Lọc dữ liệu</button>
            <a href="{{ route('admin.ebooks.index') }}" class="inline-flex h-9 items-center justify-center rounded-xl border border-beige bg-cream px-3.5 text-xs font-semibold text-espresso transition hover:bg-sand">Đặt lại</a>
        </div>
    </form>

    <div class="mt-6 overflow-hidden rounded-2xl border border-beige bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-sand text-left text-sm">
                <thead class="bg-sand/50 text-xs font-semibold uppercase tracking-wider text-mocha">
                    <tr>
                        <th class="px-5 py-3.5">Tác phẩm</th>
                        <th class="px-5 py-3.5">Thể loại & Tác giả</th>
                        <th class="px-5 py-3.5">Giá & Lượt xem</th>
                        <th class="px-5 py-3.5">Trạng thái</th>
                        <th class="px-5 py-3.5 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sand">
                    @forelse($ebooks as $ebook)
                        <tr>
                            <td class="px-5 py-4 align-middle">
                                <div class="flex items-center gap-3.5">
                                    <img src="{{ $ebook->cover_url }}" alt="{{ $ebook->title }}" class="h-14 w-10 shrink-0 rounded-lg border border-beige/60 object-cover shadow-sm">
                                    <div class="min-w-0">
                                        @if($ebook->trashed())
                                            <p class="font-serif text-base font-bold text-mocha line-through">{{ $ebook->title }}</p>
                                            <p class="mt-0.5 text-[11px] text-taupe">Đã chuyển vào thùng rác {{ $ebook->deleted_at?->diffForHumans() }}</p>
                                        @else
                                            <a href="{{ route('ebooks.show', $ebook->slug) }}" class="block font-serif text-base font-bold text-espresso transition hover:text-mocha">{{ $ebook->title }}</a>
                                        @endif
                                        <p class="mt-0.5 text-xs text-taupe">Người đăng: {{ $ebook->uploader?->name ?? 'Hệ thống' }} • {{ $ebook->pages }} trang</p>
                                        @if($ebook->status === 'draft' && $ebook->rejection_reason)
                                            <p class="mt-1 text-[11px] font-medium text-mocha">Phản hồi: {{ $ebook->rejection_reason }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 align-middle">
                                <span class="inline-flex items-center whitespace-nowrap rounded-lg border border-beige/80 bg-sand/60 px-2.5 py-1 text-xs font-semibold text-espresso">
                                    {{ $ebook->category?->name }}
                                </span>
                                <p class="mt-1.5 text-xs text-mocha">{{ $ebook->author?->name }}</p>
                            </td>
                            <td class="px-5 py-4 align-middle whitespace-nowrap">
                                <p class="font-semibold text-espresso">{{ $ebook->price > 0 ? number_format($ebook->price) . ' đ' : 'Miễn phí' }}</p>
                                <p class="mt-0.5 text-xs text-mocha">{{ $ebook->view_count }} lượt đọc • {{ $ebook->reviews_count ?? 0 }} đánh giá</p>
                            </td>
                            <td class="px-5 py-4 align-middle whitespace-nowrap">
                                @if($ebook->trashed())
                                    <span class="inline-flex items-center whitespace-nowrap rounded-lg border border-beige bg-cream px-3 py-1 text-xs font-semibold text-taupe">
                                        Đã xóa
                                    </span>
                                @elseif($ebook->status === 'published')
                                    <span class="inline-flex items-center whitespace-nowrap rounded-lg border border-espresso/20 bg-sand px-3 py-1 text-xs font-semibold text-espresso">
                                        Đã xuất bản
                                    </span>
                                @elseif($ebook->status === 'pending')
                                    <span class="inline-flex items-center whitespace-nowrap rounded-lg border border-mocha/30 bg-cream px-3 py-1 text-xs font-semibold text-mocha">
                                        Chờ duyệt
                                    </span>
                                @else
                                    <span class="inline-flex items-center whitespace-nowrap rounded-lg border border-beige bg-white px-3 py-1 text-xs font-semibold text-taupe">
                                        Từ chối
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 align-middle text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-2 whitespace-nowrap">
                                    @if($ebook->trashed())
                                        <form action="{{ route('admin.ebooks.restore', $ebook->id) }}" method="POST" class="inline-flex">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg bg-espresso px-3 text-xs font-semibold text-cream shadow-sm transition hover:bg-mocha">
                                                Khôi phục
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.ebooks.force_destroy', $ebook->id) }}" method="POST" class="inline-flex" data-confirm-title="Xóa vĩnh viễn tác phẩm" data-confirm="Tác phẩm sẽ bị xóa vĩnh viễn khỏi hệ thống và không thể khôi phục. Bạn có chắc chắn không?" data-confirm-btn="Xóa vĩnh viễn">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg border border-beige bg-white px-3 text-xs font-semibold text-mocha transition hover:border-espresso hover:bg-espresso hover:text-cream">
                                                Xóa vĩnh viễn
                                            </button>
                                        </form>
                                    @else
                                        @if($ebook->status !== 'published')
                                            <form action="{{ route('admin.ebooks.approve', $ebook->id) }}" method="POST" class="inline-flex">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg bg-espresso px-3 text-xs font-semibold text-cream shadow-sm transition hover:bg-mocha">
                                                    Duyệt
                                                </button>
                                            </form>
                                        @endif

                                        @if($ebook->status === 'pending')
                                            <button
                                                type="button"
                                                onclick="window.rvOpenRejectModal(this)"
                                                data-open-reject-modal="true"
                                                data-reject-action="{{ route('admin.ebooks.reject', $ebook->id) }}"
                                                data-ebook-title="{{ $ebook->title }}"
                                                data-author-name="{{ $ebook->uploader?->name ?? $ebook->author?->name ?? 'Tác giả' }}"
                                                class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg border border-beige bg-cream px-3 text-xs font-semibold text-espresso transition hover:bg-sand"
                                            >
                                                Từ chối
                                            </button>
                                        @endif

                                        <a href="{{ route('author.ebooks.edit', $ebook->id) }}" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg border border-beige bg-sand/50 px-3 text-xs font-semibold text-espresso transition hover:bg-sand">
                                            Sửa
                                        </a>

                                        <form action="{{ route('admin.ebooks.destroy', $ebook->id) }}" method="POST" class="inline-flex" data-confirm-title="Xóa tác phẩm" data-confirm="Bạn có chắc chắn muốn xóa tác phẩm này không?" data-confirm-btn="Xóa">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg border border-beige bg-white px-3 text-xs font-semibold text-mocha transition hover:border-espresso hover:bg-espresso hover:text-cream">
                                                Xóa
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-mocha">
                                @if($status === 'trashed')
                                    Thùng rác hiện đang trống.
                                @else
                                    Không tìm thấy tác phẩm nào phù hợp.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $ebooks->links('vendor.pagination.readverse') }}
    </div>

    @include('admin.partials.reject-modal')
</div>
@endsection
