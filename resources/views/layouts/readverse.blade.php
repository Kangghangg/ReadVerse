<!DOCTYPE html>
<html lang="vi" class="h-full bg-cream">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ReadVerse')</title>
    @php
        $favFile = file_exists(public_path('images/Icontab.png')) ? 'images/Icontab.png' : 'favicon.ico';
        $favVer = file_exists(public_path($favFile)) ? filemtime(public_path($favFile)) : time();
    @endphp
    <link rel="icon" type="image/png" href="{{ asset($favFile) }}?v={{ $favVer }}">
    <link rel="shortcut icon" href="{{ asset($favFile) }}?v={{ $favVer }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v={{ $favVer }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600,700|lora:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="flex min-h-full flex-col font-sans text-ink antialiased">
    <div id="rv-top-loader" style="position: fixed; top: 0; left: 0; height: 3px; width: 0%; background: linear-gradient(90deg, #4B3828, #A98660, #2563eb); z-index: 9999; transition: width 0.2s ease, opacity 0.2s ease; opacity: 0; pointer-events: none;"></div>
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

    <header id="rv-site-header" class="sticky top-0 z-40 w-full border-b border-sand bg-cream/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-8">
                <a href="{{ route('home') }}" data-spa-nav="true" class="font-serif text-2xl font-semibold tracking-wide text-espresso">ReadVerse</a>
                <nav id="rv-desktop-nav" class="hidden items-center gap-6 text-sm text-mocha md:flex">
                    <a href="{{ route('home') }}" data-spa-nav="true" class="transition hover:text-espresso {{ request()->routeIs('home') && !request()->boolean('favorites') ? 'font-medium text-espresso' : '' }}">Trang chủ</a>
                    @auth
                    <a href="{{ route('reader.library') }}" data-spa-nav="true" class="inline-flex items-center gap-1.5 transition hover:text-espresso {{ request()->routeIs('reader.library') ? 'font-semibold text-espresso' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-espresso" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>Tủ sách & Lịch sử</span>
                    </a>
                    @if(auth()->user()->isAuthor() || auth()->user()->isAdmin())
                    <a href="{{ route('author.ebooks.index') }}" data-spa-nav="true" class="transition hover:text-espresso {{ request()->routeIs('author.*') ? 'font-medium text-espresso' : '' }}">Cổng tác giả</a>
                    @endif
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.ebooks.index') }}" data-spa-nav="true" class="inline-flex items-center gap-1.5 rounded-lg bg-espresso/10 px-3 py-1 font-semibold text-espresso transition hover:bg-espresso hover:text-cream {{ request()->routeIs('admin.*') ? 'bg-espresso text-cream' : '' }}">
                        <span>Quản trị Admin</span>
                    </a>
                    @endif
                    @endauth
                </nav>
            </div>

            <div class="hidden items-center gap-4 text-sm md:flex">
                @auth
                <div class="flex items-center gap-2">
                    @if(auth()->user()->isAdmin())
                    <span class="rounded-md bg-espresso px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-cream">Admin</span>
                    @elseif(auth()->user()->isAuthor())
                    <span class="rounded-md bg-mocha px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-cream">Author</span>
                    @endif
                    <span class="font-medium text-mocha">{{ auth()->user()->name }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="rounded-lg border border-beige px-4 py-1.5 font-medium text-espresso transition hover:bg-sand">Đăng xuất</button>
                </form>
                @else
                <a href="{{ route('login') }}" class="text-mocha transition hover:text-espresso">Đăng nhập</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-espresso px-4 py-1.5 font-medium text-cream transition hover:bg-mocha">Đăng ký</a>
                @endauth
            </div>

            <details class="relative md:hidden">
                <summary class="flex cursor-pointer list-none items-center rounded-lg p-2 text-espresso [&::-webkit-details-marker]:hidden">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </summary>
                <div class="absolute right-0 mt-3 w-60 rounded-xl border border-sand bg-cream p-2 text-sm shadow-lg">
                    <a href="{{ route('home') }}" data-spa-nav="true" class="block rounded-lg px-3 py-2.5 text-espresso hover:bg-sand">Trang chủ</a>
                    @auth
                    <a href="{{ route('reader.library') }}" data-spa-nav="true" class="block rounded-lg px-3 py-2.5 font-medium text-espresso hover:bg-sand">Tủ sách & Lịch sử</a>
                    @if(auth()->user()->isAuthor() || auth()->user()->isAdmin())
                    <a href="{{ route('author.ebooks.index') }}" data-spa-nav="true" class="block rounded-lg px-3 py-2.5 text-espresso hover:bg-sand">Cổng tác giả</a>
                    @endif
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.ebooks.index') }}" data-spa-nav="true" class="block rounded-lg px-3 py-2.5 font-semibold text-espresso hover:bg-sand">Quản trị Admin</a>
                    @endif
                    <div class="my-2 border-t border-sand"></div>
                    <p class="px-3 py-1.5 text-xs text-mocha">{{ auth()->user()->name }}</p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full rounded-lg px-3 py-2.5 text-left text-espresso hover:bg-sand">Đăng xuất</button>
                    </form>
                    @else
                    <div class="my-2 border-t border-sand"></div>
                    <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2.5 text-espresso hover:bg-sand">Đăng nhập</a>
                    <a href="{{ route('register') }}" class="mt-1 block rounded-lg bg-espresso px-3 py-2.5 text-center font-medium text-cream hover:bg-mocha">Đăng ký</a>
                    @endauth
                </div>
            </details>
        </div>
    </header>

    <div id="rv-page-shell" class="flex flex-1 flex-col">
        @include('layouts.partials.back-bar')

        @if(session('success'))
        <div data-flash-type="success" data-flash-message="{{ session('success') }}" class="rv-flash-banner mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 shadow-sm">{{ session('success') }}</div>
        </div>
        @endif

        @if(session('error'))
        <div data-flash-type="error" data-flash-message="{{ session('error') }}" class="rv-flash-banner mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900 shadow-sm">{{ session('error') }}</div>
        </div>
        @endif

        <main id="rv-main-view" class="flex-1">
            @yield('content')
        </main>
    </div>

    <footer class="mt-auto border-t border-sand bg-sand/40 py-8">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 text-xs text-mocha sm:flex-row sm:px-6 lg:px-8">
            <span class="font-serif text-lg font-semibold text-espresso">ReadVerse</span>
            <span>&copy; {{ date('Y') }} ReadVerse</span>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>
