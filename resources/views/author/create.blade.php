@extends('layouts.readverse')

@php
    $isEdit = isset($ebook) && $ebook->id;
@endphp

@section('title', ($isEdit ? 'Cập nhật tác phẩm' : 'Tải lên tác phẩm mới') . ' - Cổng tác giả')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
    <div class="mb-6">
        <a href="{{ route('author.ebooks.index') }}" data-spa-nav="true" class="text-xs font-medium text-espresso hover:underline">← Quay lại Cổng tác giả</a>
        <h1 class="mt-2 font-serif text-3xl font-bold text-espresso">
            {{ $isEdit ? 'Cập nhật thông tin sách điện tử' : 'Tải lên sách điện tử mới' }}
        </h1>
        <p class="mt-1 text-xs text-mocha">Hỗ trợ tải tệp PDF dung lượng lớn (tự động phân mảnh từng 1MB không giới hạn dung lượng).</p>
    </div>

    @if ($errors->any())
    <div class="mb-6 rounded-2xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-900 shadow-sm">
        <p class="font-bold">Vui lòng kiểm tra lại các thông tin sau:</p>
        <ul class="mt-1.5 list-disc space-y-1 pl-5 text-xs">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form
        id="author-ebook-form"
        action="{{ $isEdit ? route('author.ebooks.update', $ebook->id) : route('author.ebooks.store') }}"
        data-chunk-endpoint="{{ route('author.ebooks.upload-chunk') }}"
        method="POST"
        enctype="multipart/form-data"
        class="space-y-6 rounded-2xl border border-beige bg-white p-6 shadow-sm"
    >
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <input type="hidden" name="pre_uploaded_file" id="pre-uploaded-file-input" value="{{ old('pre_uploaded_file') }}">
        <input type="hidden" name="pre_uploaded_cover" id="pre-uploaded-cover-input" value="{{ old('pre_uploaded_cover') }}">

        <div>
            <label class="block text-sm font-semibold text-espresso">Tiêu đề sách *</label>
            <input type="text" name="title" required value="{{ old('title', $ebook->title ?? '') }}" placeholder="Nhập tên tác phẩm..." class="mt-1 w-full rounded-xl border-beige bg-cream shadow-sm focus:border-espresso focus:ring-espresso">
            @error('title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-semibold text-espresso">Danh mục *</label>
                <select name="category_id" required class="mt-1 w-full rounded-xl border-beige bg-cream shadow-sm focus:border-espresso focus:ring-espresso">
                    <option value="">-- Chọn danh mục --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (int) old('category_id', $ebook->category_id ?? 0) === (int) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-espresso">Tác giả (Để trống sẽ lấy tên của bạn)</label>
                <select name="author_id" class="mt-1 w-full rounded-xl border-beige bg-cream shadow-sm focus:border-espresso focus:ring-espresso">
                    <option value="">-- Tự động lấy tên: {{ auth()->user()->name }} --</option>
                    @foreach($authors as $author)
                        <option value="{{ $author->id }}" {{ (int) old('author_id', $ebook->author_id ?? 0) === (int) $author->id ? 'selected' : '' }}>{{ $author->name }}</option>
                    @endforeach
                </select>
                @error('author_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-semibold text-espresso">Số trang (Để trống sẽ tự động đếm từ PDF)</label>
                <input type="number" name="pages" min="0" value="{{ old('pages', $ebook->pages ?? '') }}" placeholder="Tự động nhận diện..." class="mt-1 w-full rounded-xl border-beige bg-cream shadow-sm focus:border-espresso focus:ring-espresso">
                @error('pages') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-espresso">Giá bán (VNĐ — Nhập 0 nếu Miễn phí)</label>
                <input type="number" name="price" min="0" value="{{ old('price', isset($ebook) ? (int) $ebook->price : 0) }}" class="mt-1 w-full rounded-xl border-beige bg-cream shadow-sm focus:border-espresso focus:ring-espresso">
                @error('price') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-espresso">Mô tả tác phẩm *</label>
            <textarea name="description" rows="5" required placeholder="Giới thiệu tóm tắt nội dung cuốn sách..." class="mt-1 w-full rounded-xl border-beige bg-cream shadow-sm focus:border-espresso focus:ring-espresso">{{ old('description', $ebook->description ?? '') }}</textarea>
            @error('description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-espresso">Ảnh bìa (JPG, PNG, WebP — Tùy chọn)</label>
            <input
                id="cover-upload-input"
                type="file"
                name="cover_image"
                accept="image/*"
                data-chunk-file="cover"
                class="mt-1 block w-full text-sm text-mocha file:mr-4 file:rounded-lg file:border-0 file:bg-sand file:px-4 file:py-2 file:text-xs file:font-semibold file:text-espresso hover:file:bg-beige"
            >
            <p id="cover-upload-status" class="mt-1.5 hidden text-xs font-semibold text-espresso"></p>
            @error('cover_image') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-espresso">
                Tệp sách PDF {{ $isEdit ? '(Để trống nếu giữ nguyên tệp cũ)' : '*' }}
            </label>
            <input
                id="pdf-upload-input"
                type="file"
                name="file_path"
                accept=".pdf,.epub,application/pdf"
                data-chunk-file="ebook"
                class="mt-1 block w-full text-sm text-mocha file:mr-4 file:rounded-lg file:border-0 file:bg-sand file:px-4 file:py-2 file:text-xs file:font-semibold file:text-espresso hover:file:bg-beige"
            >
            <div id="pdf-progress-wrapper" class="mt-2.5 hidden">
                <div class="flex items-center justify-between text-xs font-semibold text-espresso">
                    <span id="pdf-upload-status">Đang chuẩn bị tải tệp...</span>
                    <span id="pdf-upload-percent">0%</span>
                </div>
                <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-sand">
                    <div id="pdf-upload-bar" class="h-full bg-espresso transition-all duration-150" style="width: 0%;"></div>
                </div>
            </div>
            @if($isEdit && !empty($ebook->file_path))
                <p class="mt-1 text-xs text-mocha">Tệp hiện tại: <a href="{{ asset('storage/' . $ebook->file_path) }}" target="_blank" class="font-semibold text-espresso underline">{{ basename($ebook->file_path) }}</a></p>
            @endif
            @error('file_path') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <button id="author-submit-btn" type="submit" class="w-full rounded-xl bg-espresso py-3.5 text-sm font-bold text-cream shadow transition hover:bg-mocha disabled:opacity-50">
            {{ $isEdit ? 'Lưu cập nhật tác phẩm' : 'Gửi bản thảo phê duyệt' }}
        </button>
    </form>
</div>
@endsection
