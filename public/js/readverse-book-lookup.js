/*
 * Tra cuu sach qua Google Books / OpenLibrary tai trang /author/ebooks/create.
 * File nay duoc nap o layout (khong nam trong noi dung trang) va dung event delegation,
 * nen van hoat dong khi trang duoc mo bang dieu huong SPA (innerHTML khong chay lai <script>).
 */
(function () {
    if (window.__rvBookLookupReady) return;
    window.__rvBookLookupReady = true;

    var results = [];

    function byId(id) {
        return document.getElementById(id);
    }

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function safeImageUrl(url) {
        return /^https?:\/\//i.test(url || '') ? url : '';
    }

    function setStatus(message, isError) {
        var statusEl = byId('api-lookup-status');
        if (!statusEl) return;
        statusEl.textContent = message;
        statusEl.classList.remove('hidden');
        statusEl.classList.toggle('text-rose-600', !!isError);
        statusEl.classList.toggle('text-espresso', !isError);
    }

    function setResultButtonsDisabled(disabled) {
        document.querySelectorAll('#api-lookup-results button').forEach(function (button) {
            button.disabled = disabled;
        });
    }

    function readJson(response) {
        return response.json().catch(function () {
            return null;
        });
    }

    function renderResults(items) {
        var resultsEl = byId('api-lookup-results');
        if (!resultsEl) return;

        resultsEl.innerHTML = items.map(function (item, idx) {
            var cover = safeImageUrl(item.cover_url);
            var coverHtml = cover
                ? '<img src="' + escapeHtml(cover) + '" alt="" loading="lazy" class="h-16 w-12 shrink-0 rounded-lg object-cover border border-beige bg-sand">'
                : '<div class="flex h-16 w-12 shrink-0 items-center justify-center rounded-lg border border-beige bg-sand text-[10px] font-bold text-espresso">SÁCH</div>';
            var pagesText = item.pages ? item.pages + ' trang' : 'chưa rõ số trang';
            var authorText = item.author ? escapeHtml(item.author) : 'chưa rõ';

            return '<div class="flex flex-col justify-between gap-3 rounded-2xl border border-beige bg-cream/40 p-3.5 sm:flex-row sm:items-center">'
                + '<div class="flex items-start gap-3">'
                + coverHtml
                + '<div>'
                + '<div class="flex flex-wrap items-center gap-1.5">'
                + '<span class="rounded bg-sand px-2 py-0.5 text-[10px] font-semibold text-espresso">' + escapeHtml(item.source) + '</span>'
                + '<span class="text-xs text-taupe">Năm XB: ' + escapeHtml(item.published_year || 'N/A') + ' • ' + pagesText + '</span>'
                + '</div>'
                + '<p class="mt-0.5 font-serif text-base font-bold text-espresso">' + escapeHtml(item.title) + '</p>'
                + '<p class="text-xs font-medium text-mocha">Tác giả: ' + authorText + '</p>'
                + '</div>'
                + '</div>'
                + '<button type="button" data-api-apply="' + idx + '" class="inline-flex h-8 shrink-0 items-center justify-center whitespace-nowrap rounded-xl bg-espresso px-3.5 text-xs font-semibold text-cream shadow-sm transition hover:bg-mocha">Chọn sách này</button>'
                + '</div>';
        }).join('');

        resultsEl.classList.remove('hidden');
    }

    function lookup() {
        var input = byId('api-book-query');
        var resultsEl = byId('api-lookup-results');
        var button = byId('api-lookup-btn');
        var query = input ? input.value.trim() : '';

        if (!query) {
            setStatus('Vui lòng nhập tên sách hoặc mã ISBN trước khi tìm kiếm.', true);
            return;
        }

        if (button) button.disabled = true;
        setStatus('Đang truy vấn dữ liệu sách từ hệ thống OpenLibrary & Google Books...', false);
        if (resultsEl) {
            resultsEl.innerHTML = '';
            resultsEl.classList.add('hidden');
        }

        fetch('/author/ebooks/api-lookup?q=' + encodeURIComponent(query), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(readJson)
            .then(function (body) {
                if (!body || !body.success || !body.results || !body.results.length) {
                    setStatus((body && body.message) ? body.message : 'Không tìm thấy kết quả phù hợp.', true);
                    return;
                }

                results = body.results;
                setStatus(
                    body.offline
                        ? body.message
                        : 'Tìm thấy ' + results.length + ' kết quả phù hợp. Chọn một tác phẩm bên dưới để điền tự động vào biểu mẫu:',
                    !!body.offline
                );
                renderResults(results);
            })
            .catch(function () {
                setStatus('Không thể kết nối dịch vụ tra cứu lúc này, vui lòng thử lại.', true);
            })
            .then(function () {
                if (button) button.disabled = false;
            });
    }

    function selectAuthor(authorId, authorName) {
        var authorSelect = byId('author_id');
        if (!authorSelect || !authorId) return;

        var found = false;
        for (var i = 0; i < authorSelect.options.length; i++) {
            if (String(authorSelect.options[i].value) === String(authorId)) {
                authorSelect.selectedIndex = i;
                found = true;
                break;
            }
        }

        if (!found) {
            var option = document.createElement('option');
            option.value = authorId;
            option.textContent = authorName;
            option.selected = true;
            authorSelect.appendChild(option);
        }

        // Ô chọn tùy biến của readverse-ajax.js không tự cập nhật nhãn khi giá trị đổi bằng script.
        var wrapper = authorSelect.closest('.rv-custom-select-wrapper');
        var label = wrapper ? wrapper.querySelector('.rv-custom-select-trigger span.truncate') : null;
        if (label) {
            label.textContent = authorSelect.options[authorSelect.selectedIndex].textContent.trim();
        }
    }

    function applyBook(idx) {
        var item = results[idx];
        if (!item) return;

        var titleInput = byId('title');
        var descInput = byId('description');
        var pagesInput = byId('pages');
        var preCover = byId('pre_uploaded_cover');
        var previewBox = byId('cover-preview-box');
        var badge = byId('api-cover-badge');

        if (titleInput) titleInput.value = item.title || '';
        if (descInput) descInput.value = item.description || '';
        if (pagesInput && item.pages) pagesInput.value = item.pages;
        if (preCover) preCover.value = '';
        if (badge) badge.classList.add('hidden');

        setStatus('Đang đồng bộ thông tin tác giả và ảnh bìa...', false);
        setResultButtonsDisabled(true);

        fetch('/author/ebooks/api-prepare', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken()
            },
            body: JSON.stringify({ author: item.author || '', cover_url: item.cover_url || '' })
        })
            .then(readJson)
            .then(function (prep) {
                if (!prep || !prep.success) {
                    setStatus('Đã điền thông tin tác phẩm "' + item.title + '", nhưng chưa đồng bộ được tác giả và ảnh bìa. Bạn có thể chọn thủ công.', true);
                    return;
                }

                selectAuthor(prep.author_id, prep.author_name);

                if (prep.pre_uploaded_cover) {
                    if (preCover) preCover.value = prep.pre_uploaded_cover;
                    var previewImg = byId('cover-preview-img');
                    if (previewImg && prep.cover_preview_url) previewImg.src = prep.cover_preview_url;
                    if (previewBox) previewBox.classList.remove('hidden');
                    if (badge) badge.classList.remove('hidden');
                    setStatus('Đã điền thông tin tác phẩm "' + item.title + '" và nạp ảnh bìa thành công.', false);
                } else {
                    setStatus('Đã điền thông tin tác phẩm "' + item.title + '". ' + (prep.cover_error || 'Sách chưa có ảnh bìa, hãy tải ảnh bìa thủ công.'), !!prep.cover_error);
                }
            })
            .catch(function () {
                setStatus('Đã điền thông tin tác phẩm "' + item.title + '", nhưng không kết nối được để đồng bộ tác giả và ảnh bìa.', true);
            })
            .then(function () {
                setResultButtonsDisabled(false);
            });
    }

    document.addEventListener('click', function (event) {
        var searchBtn = event.target.closest('[data-api-lookup]');
        if (searchBtn) {
            event.preventDefault();
            lookup();
            return;
        }

        var quickBtn = event.target.closest('[data-api-quick]');
        if (quickBtn) {
            event.preventDefault();
            var input = byId('api-book-query');
            if (input) {
                input.value = quickBtn.getAttribute('data-api-quick');
                lookup();
            }
            return;
        }

        var applyBtn = event.target.closest('[data-api-apply]');
        if (applyBtn) {
            event.preventDefault();
            applyBook(parseInt(applyBtn.getAttribute('data-api-apply'), 10));
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && event.target && event.target.id === 'api-book-query') {
            event.preventDefault();
            lookup();
        }
    });
})();
