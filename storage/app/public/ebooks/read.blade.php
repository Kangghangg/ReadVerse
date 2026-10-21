<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đọc sách: {{ \$ebook->title }} - ReadVerse</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Thư viện PDF.js & PageFlip 3D -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.js"></script>

    <style>
        body {
            background-color: #111827;
            overflow-x: hidden;
        }

        .page-flip-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 80px);
        }

        .page {
            background-color: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>

<body class="text-white flex flex-col min-h-screen">

    <!-- HEADER & THANH CÔNG CỤ XEM SÁCH -->
    <header class="bg-gray-800 border-b border-gray-700 px-6 py-3 flex items-center justify-between z-20 shadow-lg">
        <div class="flex items-center space-x-4">
            <a href="{{ route('ebooks.show', \$ebook->slug) }}"
                class="flex items-center text-gray-300 hover:text-white bg-gray-700 hover:bg-gray-600 px-3 py-1.5 rounded-lg text-sm font-medium transition">
                ← Quay lại
            </a>
            <div>
                <h1 class="text-base font-bold text-white truncate max-w-xs md:max-w-md">{{ \$ebook->title }}</h1>
                <p class="text-xs text-gray-400">Tác giả: {{ \$ebook->author->name }}</p>
            </div>
        </div>

        <!-- NÚT ĐIỀU HƯỚNG TRANG -->
        <div class="flex items-center space-x-3">
            <button id="prev-page" class="bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1.5 rounded-lg text-sm font-semibold transition">
                ◄ Trang trước
            </button>
            <span class="text-sm font-medium text-gray-300">
                Trang <span id="current-page" class="text-indigo-400 font-bold">1</span> / <span id="total-pages">--</span>
            </span>
            <button id="next-page" class="bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1.5 rounded-lg text-sm font-semibold transition">
                Trang sau ►
            </button>
        </div>
    </header>

    <!-- TRÌNH HIỂN THỊ LẬT TRANG 3D (FLIPBOOK) -->
    <main class="flex-1 page-flip-container py-6">
        <!-- Thông báo đang tải PDF -->
        <div id="loading-spinner" class="flex flex-col items-center justify-center space-y-3">
            <div class="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-gray-400 text-sm font-medium">Đang chuẩn bị trang sách 3D...</p>
        </div>

        <!-- Khung chứa các trang sách Lật 3D -->
        <div id="flipbook" class="hidden"></div>
    </main>

    <!-- SCRIPT KHỞI TẠO BỘ LẬT TRANG -->
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        const pdfUrl = "{{ asset('storage/' . \$ebook->file_path) }}";
        const flipbookContainer = document.getElementById('flipbook');
        const loadingSpinner = document.getElementById('loading-spinner');
        const currentPageEl = document.getElementById('current-page');
        const totalPagesEl = document.getElementById('total-pages');

        let pageFlip = null;

        // Load file PDF bằng PDF.js
        pdfjsLib.getDocument(pdfUrl).promise.then(async (pdf) => {
            const totalPages = pdf.numPages;
            totalPagesEl.innerText = totalPages;

            // Render toàn bộ trang PDF thành hình ảnh Canvas
            for (let i = 1; i <= totalPages; i++) {
                const page = await pdf.getPage(i);
                const viewport = page.getViewport({
                    scale: 1.5
                });

                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                await page.render({
                    canvasContext: context,
                    viewport: viewport
                }).promise;

                // Thẻ bọc trang sách cho PageFlip
                const pageDiv = document.createElement('div');
                pageDiv.className = 'page';
                pageDiv.appendChild(canvas);
                flipbookContainer.appendChild(pageDiv);
            }

            // Ẩn Spinner, hiện Flipbook
            loadingSpinner.classList.add('hidden');
            flipbookContainer.classList.remove('hidden');

            // Khởi tạo hiệu ứng Lật trang 3D với St.PageFlip
            pageFlip = new St.PageFlip(flipbookContainer, {
                width: 450, // Chiều rộng mỗi trang
                height: 600, // Chiều cao mỗi trang
                size: 'fixed',
                minWidth: 300,
                maxWidth: 600,
                minHeight: 400,
                maxHeight: 800,
                maxShadowOpacity: 0.5, // Độ đậm của bóng đổ lật trang
                showCover: true, // Trang đầu làm bìa sách
                mobileScrollSupport: true
            });

            // Nạp các trang vào bộ lật
            pageFlip.loadFromHTML(document.querySelectorAll('.page'));

            // Cập nhật số trang khi người dùng lật
            pageFlip.on('flip', (e) => {
                currentPageEl.innerText = e.data + 1;
            });

        }).catch((error) => {
            loadingSpinner.innerHTML = `
                <div class="text-center text-red-400">
                    <p class="font-bold text-lg">Không thể mở file PDF</p>
                    <p class="text-sm text-gray-400 mt-1">Vui lòng kiểm tra lại file <code class="bg-gray-800 px-2 py-1">storage/app/public/${'{{ $ebook->file_path }}'}</code></p>
                </div>`;
        });

        // Bắt sự kiện bấm nút Trang trước / Trang sau
        document.getElementById('prev-page').addEventListener('click', () => {
            if (pageFlip) pageFlip.flipPrev();
        });

        document.getElementById('next-page').addEventListener('click', () => {
            if (pageFlip) pageFlip.flipNext();
        });
    </script>
</body>

</html>