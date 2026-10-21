@extends('layouts.readverse')

@section('title', 'Đang đọc: ' . $ebook->title)

@push('styles')
<style>
    .reader-shell {
        min-height: calc(100vh - 9rem);
    }

    #flipbook {
        width: min(100%, 1120px);
        height: min(74vh, 780px);
        margin: 0 auto;
    }

    .page {
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #fff;
        box-shadow: inset 0 0 0 1px rgb(216 197 170);
    }

    .page canvas {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: #fff;
    }

    .stf__wrapper {
        margin-inline: auto;
    }
</style>
@endpush

@section('content')
<div class="reader-shell bg-slate-100">
    <div id="reader-toolbar" class="sticky z-30 border-b border-slate-200 bg-cream/95 backdrop-blur" style="top: 65px;">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-3 sm:px-6 lg:px-8 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <div class="mb-1 flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                        {{ $ebook->category?->name }}
                    </span>
                    <span class="text-xs font-medium text-slate-500">{{ $ebook->author?->name }}</span>
                    @if(!($canReadFull ?? true))
                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800">
                        Đọc thử 3 trang đầu
                    </span>
                    @endif
                </div>
                <h1 class="truncate text-lg font-bold text-slate-950 sm:text-xl">{{ $ebook->title }}</h1>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('ebooks.show', $ebook->slug) }}" class="rounded-lg border border-slate-300 bg-cream px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    Về chi tiết
                </a>
                @if($canReadFull ?? true)
                <a href="{{ asset('storage/' . $ebook->file_path) }}" download class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Tải PDF
                </a>
                @endif
                @auth
                <button id="bookmark-btn" type="button" class="rounded-lg border border-slate-300 bg-cream px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    Lưu dấu trang
                </button>
                @endauth
                <div class="rounded-full bg-slate-900 px-3 py-2 text-xs font-semibold text-white">
                    Trang <span id="current-page">1</span> / <span id="total-pages">0</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        @if(!($canReadFull ?? true))
        <div class="mb-4 flex flex-col gap-3 rounded-2xl border border-amber-300 bg-amber-50/90 p-4 text-sm text-amber-950 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="font-bold">Tác phẩm có phí ({{ number_format($ebook->price) }} đ) — Đang ở chế độ Đọc thử 3 trang đầu</p>
                <p class="text-xs text-amber-800 mt-0.5">Hãy mở khóa tác phẩm để đọc trọn vẹn toàn bộ số trang và tải tệp PDF.</p>
            </div>
            <div class="shrink-0">
                @auth
                <form action="{{ route('ebooks.unlock', $ebook->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-xl bg-espresso px-4 py-2 text-xs font-bold text-cream shadow hover:bg-mocha">
                        Mở khóa ngay ({{ number_format($ebook->price) }} đ)
                    </button>
                </form>
                @else
                <a href="{{ route('login') }}" class="inline-block rounded-xl bg-espresso px-4 py-2 text-xs font-bold text-cream shadow hover:bg-mocha">
                    Đăng nhập để mở khóa
                </a>
                @endauth
            </div>
        </div>
        @endif

        <div id="reader-status" class="mb-4 rounded-lg border border-slate-200 bg-cream px-4 py-3 text-sm font-medium text-slate-600">
            Đang tải PDF...
        </div>

        <div class="relative overflow-hidden rounded bg-slate-300 px-12 py-6 shadow-inner sm:px-20">
            <button id="prev-page" type="button" aria-label="Trang trước" title="Trang trước" class="absolute left-2 top-1/2 z-10 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-xl border border-beige bg-cream text-espresso shadow-md transition hover:bg-espresso hover:text-cream disabled:pointer-events-none disabled:opacity-30 sm:left-6 sm:h-12 sm:w-12">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <div
                id="flipbook"
                aria-label="Trình đọc sách"
                data-pdf-url="{{ asset('storage/' . $ebook->file_path) }}"
                data-initial-page="{{ $initialPage ?? ($history->last_page ?? 1) }}"
                data-max-pages="{{ $maxAllowedPages ?? 0 }}"
                data-ebook-id="{{ $ebook->id }}"
                data-can-track-progress="{{ auth()->check() ? 'true' : 'false' }}"></div>

            <button id="next-page" type="button" aria-label="Trang sau" title="Trang sau" class="absolute right-2 top-1/2 z-10 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-xl border border-beige bg-cream text-espresso shadow-md transition hover:bg-espresso hover:text-cream disabled:pointer-events-none disabled:opacity-30 sm:right-6 sm:h-12 sm:w-12">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://unpkg.com/page-flip@2.0.7/dist/js/page-flip.browser.js"></script>
<script>
    const flipbook = document.getElementById('flipbook');
    const pdfUrl = flipbook.dataset.pdfUrl;
    const initialPage = Math.max(Number.parseInt(flipbook.dataset.initialPage, 10) || 1, 1);
    const maxAllowedPages = Math.max(Number.parseInt(flipbook.dataset.maxPages, 10) || 0, 0);
    const ebookId = flipbook.dataset.ebookId;
    const canTrackProgress = flipbook.dataset.canTrackProgress === 'true';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const RENDER_SCALE = 1.5;
    const PRELOAD_RADIUS = 3;
    const KEEP_RADIUS = 8;

    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const statusBox = document.getElementById('reader-status');
    const currentPageEl = document.getElementById('current-page');
    const totalPagesEl = document.getElementById('total-pages');
    const prevButton = document.getElementById('prev-page');
    const nextButton = document.getElementById('next-page');
    const toolbar = document.getElementById('reader-toolbar');
    const siteHeader = document.querySelector('body > header');

    const slots = [];
    let pdfDoc = null;
    let pageFlip = null;
    let totalPages = 0;
    let realPdfTotalPages = 0;
    let saveTimer = null;

    prevButton.disabled = true;
    nextButton.disabled = true;

    function alignToolbar() {
        if (siteHeader && toolbar) {
            toolbar.style.top = siteHeader.offsetHeight + 'px';
        }
    }

    alignToolbar();
    window.addEventListener('resize', alignToolbar);
    window.addEventListener('load', alignToolbar);
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(alignToolbar);
    }

    function setStatus(message, isError = false) {
        statusBox.textContent = message;
        statusBox.classList.toggle('hidden', !message);
        statusBox.classList.toggle('border-red-200', isError);
        statusBox.classList.toggle('bg-red-50', isError);
        statusBox.classList.toggle('text-red-700', isError);
    }

    function updatePageState(pageNumber) {
        const visible = pageFlip && pageFlip.getOrientation() === 'landscape' ? 2 : 1;
        currentPageEl.textContent = pageNumber;
        prevButton.disabled = pageNumber <= 1;
        nextButton.disabled = (pageNumber - 1) + visible >= totalPages;

        if (maxAllowedPages > 0 && pageNumber >= totalPages) {
            setStatus(`Bạn đã đọc hết ${totalPages} trang đọc thử. Vui lòng mở khóa sách để đọc tiếp toàn bộ ${realPdfTotalPages} trang.`, false);
        } else {
            setStatus('');
        }

        queueProgressSave(pageNumber);
    }

    function queueProgressSave(pageNumber) {
        if (!canTrackProgress || !realPdfTotalPages) {
            return;
        }

        clearTimeout(saveTimer);
        saveTimer = setTimeout(() => {
            fetch(`/progress/${ebookId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    last_page: pageNumber,
                    progress_percentage: ((pageNumber / realPdfTotalPages) * 100).toFixed(2)
                })
            }).catch(() => {});
        }, 450);
    }

    function createSlots(total) {
        const elements = [];

        for (let index = 0; index < total; index++) {
            const element = document.createElement('div');
            const canvas = document.createElement('canvas');

            element.className = 'page';
            canvas.width = 2;
            canvas.height = 3;
            element.appendChild(canvas);

            slots.push({
                canvas,
                task: null
            });
            elements.push(element);
        }

        return elements;
    }

    function renderSlot(index) {
        const slot = slots[index];

        if (!slot) {
            return Promise.resolve();
        }

        if (slot.task) {
            return slot.task;
        }

        const task = pdfDoc.getPage(index + 1).then((page) => {
            const viewport = page.getViewport({
                scale: RENDER_SCALE
            });

            slot.canvas.width = viewport.width;
            slot.canvas.height = viewport.height;

            return page.render({
                canvasContext: slot.canvas.getContext('2d'),
                viewport: viewport
            }).promise;
        }).catch(() => {
            if (slot.task === task) {
                slot.task = null;
            }
        });

        slot.task = task;

        return task;
    }

    function releaseSlot(slot) {
        const task = slot.task;

        slot.task = null;
        task.then(() => {
            if (!slot.task) {
                slot.canvas.width = 2;
                slot.canvas.height = 3;
            }
        });
    }

    function syncWindow(center) {
        const from = Math.max(center - PRELOAD_RADIUS, 0);
        const to = Math.min(center + PRELOAD_RADIUS + 1, totalPages - 1);

        for (let index = from; index <= to; index++) {
            renderSlot(index);
        }

        slots.forEach((slot, index) => {
            if (slot.task && Math.abs(index - center) > KEEP_RADIUS) {
                releaseSlot(slot);
            }
        });
    }

    async function bootFlipbook() {
        const startIndex = Math.min(initialPage, totalPages) - 1;
        const elements = createSlots(totalPages);

        await Promise.all([startIndex - 1, startIndex, startIndex + 1, startIndex + 2].map(renderSlot));

        flipbook.replaceChildren(...elements);

        pageFlip = new St.PageFlip(flipbook, {
            width: 560,
            height: 780,
            size: 'stretch',
            minWidth: 300,
            maxWidth: 560,
            minHeight: 420,
            maxHeight: 780,
            maxShadowOpacity: 0.35,
            showCover: false,
            mobileScrollSupport: false,
            useMouseEvents: true,
            flippingTime: 850,
            drawShadow: true
        });

        pageFlip.loadFromHTML(elements);
        pageFlip.on('flip', (event) => {
            updatePageState(event.data + 1);
            syncWindow(event.data);
        });
        pageFlip.turnToPage(startIndex);
        updatePageState(startIndex + 1);
        syncWindow(startIndex);
    }

    prevButton.addEventListener('click', () => pageFlip?.flipPrev());
    nextButton.addEventListener('click', () => pageFlip?.flipNext());

    document.addEventListener('keydown', (event) => {
        if (event.target.closest && event.target.closest('input, textarea')) {
            return;
        }

        if (event.key === 'ArrowRight') {
            pageFlip?.flipNext();
        } else if (event.key === 'ArrowLeft') {
            pageFlip?.flipPrev();
        }
    });

    pdfjsLib.getDocument({
            url: pdfUrl
        }).promise
        .then(async (pdf) => {
            pdfDoc = pdf;
            realPdfTotalPages = pdf.numPages;
            totalPages = maxAllowedPages > 0 ? Math.min(pdf.numPages, maxAllowedPages) : pdf.numPages;
            totalPagesEl.textContent = maxAllowedPages > 0 ? `${totalPages} (Đọc thử / ${realPdfTotalPages})` : totalPages;
            await bootFlipbook();
        })
        .catch(() => {
            setStatus('Không thể tải tệp PDF của cuốn sách này. Vui lòng thử lại sau.', true);
            prevButton.disabled = true;
            nextButton.disabled = true;
        });

    const bookmarkBtn = document.getElementById('bookmark-btn');
    if (bookmarkBtn) {
        bookmarkBtn.addEventListener('click', function() {
            fetch(`/bookmarks/${ebookId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    bookmarkBtn.classList.toggle('border-indigo-300', data.bookmarked);
                    bookmarkBtn.classList.toggle('bg-indigo-50', data.bookmarked);
                    bookmarkBtn.classList.toggle('text-indigo-700', data.bookmarked);
                    bookmarkBtn.textContent = data.bookmarked ? 'Đã lưu dấu trang' : 'Lưu dấu trang';
                })
                .catch(() => {});
        });
    }
</script>
@endpush
