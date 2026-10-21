document.addEventListener('DOMContentLoaded', () => {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
    const pageCache = new Map();
    const CACHE_TTL = 20000;
    let activeNavController = null;
    let pendingConfirmResolver = null;

    function openAnimatedPopover(el) {
        if (!el) return;
        if (el._hideTimer) {
            clearTimeout(el._hideTimer);
            el._hideTimer = null;
        }
        el.classList.remove('hidden');
        el.classList.add('rv-popover-animated', 'rv-popover-closed');
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                el.classList.remove('rv-popover-closed');
                el.classList.add('rv-popover-open');
            });
        });
    }

    function closeAnimatedPopover(el) {
        if (!el || el.classList.contains('hidden')) return;
        el.classList.remove('rv-popover-open');
        el.classList.add('rv-popover-closed');
        if (el._hideTimer) clearTimeout(el._hideTimer);
        el._hideTimer = setTimeout(() => {
            el.classList.add('hidden');
        }, 220);
    }

    function askConfirm({
        title = 'Xác nhận thực hiện',
        message = 'Bạn có chắc chắn muốn thực hiện thao tác này không?',
        confirmText = 'Xác nhận xóa',
        cancelText = 'Hủy bỏ'
    } = {}) {
        const modal = document.getElementById('rv-confirm-modal');
        const dialog = document.getElementById('rv-confirm-dialog');
        const titleEl = document.getElementById('rv-confirm-title');
        const msgEl = document.getElementById('rv-confirm-message');
        const okBtn = document.getElementById('rv-confirm-ok');
        const cancelBtn = document.getElementById('rv-confirm-cancel');

        if (!modal || !dialog || !okBtn || !cancelBtn) {
            return Promise.resolve(window.confirm(message));
        }

        if (pendingConfirmResolver) {
            pendingConfirmResolver(false);
            pendingConfirmResolver = null;
        }

        if (titleEl) titleEl.textContent = title;
        if (msgEl) msgEl.textContent = message;
        okBtn.textContent = confirmText;
        cancelBtn.textContent = cancelText;

        modal.style.transition = 'opacity 240ms cubic-bezier(0.22, 1, 0.36, 1)';
        modal.style.opacity = '0';
        dialog.style.transition = 'transform 340ms cubic-bezier(0.34, 1.56, 0.64, 1), opacity 220ms cubic-bezier(0.22, 1, 0.36, 1)';
        dialog.style.transform = 'scale(0.9) translate3d(0, 12px, 0)';
        dialog.style.opacity = '0';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                modal.style.opacity = '1';
                dialog.style.transform = 'scale(1) translate3d(0, 0, 0)';
                dialog.style.opacity = '1';
            });
        });

        return new Promise((resolve) => {
            pendingConfirmResolver = (result) => {
                modal.style.opacity = '0';
                dialog.style.transform = 'scale(0.93) translate3d(0, 8px, 0)';
                dialog.style.opacity = '0';
                setTimeout(() => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    document.body.style.overflow = '';
                }, 220);
                pendingConfirmResolver = null;
                resolve(result);
            };
        });
    }

    document.addEventListener('click', (event) => {
        if (!pendingConfirmResolver) return;
        const modal = document.getElementById('rv-confirm-modal');
        const okBtn = event.target.closest('#rv-confirm-ok');
        const cancelBtn = event.target.closest('#rv-confirm-cancel');

        if (okBtn) {
            event.preventDefault();
            event.stopPropagation();
            pendingConfirmResolver(true);
        } else if (cancelBtn || event.target === modal) {
            event.preventDefault();
            event.stopPropagation();
            pendingConfirmResolver(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && pendingConfirmResolver) {
            pendingConfirmResolver(false);
        }
    });

    function startTopLoader() {
        const loader = document.getElementById('rv-top-loader');
        if (!loader) return;
        loader.style.transition = 'width 360ms cubic-bezier(0.22, 1, 0.36, 1), opacity 180ms ease';
        loader.style.opacity = '1';
        loader.style.width = '76%';
    }

    function finishTopLoader() {
        const loader = document.getElementById('rv-top-loader');
        if (!loader) return;
        loader.style.width = '100%';
        setTimeout(() => {
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.width = '0%';
            }, 200);
        }, 140);
    }

    function showToast(message, type = 'success') {
        if (!message) return;
        const container = document.getElementById('rv-toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        const isError = type === 'error';
        toast.className = `pointer-events-auto flex items-center gap-2.5 rounded-xl border px-4 py-3 text-xs font-semibold shadow-xl ${
            isError
                ? 'border-espresso/40 bg-sand text-espresso'
                : 'border-beige bg-sand text-espresso'
        }`;
        toast.style.transition = 'transform 380ms cubic-bezier(0.34, 1.56, 0.64, 1), opacity 240ms cubic-bezier(0.22, 1, 0.36, 1), filter 240ms ease';
        toast.style.transform = 'translate3d(0, -14px, 0) scale(0.92)';
        toast.style.opacity = '0';
        toast.style.filter = 'blur(4px)';

        const dot = document.createElement('span');
        dot.className = `h-2 w-2 shrink-0 rounded-full ${isError ? 'bg-mocha' : 'bg-espresso'}`;
        const text = document.createElement('span');
        text.textContent = message;

        toast.appendChild(dot);
        toast.appendChild(text);
        container.appendChild(toast);

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                toast.style.transform = 'translate3d(0, 0, 0) scale(1)';
                toast.style.opacity = '1';
                toast.style.filter = 'blur(0px)';
            });
        });

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translate3d(0, -10px, 0) scale(0.95)';
            toast.style.filter = 'blur(3px)';
            setTimeout(() => toast.remove(), 260);
        }, 3200);
    }

    function getCachedHtml(url) {
        const entry = pageCache.get(url);
        if (!entry) return null;
        if (Date.now() - entry.time > CACHE_TTL) {
            pageCache.delete(url);
            return null;
        }
        return entry.html;
    }

    function setCachedHtml(url, html) {
        if (pageCache.size > 40) {
            const firstKey = pageCache.keys().next().value;
            pageCache.delete(firstKey);
        }
        pageCache.set(url, { html, time: Date.now() });
    }

    function prefetchUrl(url) {
        if (!url || !url.startsWith(window.location.origin)) return;
        if (url.includes('/read/') || url.includes('/logout')) return;
        if (getCachedHtml(url)) return;

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
            .then((res) => (res.ok ? res.text() : null))
            .then((html) => {
                if (html) setCachedHtml(url, html);
            })
            .catch(() => {});
    }

    document.addEventListener('mouseover', (event) => {
        const link = event.target.closest('a[data-ajax-catalog-link="true"], a[data-ajax-admin-tab="true"], a[data-spa-nav="true"], #catalog-pagination a');
        if (link && link.href) {
            prefetchUrl(link.href);
        }
    });

    function applyOptimisticTabHighlight(clickedLink) {
        if (clickedLink.hasAttribute('data-ajax-admin-tab')) {
            const tabContainer = clickedLink.closest('#admin-suite-tabs');
            if (!tabContainer) return;
            tabContainer.querySelectorAll('a[data-ajax-admin-tab="true"]').forEach((tab) => {
                const isTarget = tab === clickedLink;
                tab.classList.toggle('bg-espresso', isTarget);
                tab.classList.toggle('text-cream', isTarget);
                tab.classList.toggle('shadow-sm', isTarget);
                tab.classList.toggle('border', !isTarget);
                tab.classList.toggle('border-beige', !isTarget);
                tab.classList.toggle('bg-sand/40', !isTarget);
                tab.classList.toggle('text-espresso', !isTarget);
            });
            return;
        }

        if (clickedLink.hasAttribute('data-ajax-catalog-link')) {
            const bar = clickedLink.parentElement;
            if (!bar) return;
            const isFavLink = clickedLink.href.includes('favorites=1') && clickedLink.textContent.includes('Đã thích');
            bar.querySelectorAll('a[data-ajax-catalog-link="true"]').forEach((tab) => {
                const tabIsFav = tab.href.includes('favorites=1') && tab.textContent.includes('Đã thích');
                if (tabIsFav) {
                    if (isFavLink) {
                        tab.className = 'inline-flex h-8 items-center gap-1.5 rounded-lg px-4 text-xs font-medium transition bg-rose-600 text-cream shadow-sm';
                    }
                    return;
                }
                const isTarget = tab === clickedLink;
                tab.classList.toggle('bg-espresso', isTarget);
                tab.classList.toggle('text-cream', isTarget);
                tab.classList.toggle('border', !isTarget);
                tab.classList.toggle('border-beige', !isTarget);
                tab.classList.toggle('text-mocha', !isTarget);
                tab.classList.toggle('hover:bg-sand', !isTarget);
            });
        }
    }

    function closeAllCustomSelects(exceptWrapper = null) {
        document.querySelectorAll('.rv-custom-select-wrapper').forEach((w) => {
            if (w !== exceptWrapper) {
                const menu = w.querySelector('.rv-custom-select-menu');
                const chevron = w.querySelector('.rv-custom-select-chevron');
                if (menu) closeAnimatedPopover(menu);
                if (chevron) chevron.style.transform = 'rotate(0deg)';
                setTimeout(() => {
                    if (menu && menu.classList.contains('hidden')) {
                        w.style.zIndex = '';
                    }
                }, 220);
            }
        });
    }

    function initCustomSelects(rootNode = document) {
        const selects = rootNode.querySelectorAll('select:not([data-rv-enhanced="true"])');
        selects.forEach((select) => {
            select.dataset.rvEnhanced = 'true';
            const isCompact = select.dataset.compactSelect === 'true';

            const wrapper = document.createElement('div');
            wrapper.className = isCompact
                ? 'rv-custom-select-wrapper relative inline-block text-left'
                : 'rv-custom-select-wrapper relative block w-full text-left';

            select.parentNode.insertBefore(wrapper, select);
            wrapper.appendChild(select);
            select.classList.add('sr-only');

            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = isCompact
                ? 'rv-custom-select-trigger inline-flex min-w-[112px] items-center justify-between gap-2.5 rounded-xl border border-beige bg-cream px-3 py-1.5 text-xs font-medium text-espresso shadow-sm transition hover:bg-sand/60 focus:border-espresso focus:outline-none focus:ring-1 focus:ring-espresso'
                : 'rv-custom-select-trigger flex w-full items-center justify-between gap-2.5 rounded-xl border border-beige bg-cream px-3.5 py-2.5 text-sm text-espresso transition hover:bg-sand/40 focus:border-espresso focus:outline-none focus:ring-1 focus:ring-espresso';

            const labelSpan = document.createElement('span');
            labelSpan.className = 'truncate';
            const selectedOption = select.options[select.selectedIndex];
            labelSpan.textContent = selectedOption ? selectedOption.textContent.trim() : '';

            const chevron = document.createElement('span');
            chevron.className = 'rv-custom-select-chevron pointer-events-none flex shrink-0 items-center text-mocha';
            chevron.style.transition = 'transform 280ms cubic-bezier(0.34, 1.56, 0.64, 1)';
            chevron.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>';

            trigger.appendChild(labelSpan);
            trigger.appendChild(chevron);
            wrapper.appendChild(trigger);

            const menu = document.createElement('div');
            menu.className = 'rv-custom-select-menu rv-popover-animated rv-popover-closed hidden rounded-xl border border-beige bg-cream p-1.5 shadow-2xl';
            menu.style.position = 'absolute';
            menu.style.top = 'calc(100% + 6px)';
            menu.style.left = '0';
            menu.style.minWidth = '100%';
            menu.style.width = isCompact ? 'max-content' : '100%';
            menu.style.maxHeight = '260px';
            menu.style.overflowY = 'auto';
            menu.style.padding = '6px';
            menu.style.borderRadius = '12px';
            menu.style.zIndex = '999';

            function renderOptions() {
                menu.innerHTML = '';
                const totalOpts = select.options.length;
                Array.from(select.options).forEach((opt, idx) => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    const isSelected = idx === select.selectedIndex;
                    item.className = `flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-left ${isCompact ? 'text-xs' : 'text-sm'} transition ${
                        isSelected
                            ? 'bg-espresso font-semibold text-cream shadow-sm'
                            : 'text-espresso hover:bg-sand/80'
                    }`;
                    item.style.borderRadius = '8px';
                    if (idx < totalOpts - 1) {
                        item.style.marginBottom = '2px';
                    }

                    const textSpan = document.createElement('span');
                    textSpan.className = 'truncate';
                    textSpan.textContent = opt.textContent.trim();
                    item.appendChild(textSpan);

                    if (isSelected) {
                        const checkIcon = document.createElement('span');
                        checkIcon.className = 'shrink-0 text-xs';
                        checkIcon.textContent = '✓';
                        item.appendChild(checkIcon);
                    }

                    item.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        if (select.selectedIndex !== idx) {
                            select.selectedIndex = idx;
                            labelSpan.textContent = opt.textContent.trim();
                            renderOptions();
                            closeAllCustomSelects();

                            if (select.dataset.autoSubmit === 'true' && select.form) {
                                select.form.requestSubmit();
                                return;
                            }

                            select.dispatchEvent(new Event('change', { bubbles: true }));
                        } else {
                            closeAllCustomSelects();
                        }
                    });

                    menu.appendChild(item);
                });
            }

            renderOptions();
            wrapper.appendChild(menu);

            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const isClosed = menu.classList.contains('hidden') || menu.classList.contains('rv-popover-closed');
                closeAllCustomSelects(wrapper);
                if (isClosed) {
                    renderOptions();
                    wrapper.style.zIndex = '999';
                    chevron.style.transform = 'rotate(180deg)';
                    openAnimatedPopover(menu);
                } else {
                    chevron.style.transform = 'rotate(0deg)';
                    closeAnimatedPopover(menu);
                    setTimeout(() => {
                        wrapper.style.zIndex = '';
                    }, 220);
                }
            });
        });
    }

    function initLiveSearchInputs(rootNode = document) {
        const searchInputs = rootNode.querySelectorAll('[data-live-search]:not([data-rv-search-bound="true"])');
        searchInputs.forEach((input) => {
            input.dataset.rvSearchBound = 'true';
            const anchorContainer = input.closest('form') || input.parentElement;
            if (!anchorContainer) return;

            anchorContainer.style.position = 'relative';

            const dropdown = document.createElement('div');
            dropdown.className = 'js-live-search-dropdown rv-popover-animated rv-popover-closed hidden bg-cream border border-beige rounded-xl p-1.5 shadow-2xl text-left';
            dropdown.style.position = 'absolute';
            dropdown.style.top = 'calc(100% + 12px)';
            dropdown.style.left = '0';
            dropdown.style.right = '0';
            dropdown.style.width = '100%';
            dropdown.style.maxHeight = '320px';
            dropdown.style.overflowY = 'auto';
            dropdown.style.padding = '6px';
            dropdown.style.borderRadius = '12px';
            dropdown.style.zIndex = '999';
            anchorContainer.appendChild(dropdown);

            let debounceTimer = null;

            input.addEventListener('input', (e) => {
                const query = e.target.value.trim();
                clearTimeout(debounceTimer);

                if (query.length < 2) {
                    closeAnimatedPopover(dropdown);
                    return;
                }

                debounceTimer = setTimeout(async () => {
                    try {
                        const response = await fetch(`/api/ebooks/search?q=${encodeURIComponent(query)}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) return;
                        const payload = await response.json();
                        const items = payload.data || [];

                        if (items.length === 0) {
                            dropdown.innerHTML = '<div class="px-3.5 py-2.5 text-sm text-mocha">Không tìm thấy sách phù hợp.</div>';
                            openAnimatedPopover(dropdown);
                            return;
                        }

                        dropdown.innerHTML = items.map((book) => `
                            <a href="${book.url}" data-spa-nav="true" style="border-radius: 8px; margin-bottom: 2px;" class="flex items-center gap-3.5 rounded-lg px-3.5 py-2.5 hover:bg-sand/80 transition last:mb-0">
                                <img src="${book.cover_url}" alt="${book.title}" class="w-10 h-14 object-cover rounded-md flex-shrink-0 shadow-sm">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-espresso truncate">${book.title}</p>
                                    <p class="text-xs text-mocha truncate mt-0.5">${book.author} • ${book.category}</p>
                                </div>
                                <span class="text-xs font-semibold text-espresso whitespace-nowrap">${book.price === '0' ? 'Miễn phí' : book.price + ' đ'}</span>
                            </a>
                        `).join('');
                        openAnimatedPopover(dropdown);
                    } catch (err) {
                        console.error(err);
                    }
                }, 160);
            });

            input.addEventListener('focus', () => {
                if (input.value.trim().length >= 2 && dropdown.innerHTML.trim() !== '') {
                    openAnimatedPopover(dropdown);
                }
            });

            document.addEventListener('click', (e) => {
                if (!anchorContainer.contains(e.target)) {
                    closeAnimatedPopover(dropdown);
                }
            });
        });
    }

    function openQrModalAnimated(modal) {
        if (!modal) return;
        const innerCard = modal.firstElementChild;
        modal.style.transition = 'opacity 260ms cubic-bezier(0.22, 1, 0.36, 1)';
        modal.style.opacity = '0';
        if (innerCard) {
            innerCard.style.transition = 'transform 360ms cubic-bezier(0.34, 1.56, 0.64, 1), opacity 240ms cubic-bezier(0.22, 1, 0.36, 1)';
            innerCard.style.transform = 'scale(0.92) translate3d(0, 14px, 0)';
            innerCard.style.opacity = '0';
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                modal.style.opacity = '1';
                if (innerCard) {
                    innerCard.style.transform = 'scale(1) translate3d(0, 0, 0)';
                    innerCard.style.opacity = '1';
                }
            });
        });
    }

    function closeQrModalAnimated(modal) {
        if (!modal || modal.classList.contains('hidden')) return;
        const innerCard = modal.firstElementChild;
        modal.style.opacity = '0';
        if (innerCard) {
            innerCard.style.transform = 'scale(0.94) translate3d(0, 10px, 0)';
            innerCard.style.opacity = '0';
        }
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }, 240);
    }

    function autoOpenCheckoutModalIfNeeded() {
        const modal = document.getElementById('qr-checkout-modal');
        if (modal && modal.dataset.autoOpen === 'true') {
            openQrModalAnimated(modal);
        }
    }

    function hydratePageComponents(rootNode = document) {
        initCustomSelects(rootNode);
        initLiveSearchInputs(rootNode);
        autoOpenCheckoutModalIfNeeded();
    }

    hydratePageComponents();

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.rv-custom-select-wrapper')) {
            closeAllCustomSelects();
        }
    });

    function applyHtmlDocumentUpdate(htmlText, targetUrl, options = {}) {
        const {
            pushState = true,
            preserveScroll = true,
            savedScrollY = window.scrollY,
            onlyCatalog = false
        } = options;

        const parser = new DOMParser();
        const doc = parser.parseFromString(htmlText, 'text/html');

        if (doc.title) {
            document.title = doc.title;
        }

        doc.querySelectorAll('.rv-flash-banner').forEach((banner) => {
            const msg = banner.getAttribute('data-flash-message') || banner.textContent.trim();
            const type = banner.getAttribute('data-flash-type') || 'success';
            if (msg) showToast(msg, type);
            banner.remove();
        });

        const currentContainer = document.getElementById('catalog-container');
        const newContainer = doc.getElementById('catalog-container');

        if (onlyCatalog && currentContainer && newContainer) {
            const activeInput = document.activeElement;
            const isSearchFocused = activeInput && activeInput.getAttribute('name') === 'search' && currentContainer.contains(activeInput);
            const cursorStart = isSearchFocused ? activeInput.selectionStart : null;
            const cursorEnd = isSearchFocused ? activeInput.selectionEnd : null;

            currentContainer.innerHTML = newContainer.innerHTML;
            hydratePageComponents(currentContainer);

            if (isSearchFocused) {
                const newSearchBox = currentContainer.querySelector('input[name="search"]');
                if (newSearchBox) {
                    newSearchBox.focus();
                    if (cursorStart !== null && cursorEnd !== null) {
                        newSearchBox.setSelectionRange(cursorStart, cursorEnd);
                    }
                }
            }
        } else {
            const currentShell = document.getElementById('rv-page-shell') || document.getElementById('rv-main-view');
            const newShell = doc.getElementById('rv-page-shell') || doc.getElementById('rv-main-view');
            if (currentShell && newShell) {
                currentShell.innerHTML = newShell.innerHTML;
                currentShell.classList.remove('rv-view-enter');
                void currentShell.offsetWidth;
                currentShell.classList.add('rv-view-enter');
                hydratePageComponents(currentShell);
            }

            const currentDesktopNav = document.getElementById('rv-desktop-nav');
            const newDesktopNav = doc.getElementById('rv-desktop-nav');
            if (currentDesktopNav && newDesktopNav) {
                currentDesktopNav.innerHTML = newDesktopNav.innerHTML;
            }
        }

        const newHeroForm = doc.getElementById('hero-search-form');
        const currentHeroForm = document.getElementById('hero-search-form');
        if (newHeroForm && currentHeroForm) {
            const searchInput = currentHeroForm.querySelector('#hero-search-input');
            const newSearchInput = newHeroForm.querySelector('#hero-search-input');
            if (searchInput && newSearchInput && document.activeElement !== searchInput) {
                searchInput.value = newSearchInput.value;
            }
            currentHeroForm.querySelectorAll('input[type="hidden"]').forEach((el) => el.remove());
            newHeroForm.querySelectorAll('input[type="hidden"]').forEach((el) => {
                currentHeroForm.insertBefore(el.cloneNode(true), currentHeroForm.firstChild);
            });
        }

        if (pushState && targetUrl) {
            window.history.pushState({ path: targetUrl }, '', targetUrl);
        }

        if (preserveScroll) {
            window.scrollTo({ top: savedScrollY, behavior: 'instant' });
        } else {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    async function navigateSmoothly(targetUrl, options = {}) {
        const {
            pushState = true,
            preserveScroll = true,
            onlyCatalog = false,
            useCache = true
        } = options;

        const savedScrollY = window.scrollY;

        if (useCache) {
            const cachedHtml = getCachedHtml(targetUrl);
            if (cachedHtml) {
                applyHtmlDocumentUpdate(cachedHtml, targetUrl, {
                    pushState,
                    preserveScroll,
                    savedScrollY,
                    onlyCatalog
                });
                fetch(targetUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                    .then((res) => (res.ok ? res.text() : null))
                    .then((freshHtml) => {
                        if (freshHtml) setCachedHtml(targetUrl, freshHtml);
                    })
                    .catch(() => {});
                return;
            }
        }

        if (activeNavController) {
            activeNavController.abort();
        }
        activeNavController = new AbortController();

        startTopLoader();

        try {
            const response = await fetch(targetUrl, {
                signal: activeNavController.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });

            if (!response.ok) {
                throw new Error('Navigation failed');
            }

            const htmlText = await response.text();
            setCachedHtml(targetUrl, htmlText);

            applyHtmlDocumentUpdate(htmlText, response.url || targetUrl, {
                pushState,
                preserveScroll,
                savedScrollY,
                onlyCatalog
            });
        } catch (error) {
            if (error.name !== 'AbortError') {
                window.location.href = targetUrl;
            }
        } finally {
            finishTopLoader();
        }
    }

    document.addEventListener('click', async (event) => {
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        const openQrBtn = event.target.closest('[data-open-qr-modal="true"]');
        if (openQrBtn) {
            event.preventDefault();
            const modal = document.getElementById('qr-checkout-modal');
            openQrModalAnimated(modal);
            return;
        }

        const closeQrBtn = event.target.closest('[data-close-qr-modal="true"]');
        const qrModal = document.getElementById('qr-checkout-modal');
        if (closeQrBtn || (qrModal && event.target === qrModal)) {
            event.preventDefault();
            closeQrModalAnimated(qrModal);
            return;
        }

        const confirmQrBtn = event.target.closest('#confirm-qr-payment-btn');
        if (confirmQrBtn && qrModal) {
            event.preventDefault();
            if (qrModal.dataset.isAuthenticated !== 'true') {
                window.location.href = qrModal.dataset.loginUrl || '/login';
                return;
            }

            const confirmText = document.getElementById('confirm-qr-payment-text');
            const checkoutError = document.getElementById('qr-checkout-error');
            confirmQrBtn.disabled = true;
            if (confirmText) confirmText.textContent = 'Đang xác thực giao dịch...';
            if (checkoutError) checkoutError.classList.add('hidden');

            try {
                const response = await fetch(qrModal.dataset.unlockUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ payment_method: 'vietqr_momo' })
                });

                if (response.status === 401) {
                    window.location.href = qrModal.dataset.loginUrl || '/login';
                    return;
                }

                const result = await response.json();
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Không thể xác nhận thanh toán.');
                }

                pageCache.clear();
                const readUrl = result.read_url || qrModal.dataset.readUrl;
                const actionContainer = document.getElementById('ebook-action-container');
                if (actionContainer) {
                    actionContainer.innerHTML = `
                        <a
                            id="read-now-btn"
                            href="${readUrl}"
                            style="background-color: #2563eb; color: #ffffff;"
                            class="rv-view-enter inline-flex w-full items-center justify-center gap-2 rounded-xl py-3.5 px-5 text-center text-sm font-bold text-white shadow-md transition hover:opacity-95"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>Đọc ngay</span>
                        </a>
                    `;
                }

                const unlockedBadge = document.getElementById('unlocked-badge');
                if (unlockedBadge) {
                    unlockedBadge.classList.remove('hidden');
                    unlockedBadge.classList.add('inline-block', 'rv-view-enter');
                }

                closeQrModalAnimated(qrModal);
                showToast(`${result.message} (${result.order_code || 'RV-PAID'})`, 'success');
            } catch (err) {
                if (checkoutError) {
                    checkoutError.textContent = err.message || 'Có lỗi xảy ra khi xử lý thanh toán.';
                    checkoutError.classList.remove('hidden');
                }
                confirmQrBtn.disabled = false;
                if (confirmText) confirmText.textContent = 'Xác nhận đã thanh toán';
            }
            return;
        }

        const resetButton = event.target.closest('[data-reset-catalog-filter="true"]');
        if (resetButton) {
            event.preventDefault();
            const resetUrl = resetButton.dataset.resetUrl || window.location.pathname;
            const heroInput = document.getElementById('hero-search-input');
            if (heroInput) heroInput.value = '';
            document.querySelectorAll('.js-live-search-dropdown').forEach((el) => closeAnimatedPopover(el));
            await navigateSmoothly(resetUrl, { pushState: true, preserveScroll: true, onlyCatalog: true, useCache: false });
            return;
        }

        const catalogLink = event.target.closest('a[data-ajax-catalog-link="true"], #catalog-pagination a');
        if (catalogLink && catalogLink.href) {
            event.preventDefault();
            applyOptimisticTabHighlight(catalogLink);
            await navigateSmoothly(catalogLink.href, { pushState: true, preserveScroll: true, onlyCatalog: true, useCache: true });
            return;
        }

        const adminTabLink = event.target.closest('a[data-ajax-admin-tab="true"]');
        if (adminTabLink && adminTabLink.href) {
            event.preventDefault();
            applyOptimisticTabHighlight(adminTabLink);
            await navigateSmoothly(adminTabLink.href, { pushState: true, preserveScroll: true, onlyCatalog: false, useCache: true });
            return;
        }

        const spaNavLink = event.target.closest('a[data-spa-nav="true"]');
        if (spaNavLink && spaNavLink.href && !spaNavLink.href.includes('/read/')) {
            event.preventDefault();
            document.querySelectorAll('.js-live-search-dropdown').forEach((el) => closeAnimatedPopover(el));
            await navigateSmoothly(spaNavLink.href, { pushState: true, preserveScroll: false, onlyCatalog: false, useCache: true });
            return;
        }

        const button = event.target.closest('[data-bookmark-id], .js-bookmark-toggle');
        if (!button) return;

        event.preventDefault();
        if (button.dataset.loading === 'true') return;

        const ebookId = button.dataset.bookmarkId || button.dataset.ebookId;
        if (!ebookId) return;

        const wasBookmarked = button.getAttribute('aria-pressed') === 'true';
        if (wasBookmarked) {
            const bookTitle = button.dataset.bookTitle ? `«${button.dataset.bookTitle}»` : 'cuốn sách này';
            const confirmed = await askConfirm({
                title: 'Bỏ khỏi Tủ sách yêu thích',
                message: `Bạn có chắc chắn muốn bỏ ${bookTitle} khỏi danh sách yêu thích không?`,
                confirmText: 'Bỏ yêu thích',
                cancelText: 'Giữ lại'
            });
            if (!confirmed) return;
        }

        button.dataset.loading = 'true';
        const endpoint = button.dataset.endpoint || `/bookmarks/${ebookId}`;

        const optimisticState = !wasBookmarked;
        button.classList.remove('rv-heart-pop');
        void button.offsetWidth;
        button.classList.add('rv-heart-pop');

        button.setAttribute('aria-pressed', optimisticState ? 'true' : 'false');
        button.classList.toggle('border-rose-300', optimisticState);
        button.classList.toggle('bg-rose-50/80', optimisticState);
        button.classList.toggle('text-rose-600', optimisticState);
        button.classList.toggle('border-beige', !optimisticState);
        button.classList.toggle('bg-sand/70', !optimisticState);
        button.classList.toggle('text-mocha', !optimisticState);

        const icon = button.querySelector('svg');
        if (icon) {
            icon.classList.toggle('fill-current', optimisticState);
            icon.classList.toggle('text-rose-600', optimisticState);
            icon.setAttribute('fill', optimisticState ? 'currentColor' : 'none');
        }

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ ebook_id: Number(ebookId) })
            });

            if (response.status === 401) {
                window.location.href = '/login';
                return;
            }

            if (!response.ok) {
                throw new Error('Request failed');
            }

            const result = await response.json();
            if (result.success) {
                pageCache.clear();
                const isBookmarked = Boolean(result.bookmarked);
                const favCountBadge = document.getElementById('fav-tab-count');
                if (favCountBadge && typeof result.favorite_count !== 'undefined') {
                    favCountBadge.textContent = String(result.favorite_count);
                }

                showToast(isBookmarked ? 'Đã thêm vào tủ sách yêu thích' : 'Đã bỏ khỏi tủ sách yêu thích', 'success');

                const currentParams = new URLSearchParams(window.location.search);
                if ((currentParams.get('favorites') === '1' || window.location.pathname === '/my-library') && !isBookmarked) {
                    const cardArticle = button.closest('article');
                    if (cardArticle) {
                        cardArticle.style.transition = 'transform 260ms cubic-bezier(0.22, 1, 0.36, 1), opacity 240ms ease';
                        cardArticle.style.transform = 'scale(0.88) translate3d(0, 10px, 0)';
                        cardArticle.style.opacity = '0';
                        setTimeout(() => cardArticle.remove(), 250);
                    } else {
                        await navigateSmoothly(window.location.href, { pushState: false, preserveScroll: true, onlyCatalog: true, useCache: false });
                    }
                }
            }
        } catch (error) {
            console.error(error);
        } finally {
            button.dataset.loading = 'false';
        }
    });

    document.addEventListener('submit', async (event) => {
        const reviewDeleteForm = event.target.closest('form[data-ajax-review-delete="true"]');
        if (reviewDeleteForm) {
            event.preventDefault();
            const confirmed = await askConfirm({
                title: reviewDeleteForm.getAttribute('data-confirm-title') || 'Xóa bình luận đánh giá',
                message: reviewDeleteForm.getAttribute('data-confirm') || 'Bạn có chắc chắn muốn xóa đánh giá này không?',
                confirmText: reviewDeleteForm.getAttribute('data-confirm-btn') || 'Xóa đánh giá',
                cancelText: 'Hủy bỏ'
            });
            if (!confirmed) return;

            try {
                const response = await fetch(reviewDeleteForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new FormData(reviewDeleteForm)
                });

                const result = await response.json();
                if (response.ok && result.success) {
                    pageCache.clear();
                    const reviewCard = reviewDeleteForm.closest('[id^="review-item-"]');
                    if (reviewCard) {
                        reviewCard.style.transition = 'transform 260ms cubic-bezier(0.22, 1, 0.36, 1), opacity 240ms ease';
                        reviewCard.style.transform = 'scale(0.95) translate3d(0, -6px, 0)';
                        reviewCard.style.opacity = '0';
                        setTimeout(() => reviewCard.remove(), 250);
                    }

                    const avgDisplay = document.getElementById('avg-rating-display');
                    const countDisplay = document.getElementById('reviews-count-display');
                    if (avgDisplay && result.stats) {
                        avgDisplay.textContent = result.stats.avg_rating;
                        countDisplay.textContent = result.stats.reviews_count;
                    }
                    showToast('Đã xóa đánh giá thành công.', 'success');
                }
            } catch (err) {
                console.error(err);
            }
            return;
        }

        const reviewForm = event.target.closest('#ajax-review-form');
        if (reviewForm) {
            event.preventDefault();
            const statusEl = document.getElementById('ajax-review-status');
            const submitBtn = document.getElementById('ajax-review-submit');
            const reviewsList = document.getElementById('reviews-list');
            const avgDisplay = document.getElementById('avg-rating-display');
            const countDisplay = document.getElementById('reviews-count-display');

            if (submitBtn) submitBtn.disabled = true;
            if (statusEl) {
                statusEl.textContent = 'Đang gửi...';
                statusEl.className = 'text-xs font-medium text-mocha';
            }

            try {
                const response = await fetch(reviewForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new FormData(reviewForm)
                });

                const result = await response.json();
                if (!response.ok || !result.success) {
                    const errMsg = result.message || 'Không thể gửi đánh giá.';
                    if (statusEl) {
                        statusEl.textContent = errMsg;
                        statusEl.className = 'text-xs font-medium text-rose-600';
                    }
                    showToast(errMsg, 'error');
                    return;
                }

                pageCache.clear();
                const rev = result.review;
                if (avgDisplay && result.stats) {
                    avgDisplay.textContent = result.stats.avg_rating;
                    countDisplay.textContent = result.stats.reviews_count;
                }

                const placeholder = document.getElementById('no-reviews-placeholder');
                if (placeholder) placeholder.remove();

                if (reviewsList) {
                    const existingCard = reviewsList.querySelector(`[data-user-id="${rev.user_id}"]`);
                    if (existingCard) existingCard.remove();

                    const card = document.createElement('div');
                    card.id = `review-item-${rev.id}`;
                    card.dataset.userId = String(rev.user_id);
                    card.className = 'rv-view-enter rounded-2xl border border-beige bg-white p-4 shadow-sm transition';
                    card.innerHTML = `
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-sm text-espresso"></span>
                                <span class="rounded-md bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-800">${rev.rating} ★</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-taupe">${rev.updated_at}</span>
                                <form action="${rev.delete_url}" method="POST" data-ajax-review-delete="true" data-confirm-title="Xóa bình luận đánh giá" data-confirm="Bạn có chắc chắn muốn xóa đánh giá này của mình không?" data-confirm-btn="Xóa đánh giá" class="inline">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">Xóa</button>
                                </form>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-ink rv-comment-text"></p>
                    `;
                    card.querySelector('span.font-semibold').textContent = rev.user_name;
                    card.querySelector('.rv-comment-text').textContent = rev.comment;
                    reviewsList.prepend(card);
                }

                if (statusEl) {
                    statusEl.textContent = result.message;
                    statusEl.className = 'text-xs font-medium text-espresso';
                }
                showToast(result.message, 'success');
            } catch (err) {
                showToast('Lỗi kết nối khi gửi đánh giá.', 'error');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
            return;
        }

        const mutationForm = event.target.closest('form[data-ajax-mutation-form="true"]');
        if (mutationForm) {
            event.preventDefault();
            const confirmMsg = mutationForm.getAttribute('data-confirm');
            if (confirmMsg) {
                const confirmed = await askConfirm({
                    title: mutationForm.getAttribute('data-confirm-title') || 'Xác nhận thực hiện',
                    message: confirmMsg,
                    confirmText: mutationForm.getAttribute('data-confirm-btn') || 'Xác nhận xóa',
                    cancelText: 'Hủy bỏ'
                });
                if (!confirmed) return;
            }

            const savedScrollY = window.scrollY;
            startTopLoader();

            try {
                const response = await fetch(mutationForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    },
                    body: new FormData(mutationForm)
                });

                if (!response.ok) {
                    throw new Error('Mutation request failed');
                }

                pageCache.clear();
                const htmlText = await response.text();
                applyHtmlDocumentUpdate(htmlText, response.url || window.location.href, {
                    pushState: false,
                    preserveScroll: true,
                    savedScrollY,
                    onlyCatalog: false
                });
            } catch (err) {
                console.error(err);
                mutationForm.submit();
            } finally {
                finishTopLoader();
            }
            return;
        }

        const catalogForm = event.target.closest('form[data-ajax-catalog-form="true"]');
        if (!catalogForm) return;

        event.preventDefault();
        const formData = new FormData(catalogForm);
        const params = new URLSearchParams();

        for (const [key, value] of formData.entries()) {
            if (String(value).trim() !== '') {
                params.append(key, String(value).trim());
            }
        }

        const baseUrl = catalogForm.getAttribute('action') || window.location.pathname;
        const queryString = params.toString();
        const targetUrl = queryString ? `${baseUrl}?${queryString}` : baseUrl;

        document.querySelectorAll('.js-live-search-dropdown').forEach((el) => closeAnimatedPopover(el));
        await navigateSmoothly(targetUrl, { pushState: true, preserveScroll: true, onlyCatalog: true, useCache: true });
    });

    document.addEventListener('change', async (event) => {
        const select = event.target.closest('form[data-ajax-catalog-form="true"] select');
        if (!select) return;

        const form = select.closest('form[data-ajax-catalog-form="true"]');
        if (form) {
            form.requestSubmit();
        }
    });

    let adminSearchTimer = null;
    document.addEventListener('input', (event) => {
        const searchInput = event.target.closest('input[data-ajax-search-input="true"]');
        if (!searchInput) return;

        clearTimeout(adminSearchTimer);
        adminSearchTimer = setTimeout(() => {
            const form = searchInput.closest('form[data-ajax-catalog-form="true"]');
            if (form) {
                form.requestSubmit();
            }
        }, 200);
    });

    window.addEventListener('popstate', () => {
        navigateSmoothly(window.location.href, { pushState: false, preserveScroll: true, onlyCatalog: false, useCache: true });
    });

    let activeChunkUploads = 0;

    function arrayBufferToBase64(buffer) {
        let binary = '';
        const bytes = new Uint8Array(buffer);
        const len = bytes.byteLength;
        for (let i = 0; i < len; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return window.btoa(binary);
    }

    async function uploadFileInChunks(file, targetType) {
        const form = document.getElementById('author-ebook-form');
        if (!form || !file) return;

        const endpoint = form.dataset.chunkEndpoint || '/author/ebooks/upload-chunk';
        const submitBtn = document.getElementById('author-submit-btn');
        const ext = (file.name.split('.').pop() || 'pdf').toLowerCase();
        const uploadId = Date.now().toString(36) + '_' + Math.random().toString(36).substring(2, 8);
        const CHUNK_SIZE = 750 * 1024;
        const totalChunks = Math.max(1, Math.ceil(file.size / CHUNK_SIZE));

        const isPdf = targetType === 'ebook';
        const progressWrapper = document.getElementById('pdf-progress-wrapper');
        const statusEl = document.getElementById(isPdf ? 'pdf-upload-status' : 'cover-upload-status');
        const percentEl = document.getElementById('pdf-upload-percent');
        const barEl = document.getElementById('pdf-upload-bar');
        const hiddenInput = document.getElementById(isPdf ? 'pre-uploaded-file-input' : 'pre-uploaded-cover-input');
        const fileInput = document.getElementById(isPdf ? 'pdf-upload-input' : 'cover-upload-input');

        activeChunkUploads++;
        if (submitBtn) submitBtn.disabled = true;

        if (isPdf && progressWrapper) {
            progressWrapper.classList.remove('hidden');
        } else if (statusEl) {
            statusEl.classList.remove('hidden');
        }

        const mbTotal = (file.size / 1048576).toFixed(2);

        try {
            let finalRelPath = '';
            for (let idx = 0; idx < totalChunks; idx++) {
                const start = idx * CHUNK_SIZE;
                const end = Math.min(start + CHUNK_SIZE, file.size);
                const slice = file.slice(start, end);
                const buffer = await slice.arrayBuffer();
                const base64Data = arrayBufferToBase64(buffer);

                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        upload_id: uploadId,
                        chunk_index: idx,
                        total_chunks: totalChunks,
                        ext: ext,
                        target_type: targetType,
                        chunk_base64: base64Data
                    })
                });

                if (!response.ok) {
                    throw new Error('Chunk upload failed');
                }

                const result = await response.json();
                if (!result.success) {
                    throw new Error(result.message || 'Upload error');
                }

                finalRelPath = result.relative_path;
                const pct = Math.round(((idx + 1) / totalChunks) * 100);
                if (isPdf) {
                    if (statusEl) statusEl.textContent = `Đang tải tệp lên máy chủ: ${file.name} (${mbTotal} MB)...`;
                    if (percentEl) percentEl.textContent = `${pct}%`;
                    if (barEl) barEl.style.width = `${pct}%`;
                } else if (statusEl) {
                    statusEl.textContent = `Đang tải ảnh bìa: ${pct}%...`;
                }
            }

            if (hiddenInput) hiddenInput.value = finalRelPath;
            if (fileInput) fileInput.value = '';

            if (isPdf) {
                if (statusEl) {
                    statusEl.textContent = `✓ Đã tải xong tệp: ${file.name} (${mbTotal} MB) — Sẵn sàng gửi duyệt!`;
                    statusEl.className = 'text-xs font-bold text-espresso';
                }
                if (barEl) barEl.className = 'h-full bg-espresso transition-all duration-150';
            } else if (statusEl) {
                statusEl.textContent = `✓ Đã tải xong ảnh bìa: ${file.name}`;
            }
        } catch (err) {
            console.error(err);
            if (statusEl) {
                statusEl.textContent = 'Không thể phân mảnh tệp, sẽ gửi trực tiếp khi bấm nút.';
                statusEl.className = 'text-xs font-semibold text-amber-700';
            }
        } finally {
            activeChunkUploads = Math.max(0, activeChunkUploads - 1);
            if (submitBtn && activeChunkUploads === 0) {
                submitBtn.disabled = false;
            }
        }
    }

    document.addEventListener('change', (event) => {
        const chunkInput = event.target.closest('input[data-chunk-file]');
        if (!chunkInput || !chunkInput.files || !chunkInput.files[0]) return;
        const targetType = chunkInput.getAttribute('data-chunk-file');
        uploadFileInChunks(chunkInput.files[0], targetType);
    });
});


;(function () {
    if (window.__rvDropdownStackFixApplied) return;
    window.__rvDropdownStackFixApplied = true;

    function syncDropdownAncestors() {
        document.querySelectorAll('.rv-dropdown-row-active, .rv-dropdown-cell-active, .rv-dropdown-parent-active').forEach(function (el) {
            el.classList.remove('rv-dropdown-row-active', 'rv-dropdown-cell-active', 'rv-dropdown-parent-active');
        });
        document.querySelectorAll('[data-rv-temp-overflow]').forEach(function (el) {
            el.style.overflow = el.getAttribute('data-rv-temp-overflow') || '';
            el.removeAttribute('data-rv-temp-overflow');
        });

        document.querySelectorAll('.rv-popover-open').forEach(function (menu) {
            menu.style.zIndex = '99999';
            menu.style.backgroundColor = '#FFFDF9';

            var wrapper = menu.parentElement;
            if (wrapper) {
                wrapper.classList.add('rv-dropdown-parent-active');
            }
            var form = menu.closest('form');
            if (form) {
                form.classList.add('rv-dropdown-parent-active');
            }
            var td = menu.closest('td');
            if (td) {
                td.classList.add('rv-dropdown-cell-active');
            }
            var tr = menu.closest('tr');
            if (tr) {
                tr.classList.add('rv-dropdown-row-active');
            }

            var anc = menu.parentElement;
            while (anc && anc !== document.body) {
                var cs = window.getComputedStyle(anc);
                if (cs.overflow === 'hidden' || cs.overflowY === 'hidden' || cs.overflowX === 'auto' || cs.overflowX === 'hidden') {
                    if (!anc.hasAttribute('data-rv-temp-overflow')) {
                        anc.setAttribute('data-rv-temp-overflow', anc.style.overflow || '');
                    }
                    anc.style.overflow = 'visible';
                }
                anc = anc.parentElement;
            }
        });
    }

    var obs = new MutationObserver(function () {
        syncDropdownAncestors();
    });

    function startObserver() {
        if (document.body) {
            obs.observe(document.body, {
                subtree: true,
                attributes: true,
                attributeFilter: ['class']
            });
            syncDropdownAncestors();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startObserver);
    } else {
        startObserver();
    }
    document.addEventListener('click', function () {
        setTimeout(syncDropdownAncestors, 10);
    }, true);
})();
