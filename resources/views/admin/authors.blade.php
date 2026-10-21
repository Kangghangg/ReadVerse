@extends('layouts.readverse')

@section('title', 'Quản lý Tác giả - ReadVerse Admin')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    @include('admin.partials.nav')

    <div id="catalog-container" class="grid grid-cols-1 gap-8 lg:grid-cols-12">
        <div class="lg:col-span-4">
            <div class="rounded-2xl border border-beige bg-white p-6 shadow-sm">
                <h2 class="font-serif text-2xl font-semibold text-espresso">Thêm tác giả mới</h2>
                <form action="{{ route('admin.authors.store') }}" method="POST" enctype="multipart/form-data" data-ajax-mutation-form="true" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold uppercase text-mocha">Tên tác giả *</label>
                        <input type="text" name="name" required placeholder="VD: Nam Cao" class="mt-1 w-full rounded-xl border border-beige bg-cream px-3.5 py-2 text-sm text-espresso">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-mocha">Tiểu sử</label>
                        <textarea name="bio" rows="3" placeholder="Giới thiệu tiểu sử tác giả..." class="mt-1 w-full rounded-xl border border-beige bg-cream px-3.5 py-2 text-sm text-espresso"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-mocha">Ảnh đại diện</label>
                        <input type="file" name="avatar" accept="image/*" class="mt-1 block w-full text-xs text-mocha file:mr-3 file:rounded-lg file:border-0 file:bg-sand file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-espresso">
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-espresso py-2.5 text-xs font-semibold text-cream hover:bg-mocha">
                        + Thêm tác giả
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-8">
            <div id="catalog-grid-wrapper" class="overflow-hidden rounded-2xl border border-beige bg-white shadow-sm transition-opacity duration-150">
                <table class="min-w-full divide-y divide-sand text-left text-sm">
                    <thead class="bg-sand/50 text-xs font-semibold uppercase text-mocha">
                        <tr>
                            <th class="px-5 py-4">Tác giả & Cập nhật thông tin</th>
                            <th class="px-5 py-4">Tác phẩm</th>
                            <th class="px-5 py-4 text-right">Xóa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sand">
                        @forelse($authors as $authorItem)
                        <tr>
                            <td class="px-5 py-4">
                                <form action="{{ route('admin.authors.update', $authorItem->id) }}" method="POST" enctype="multipart/form-data" data-ajax-mutation-form="true" class="space-y-2">
                                    @csrf
                                    @method('PUT')
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $authorItem->avatar_url }}" alt="{{ $authorItem->name }}" class="h-10 w-10 rounded-full object-cover border border-beige">
                                        <input type="text" name="name" value="{{ $authorItem->name }}" required class="flex-1 rounded-lg border border-beige bg-cream px-2.5 py-1.5 text-xs font-semibold text-espresso">
                                        <input type="file" name="avatar" accept="image/*" class="w-40 text-[11px] text-mocha">
                                        <button type="submit" class="rounded-lg bg-espresso px-3 py-1.5 text-xs font-semibold text-cream hover:bg-mocha">Lưu</button>
                                    </div>
                                    <input type="text" name="bio" value="{{ $authorItem->bio }}" placeholder="Tiểu sử tác giả..." class="w-full rounded-lg border border-beige bg-cream px-2.5 py-1.5 text-xs text-mocha">
                                </form>
                            </td>
                            <td class="px-5 py-4 text-xs font-semibold text-espresso whitespace-nowrap">
                                {{ $authorItem->ebooks_count }} sách
                            </td>
                            <td class="px-5 py-4 text-right">
                                <form action="{{ route('admin.authors.destroy', $authorItem->id) }}" method="POST" data-ajax-mutation-form="true" data-confirm-title="Xóa tác giả" data-confirm="Bạn có chắc chắn muốn xóa tác giả &laquo;{{ $authorItem->name }}&raquo; cùng các tác phẩm liên quan không?" data-confirm-btn="Xóa tác giả" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-rose-500">Xóa</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-mocha">Chưa có tác giả nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div id="catalog-pagination" class="mt-6">
                {{ $authors->links('vendor.pagination.readverse') }}
            </div>
        </div>
    </div>
</div>
@endsection
