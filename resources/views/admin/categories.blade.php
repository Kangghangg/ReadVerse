@extends('layouts.readverse')

@section('title', 'Quản lý Thể loại - ReadVerse Admin')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    @include('admin.partials.nav')

    <div id="catalog-container" class="grid grid-cols-1 gap-8 lg:grid-cols-12">
        <div class="lg:col-span-4">
            <div class="rounded-2xl border border-beige bg-white p-6 shadow-sm">
                <h2 class="font-serif text-2xl font-semibold text-espresso">Thêm thể loại mới</h2>
                <form action="{{ route('admin.categories.store') }}" method="POST" data-ajax-mutation-form="true" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold uppercase text-mocha">Tên thể loại *</label>
                        <input type="text" name="name" required placeholder="VD: Trí Tuệ Nhân Tạo" class="mt-1 w-full rounded-xl border border-beige bg-cream px-3.5 py-2 text-sm text-espresso">
                        @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-mocha">Mô tả</label>
                        <textarea name="description" rows="3" placeholder="Mô tả ngắn về danh mục..." class="mt-1 w-full rounded-xl border border-beige bg-cream px-3.5 py-2 text-sm text-espresso"></textarea>
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-espresso py-2.5 text-xs font-semibold text-cream hover:bg-mocha">
                        + Tạo thể loại
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-8">
            <div id="catalog-grid-wrapper" class="overflow-hidden rounded-2xl border border-beige bg-white shadow-sm transition-opacity duration-150">
                <table class="min-w-full divide-y divide-sand text-left text-sm">
                    <thead class="bg-sand/50 text-xs font-semibold uppercase text-mocha">
                        <tr>
                            <th class="px-5 py-4">Thể loại & Cập nhật nhanh</th>
                            <th class="px-5 py-4">Số sách</th>
                            <th class="px-5 py-4 text-right">Xóa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sand">
                        @forelse($categories as $cat)
                        <tr>
                            <td class="px-5 py-4">
                                <form action="{{ route('admin.categories.update', $cat->id) }}" method="POST" data-ajax-mutation-form="true" class="grid grid-cols-1 gap-2 sm:grid-cols-12">
                                    @csrf
                                    @method('PUT')
                                    <div class="sm:col-span-4">
                                        <input type="text" name="name" value="{{ $cat->name }}" required class="w-full rounded-lg border border-beige bg-cream px-2.5 py-1.5 text-xs font-semibold text-espresso">
                                    </div>
                                    <div class="sm:col-span-6">
                                        <input type="text" name="description" value="{{ $cat->description }}" placeholder="Mô tả..." class="w-full rounded-lg border border-beige bg-cream px-2.5 py-1.5 text-xs text-mocha">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <button type="submit" class="w-full rounded-lg bg-espresso px-2.5 py-1.5 text-xs font-semibold text-cream hover:bg-mocha">Lưu</button>
                                    </div>
                                </form>
                            </td>
                            <td class="px-5 py-4 text-xs font-semibold text-espresso">
                                {{ $cat->ebooks_count }} sách
                            </td>
                            <td class="px-5 py-4 text-right">
                                <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" data-ajax-mutation-form="true" data-confirm-title="Xóa thể loại sách" data-confirm="Bạn có chắc chắn muốn xóa thể loại &laquo;{{ $cat->name }}&raquo; khỏi hệ thống không?" data-confirm-btn="Xóa thể loại" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-rose-500">Xóa</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-mocha">Chưa có thể loại nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
