<!DOCTYPE html>
<html lang="vi" class="bg-cream">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ReadVerse - Thư viện Sách Điện Tử')</title>

    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('images/favicon-64.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/Icontab.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,600&family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="flex min-h-screen flex-col bg-cream font-sans text-ink antialiased selection:bg-beige selection:text-ink">
    <div id="rv-top-loader" style="position: fixed; top: 0; left: 0; height: 3px; width: 0%; background: #4B3828; z-index: 9999; transition: width 0.2s ease, opacity 0.2s ease; opacity: 0; pointer-events: none;"></div>
    <div id="rv-toast-container" style="position: fixed; top: 76px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; max-width: 380px; pointer-events: none;"></div>

    <div
        id="rv-confirm-modal"
        class="fixed inset-0 hidden items-center justify-center p-4"
        style="z-index: 9998; background-color: rgba(27, 21, 16, 0.58); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);"
        role="dialog"
        aria-modal="true"
        aria-labelledby="rv-confirm-title"
    >
        <div
            id="rv-confirm-dialog"
            class="w-full max-w-md rounded-3xl border border-beige bg-cream p-6 shadow-2xl transition-all duration-150 sm:p-7"
            style="transform: scale(0.95); opacity: 0;"
        >
            <div>
                <h3 id="rv-confirm-title" class="font-serif text-2xl font-bold text-espresso">Xác nhận thực hiện</h3>
                <p id="rv-confirm-message" class="mt-2 text-sm leading-relaxed text-mocha">Bạn có chắc chắn muốn thực hiện thao tác này không?</p>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3 border-t border-sand pt-4">
                <button
                    type="button"
                    id="rv-confirm-cancel"
                    class="rounded-xl border border-beige bg-sand/60 px-5 py-2.5 text-xs font-semibold text-espresso transition hover:bg-sand focus:outline-none"
                >
                    Hủy bỏ
                </button>
                <button
                    type="button"
                    id="rv-confirm-ok"
                    class="rounded-xl bg-rose-600 px-5 py-2.5 text-xs font-bold text-white shadow-md transition hover:bg-rose-500 focus:outline-none"
                >
                    Xác nhận xóa
                </button>
            </div>
        </div>
    </div>

    <header id="rv-site-header" class="sticky top-0 z-40 border-b border-sand bg-cream/95 backdrop-blur">
        <div id="rv-desktop-nav" class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3.5 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center gap-2 sm:gap-5">
                <a href="{{ route('home') }}" data-spa-nav="true" class="font-serif text-2xl font-bold tracking-tight text-espresso">
                    ReadVerse
                </a>

                <nav class="flex flex-wrap items-center gap-1.5 text-xs font-medium text-mocha sm:gap-2">
                    <a
                        href="{{ route('home') }}"
                        data-spa-nav="true"
                        class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg px-3 transition {{ request()->routeIs('home') ? 'bg-sand font-semibold text-espresso' : 'text-mocha hover:bg-sand/60 hover:text-espresso' }}"
                    >
                        Trang chủ
                    </a>

                    @auth
                        <a
                            href="{{ route('library.index') }}"
                            data-spa-nav="true"
                            class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg px-3 transition {{ request()->routeIs('library.*') || request()->routeIs('reader.library') ? 'bg-sand font-semibold text-espresso' : 'text-mocha hover:bg-sand/60 hover:text-espresso' }}"
                        >
                            Tủ sách & Lịch sử
                        </a>

                        @if(auth()->user()->isAuthor() || auth()->user()->isAdmin())
                            <a
                                href="{{ route('author.ebooks.index') }}"
                                data-spa-nav="true"
                                class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg px-3 transition {{ request()->routeIs('author.*') ? 'bg-sand font-semibold text-espresso' : 'text-mocha hover:bg-sand/60 hover:text-espresso' }}"
                            >
                                Cổng tác giả
                            </a>
                        @endif

                        @if(auth()->user()->isAdmin())
                            <a
                                href="{{ route('admin.ebooks.pending') }}"
                                data-spa-nav="true"
                                class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg px-3 transition {{ request()->routeIs('admin.*') ? 'bg-sand font-semibold text-espresso' : 'text-mocha hover:bg-sand/60 hover:text-espresso' }}"
                            >
                                Quản trị Admin
                            </a>
                        @endif
                    @endauth
                </nav>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2">
                @auth
                    @if(!auth()->user()->isAdmin())
                        @php
                            $navCartCount = \Illuminate\Support\Facades\Schema::hasTable('cart_items')
                                ? \App\Models\CartItem::where('user_id', auth()->id())->count()
                                : 0;
                        @endphp
                        <a
                            href="{{ route('cart.index') }}"
                            data-spa-nav="true"
                            class="inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg border border-beige bg-white px-3 text-xs font-semibold text-espresso transition hover:bg-sand {{ request()->routeIs('cart.*') ? 'bg-sand' : '' }}"
                        >
                            <span>Giỏ hàng</span>
                            <span id="nav-cart-count" class="inline-flex h-4 min-w-[16px] items-center justify-center rounded-full bg-espresso px-1 text-[10px] font-bold text-cream {{ $navCartCount > 0 ? '' : 'hidden' }}">{{ $navCartCount }}</span>
                        </a>

                        <a
                            href="{{ route('profile.edit') }}"
                            data-spa-nav="true"
                            class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg border border-beige bg-white px-3 text-xs font-semibold text-espresso transition hover:bg-sand {{ request()->routeIs('profile.*') ? 'bg-sand' : '' }}"
                        >
                            <span>Hồ sơ</span>
                        </a>
                    @endif

                    <span class="inline-flex h-7 items-center justify-center whitespace-nowrap rounded-md bg-espresso px-2.5 text-[10px] font-bold uppercase tracking-wider text-cream">
                        {{ auth()->user()->role }}
                    </span>

                    <span class="hidden text-xs font-semibold text-espresso sm:inline">
                        {{ auth()->user()->name }}
                    </span>

                    <form method="POST" action="{{ route('logout') }}" class="inline-flex">
                        @csrf
                        <button type="submit" class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg border border-beige bg-white px-3 text-xs font-semibold text-espresso transition hover:bg-sand">
                            Đăng xuất
                        </button>
                    </form>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg border border-beige bg-white px-3 text-xs font-semibold text-espresso transition hover:bg-sand"
                    >
                        Đăng nhập
                    </a>
                    <a
                        href="{{ route('register') }}"
                        class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg bg-espresso px-3.5 text-xs font-semibold text-cream shadow-sm transition hover:bg-mocha"
                    >
                        Đăng ký
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <div id="rv-page-shell" class="flex flex-1 flex-col">
        @include('layouts.partials.back-bar')

        @if(session('success'))
            <div data-flash-type="success" data-flash-message="{{ session('success') }}" class="rv-flash-banner mx-auto w-full max-w-7xl px-4 pt-5 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-beige bg-sand/70 px-5 py-3.5 text-xs font-semibold text-espresso shadow-sm">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div data-flash-type="error" data-flash-message="{{ session('error') }}" class="rv-flash-banner mx-auto w-full max-w-7xl px-4 pt-5 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-mocha/40 bg-white px-5 py-3.5 text-xs font-semibold text-espresso shadow-sm">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mx-auto w-full max-w-7xl px-4 pt-5 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-mocha/40 bg-white px-5 py-3.5 text-xs text-espresso shadow-sm">
                    <p class="font-semibold">Vui lòng kiểm tra lại thông tin:</p>
                    <ul class="mt-1 list-disc space-y-0.5 pl-4 text-mocha">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <main id="rv-main-view" class="flex-1">
            @yield('content')
        </main>
    </div>

    <footer class="mt-16 border-t border-sand bg-cream py-8 text-center text-xs text-mocha">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="font-serif text-base font-semibold text-espresso">ReadVerse — Nền tảng Thư viện & Đọc Sách Điện Tử Trực Tuyến</p>
            <p class="mt-1 text-taupe">Đồ án học phần Lập trình Mã nguồn mở • Xây dựng trên Laravel Framework</p>
        </div>
    </footer>

    @auth
        @if(auth()->user()->isAuthor() || auth()->user()->isAdmin())
            <script src="{{ asset('js/readverse-book-lookup.js') }}?v={{ filemtime(public_path('js/readverse-book-lookup.js')) }}" defer></script>
        @endif
    @endauth

    @stack('scripts')
</body>
</html>
