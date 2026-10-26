@extends('layouts.readverse')

@section('title', isset($ebook) ? 'Chỉnh sửa sách điện tử - ReadVerse' : 'Đăng tải sách mới - ReadVerse')

@section('content')
@php
    $isEdit = isset($ebook);
@endphp

<div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-sand pb-5">
        <div>
            <span class="text-xs font-semibold uppercase tracking-widest text-taupe">ReadVerse Publishing</span>
            <h1 class="mt-1 font-serif text-3xl font-bold text-espresso">
                {{ $isEdit ? 'Chỉnh sửa tác phẩm: ' . $ebook->title : 'Đăng tải Sách Điện Tử Mới' }}
            </h1>
            <p class="mt-1 text-xs text-mocha">
                {{ auth()->user()->isAdmin() ? 'Sách đăng tải từ Quản trị viên sẽ được xuất bản trực tiếp lên thư viện.' : 'Bản thảo sẽ được gửi tới Quản trị viên phê duyệt trước khi xuất bản công khai.' }}
            </p>
        </div>

        <a href="{{ auth()->user()->isAdmin() ? route('admin.ebooks.index') : route('author.ebooks.index') }}" class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-xl border border-beige bg-white px-4 text-xs font-semibold text-espresso shadow-sm transition hover:bg-sand">
            Quay lại danh sách
        </a>
    </div>

    <div class="mt-6 rounded-3xl border border-beige bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="font-serif text-xl font-bold text-espresso">Tra cứu nhanh thông tin tác phẩm</h2>
                <p class="mt-0.5 text-xs text-mocha">Nhập tên sách hoặc mã ISBN để tự động điền thông tin từ hệ thống dữ liệu OpenLibrary & Google Books</p>
            </div>
        </div>

        <div class="mt-3.5 flex flex-col gap-2.5 sm:flex-row">
            <input
                id="api-book-query"
                type="text"
                placeholder="Nhập tên sách hoặc mã ISBN (VD: Clean Code, Atomic Habits, 9780132350884...)"
                class="h-10 flex-1 rounded-xl border border-beige bg-cream/60 px-4 text-sm text-ink placeholder:text-taupe focus:border-espresso focus:bg-white focus:ring-espresso"
            >
            <button
                type="button"
                id="api-lookup-btn"
                onclick="window.rvLookupExternalBookApi()"
                class="inline-flex h-10 items-center justify-center whitespace-nowrap rounded-xl bg-espresso px-5 text-xs font-semibold text-cream shadow-sm transition hover:bg-mocha"
            >
                Tìm kiếm & Điền tự động
            </button>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-1.5 text-[11px]">
            <span class="font-semibold text-mocha">Gợi ý tra cứu:</span>
            <button type="button" onclick="window.rvQuickApiSearch('Clean Code Robert Martin')" class="rounded-lg border border-beige bg-cream/50 px-2.5 py-1 font-medium text-espresso transition hover:bg-sand">Clean Code</button>
            <button type="button" onclick="window.rvQuickApiSearch('The Pragmatic Programmer')" class="rounded-lg border border-beige bg-cream/50 px-2.5 py-1 font-medium text-espresso transition hover:bg-sand">The Pragmatic Programmer</button>
            <button type="button" onclick="window.rvQuickApiSearch('Atomic Habits James Clear')" class="rounded-lg border border-beige bg-cream/50 px-2.5 py-1 font-medium text-espresso transition hover:bg-sand">Atomic Habits</button>
            <button type="button" onclick="window.rvQuickApiSearch('Design Patterns Erich Gamma')" class="rounded-lg border border-beige bg-cream/50 px-2.5 py-1 font-medium text-espresso transition hover:bg-sand">Design Patterns</button>
            <button type="button" onclick="window.rvQuickApiSearch('9780132350884')" class="rounded-lg border border-beige bg-cream/50 px-2.5 py-1 font-mono font-medium text-espresso transition hover:bg-sand">ISBN: 9780132350884</button>
        </div>

        <div id="api-lookup-status" class="mt-3 hidden rounded-xl border border-beige bg-cream/60 px-4 py-2.5 text-xs font-semibold text-espresso"></div>
        <div id="api-lookup-results" class="mt-3 hidden space-y-2.5"></div>
    </div>

    <form
        method="POST"
        action="{{ $isEdit ? route('author.ebooks.update', $ebook->id) : route('author.ebooks.store') }}"
        enctype="multipart/form-data"
        class="mt-6 space-y-6 rounded-3xl border border-beige bg-white p-6 shadow-sm sm:p-8"
    >
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <input type="hidden" name="pre_uploaded_cover" id="pre_uploaded_cover" value="{{ old('pre_uploaded_cover') }}">
        <input type="hidden" name="pre_uploaded_file" id="pre_uploaded_file" value="{{ old('pre_uploaded_file') }}">

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="title" class="block text-xs font-semibold uppercase tracking-wider text-espresso">Tiêu đề tác phẩm *</label>
                <input
                    id="title"
                    name="title"
                    type="text"
                    required
                    value="{{ old('title', $isEdit ? $ebook->title : '') }}"
                    placeholder="Nhập tên cuốn sách..."
                    class="mt-1.5 w-full rounded-xl border border-beige bg-cream/60 px-4 py-2.5 text-sm text-ink focus:border-espresso focus:bg-white focus:ring-espresso"
                >
                <x-input-error class="mt-1.5" :messages="$errors->get('title')" />
            </div>

            <div>
                <label for="category_id" class="block text-xs font-semibold uppercase tracking-wider text-espresso">Danh mục thể loại *</label>
                <select
                    id="category_id"
                    name="category_id"
                    required
                    class="mt-1.5 w-full rounded-xl border border-beige bg-cream/60 px-4 py-2.5 text-sm font-medium text-espresso focus:border-espresso focus:bg-white focus:ring-espresso"
                >
                    <option value="">Chọn thể loại sách</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((int) old('category_id', $isEdit ? $ebook->category_id : 0) === (int) $cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-1.5" :messages="$errors->get('category_id')" />
            </div>

            <div>
                <label for="author_id" class="block text-xs font-semibold uppercase tracking-wider text-espresso">Tác giả</label>
                <select
                    id="author_id"
                    name="author_id"
                    class="mt-1.5 w-full rounded-xl border border-beige bg-cream/60 px-4 py-2.5 text-sm font-medium text-espresso focus:border-espresso focus:bg-white focus:ring-espresso"
                >
                    <option value="">Tự động lấy theo tên tài khoản / Chọn tác giả</option>
                    @foreach($authors as $au)
                        <option value="{{ $au->id }}" @selected((int) old('author_id', $isEdit ? $ebook->author_id : 0) === (int) $au->id)>
                            {{ $au->name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-1.5" :messages="$errors->get('author_id')" />
            </div>

            <div>
                <label for="price" class="block text-xs font-semibold uppercase tracking-wider text-espresso">Giá bán mở khóa (VNĐ — Nhập 0 nếu Miễn phí)</label>
                <input
                    id="price"
                    name="price"
                    type="number"
                    min="0"
                    step="1000"
                    value="{{ old('price', $isEdit ? (int) $ebook->price : 0) }}"
                    class="mt-1.5 w-full rounded-xl border border-beige bg-cream/60 px-4 py-2.5 text-sm text-ink focus:border-espresso focus:bg-white focus:ring-espresso"
                >
                <x-input-error class="mt-1.5" :messages="$errors->get('price')" />
            </div>

            <div>
                <label for="pages" class="block text-xs font-semibold uppercase tracking-wider text-espresso">Số trang sách</label>
                <input
                    id="pages"
                    name="pages"
                    type="number"
                    min="0"
                    value="{{ old('pages', $isEdit ? $ebook->pages : '') }}"
                    placeholder="Tự động đếm từ tệp PDF"
                    class="mt-1.5 w-full rounded-xl border border-beige bg-cream/60 px-4 py-2.5 text-sm text-ink focus:border-espresso focus:bg-white focus:ring-espresso"
                >
                <x-input-error class="mt-1.5" :messages="$errors->get('pages')" />
            </div>
        </div>

        <div>
            <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-espresso">Mô tả nội dung tác phẩm *</label>
            <textarea
                id="description"
                name="description"
                rows="5"
                required
                placeholder="Giới thiệu tóm tắt nội dung và giá trị của cuốn sách..."
                class="mt-1.5 w-full rounded-xl border border-beige bg-cream/60 px-4 py-2.5 text-sm text-ink focus:border-espresso focus:bg-white focus:ring-espresso"
            >{{ old('description', $isEdit ? $ebook->description : '') }}</textarea>
            <x-input-error class="mt-1.5" :messages="$errors->get('description')" />
        </div>

        <div class="grid grid-cols-1 gap-6 border-t border-sand pt-6 sm:grid-cols-2">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-espresso">Ảnh bìa sách</label>
                <div class="mt-2 flex items-start gap-4">
                    <div id="cover-preview-box" class="{{ $isEdit && $ebook->cover_image ? '' : 'hidden' }} h-28 w-20 shrink-0 overflow-hidden rounded-xl border border-beige bg-sand shadow-sm">
                        <img id="cover-preview-img" src="{{ $isEdit ? $ebook->cover_url : '' }}" alt="Cover Preview" class="h-full w-full object-cover">
                    </div>
                    <div class="flex-1">
                        <input
                            type="file"
                            name="cover_image"
                            accept="image/jpeg,image/png,image/jpg,image/webp,image/svg+xml"
                            onchange="if(this.files && this.files[0]){ document.getElementById('cover-preview-img').src = URL.createObjectURL(this.files[0]); document.getElementById('cover-preview-box').classList.remove('hidden'); }"
                            class="block w-full text-xs text-mocha file:mr-3 file:rounded-xl file:border-0 file:bg-sand file:px-4 file:py-2 file:text-xs file:font-semibold file:text-espresso hover:file:bg-beige"
                        >
                        <p id="api-cover-badge" class="mt-1.5 hidden text-[11px] font-semibold text-espresso">Đã nạp tự động ảnh bìa</p>
                        <p class="mt-1 text-[11px] text-taupe">Hỗ trợ JPG, PNG, WEBP.</p>
                    </div>
                </div>
                <x-input-error class="mt-1.5" :messages="$errors->get('cover_image')" />
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-espresso">
                    Tệp sách điện tử (.PDF / .EPUB) {{ $isEdit ? '(Để trống nếu giữ nguyên tệp cũ)' : '*' }}
                </label>
                <div class="mt-2">
                    <input
                        type="file"
                        name="file_path"
                        accept=".pdf,.epub,application/pdf"
                        class="block w-full text-xs text-mocha file:mr-3 file:rounded-xl file:border-0 file:bg-espresso file:px-4 file:py-2 file:text-xs file:font-semibold file:text-cream hover:file:bg-mocha"
                    >
                    @if($isEdit && $ebook->file_path)
                        <p class="mt-1.5 text-xs text-mocha">
                            Tệp hiện tại: <a href="{{ asset('storage/' . $ebook->file_path) }}" target="_blank" class="font-semibold text-espresso underline">Xem PDF hiện có</a>
                        </p>
                    @endif
                    <p class="mt-1 text-[11px] text-taupe">Tệp PDF dùng cho trình đọc sách lật trang trực tuyến.</p>
                </div>
                <x-input-error class="mt-1.5" :messages="$errors->get('file_path')" />
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-sand pt-5">
            <a href="{{ auth()->user()->isAdmin() ? route('admin.ebooks.index') : route('author.ebooks.index') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-beige bg-cream px-5 text-xs font-semibold text-espresso transition hover:bg-sand">
                Hủy bỏ
            </a>
            <button type="submit" class="inline-flex h-10 items-center justify-center rounded-xl bg-espresso px-6 text-xs font-semibold text-cream shadow-sm transition hover:bg-mocha">
                {{ $isEdit ? 'Lưu cập nhật tác phẩm' : (auth()->user()->isAdmin() ? 'Xuất bản lên thư viện' : 'Gửi bản thảo chờ duyệt') }}
            </button>
        </div>
    </form>
</div>

<script>
window.__rvApiResultsCache = [];

window.rvQuickApiSearch = function(keyword) {
    var input = document.getElementById('api-book-query');
    if (input) {
        input.value = keyword;
        window.rvLookupExternalBookApi();
    }
};

window.rvLookupExternalBookApi = function() {
    var input = document.getElementById('api-book-query');
    var statusEl = document.getElementById('api-lookup-status');
    var resultsEl = document.getElementById('api-lookup-results');
    var btn = document.getElementById('api-lookup-btn');

    var q = input ? input.value.trim() : '';
    if (!q) {
        if (statusEl) {
            statusEl.textContent = 'Vui lòng nhập tên sách hoặc mã ISBN trước khi tìm kiếm.';
            statusEl.classList.remove('hidden');
        }
        return;
    }

    if (btn) btn.disabled = true;
    if (statusEl) {
        statusEl.textContent = 'Đang truy vấn dữ liệu sách từ hệ thống OpenLibrary & Google Books...';
        statusEl.classList.remove('hidden');
    }
    if (resultsEl) {
        resultsEl.innerHTML = '';
        resultsEl.classList.add('hidden');
    }

    fetch('/author/ebooks/api-lookup?q=' + encodeURIComponent(q), {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (btn) btn.disabled = false;
        if (!res || !res.success || !res.results || !res.results.length) {
            if (statusEl) statusEl.textContent = (res && res.message) ? res.message : 'Không tìm thấy kết quả phù hợp.';
            return;
        }

        window.__rvApiResultsCache = res.results;
        if (statusEl) {
            statusEl.textContent = 'Tìm thấy ' + res.results.length + ' kết quả phù hợp. Chọn một tác phẩm bên dưới để điền tự động vào biểu mẫu:';
        }

        var html = '';
        res.results.forEach(function(item, idx) {
            var coverImg = item.cover_url
                ? '<img src="' + item.cover_url + '" class="h-16 w-12 shrink-0 rounded-lg object-cover border border-beige bg-sand">'
                : '<div class="flex h-16 w-12 shrink-0 items-center justify-center rounded-lg border border-beige bg-sand text-[10px] font-bold text-espresso">SÁCH</div>';

            html += '<div class="flex flex-col justify-between gap-3 rounded-2xl border border-beige bg-cream/40 p-3.5 sm:flex-row sm:items-center">'
                + '<div class="flex items-start gap-3">'
                + coverImg
                + '<div>'
                + '<div class="flex flex-wrap items-center gap-1.5">'
                + '<span class="rounded bg-sand px-2 py-0.5 text-[10px] font-semibold text-espresso">' + item.source + '</span>'
                + '<span class="text-xs text-taupe">Năm XB: ' + (item.published_year || 'N/A') + ' • ' + item.pages + ' trang</span>'
                + '</div>'
                + '<p class="mt-0.5 font-serif text-base font-bold text-espresso">' + item.title + '</p>'
                + '<p class="text-xs font-medium text-mocha">Tác giả: ' + item.author + '</p>'
                + '</div>'
                + '</div>'
                + '<button type="button" onclick="window.rvApplyApiBook(' + idx + ')" class="inline-flex h-8 shrink-0 items-center justify-center whitespace-nowrap rounded-xl bg-espresso px-3.5 text-xs font-semibold text-cream shadow-sm transition hover:bg-mocha">Chọn sách này</button>'
                + '</div>';
        });

        if (resultsEl) {
            resultsEl.innerHTML = html;
            resultsEl.classList.remove('hidden');
        }
    })
    .catch(function() {
        if (btn) btn.disabled = false;
        if (statusEl) statusEl.textContent = 'Không thể kết nối dịch vụ tra cứu lúc này, vui lòng thử lại.';
    });
};

window.rvApplyApiBook = function(idx) {
    var item = window.__rvApiResultsCache[idx];
    if (!item) return;

    var titleInput = document.getElementById('title');
    var descInput = document.getElementById('description');
    var pagesInput = document.getElementById('pages');
    var catSelect = document.getElementById('category_id');
    var statusEl = document.getElementById('api-lookup-status');

    if (titleInput) titleInput.value = item.title || '';
    if (descInput) descInput.value = item.description || '';
    if (pagesInput) pagesInput.value = item.pages || 250;
    if (catSelect && !catSelect.value && catSelect.options.length > 1) {
        catSelect.selectedIndex = 1;
    }

    if (statusEl) {
        statusEl.textContent = 'Đang đồng bộ thông tin tác giả và ảnh bìa...';
    }

    var csrf = document.querySelector('meta[name="csrf-token"]');
    fetch('/author/ebooks/api-prepare', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : ''
        },
        body: JSON.stringify({
            author: item.author,
            cover_url: item.cover_url
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(prep) {
        if (prep && prep.success) {
            var authorSelect = document.getElementById('author_id');
            if (authorSelect && prep.author_id) {
                var found = false;
                for (var i = 0; i < authorSelect.options.length; i++) {
                    if (String(authorSelect.options[i].value) === String(prep.author_id)) {
                        authorSelect.selectedIndex = i;
                        found = true;
                        break;
                    }
                }
                if (!found) {
                    var opt = document.createElement('option');
                    opt.value = prep.author_id;
                    opt.textContent = prep.author_name;
                    opt.selected = true;
                    authorSelect.appendChild(opt);
                }
            }

            if (prep.pre_uploaded_cover) {
                var preCov = document.getElementById('pre_uploaded_cover');
                if (preCov) preCov.value = prep.pre_uploaded_cover;
            }

            if (prep.cover_preview_url) {
                var previewImg = document.getElementById('cover-preview-img');
                var previewBox = document.getElementById('cover-preview-box');
                var badge = document.getElementById('api-cover-badge');
                if (previewImg) previewImg.src = prep.cover_preview_url;
                if (previewBox) previewBox.classList.remove('hidden');
                if (badge) badge.classList.remove('hidden');
            }

            if (statusEl) {
                statusEl.textContent = 'Đã điền thông tin tác phẩm "' + item.title + '" và nạp ảnh bìa thành công.';
            }
        }
    })
    .catch(function() {});
};
</script>
@endsection
