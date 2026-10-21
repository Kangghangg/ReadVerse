<!DOCTYPE html>
<html lang="vi" class="h-full bg-cream">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ReadVerse</title>
        @php
        $favFile = file_exists(public_path('images/favicon-64.png'))
            ? public_path('images/favicon-64.png')
            : public_path('images/Icontab.png');
        $favVer = file_exists($favFile) ? filemtime($favFile) : time();
        $favBase64 = file_exists($favFile) ? 'data:image/png;base64,' . base64_encode(file_get_contents($favFile)) : asset('favicon.ico');
    @endphp
    <link rel="icon" type="image/png" sizes="64x64" href="{{ $favBase64 }}">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32.png?v={{ $favVer }}">
    <link rel="icon" type="image/x-icon" href="/favicon.ico?v={{ $favVer }}">
    <link rel="shortcut icon" href="/favicon.ico?v={{ $favVer }}">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v={{ $favVer }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600,700|lora:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="min-h-full font-sans text-ink antialiased">
    @php
        $bgCandidates = ['images/Background.JPEG', 'images/Background.jpg', 'images/Background.jpeg', 'images/Background.png', 'images/banner.jpg'];
        $bgPath = 'images/Background.JPEG';
        foreach ($bgCandidates as $candidate) {
            if (file_exists(public_path($candidate))) {
                $bgPath = $candidate;
                break;
            }
        }
        $bgVersion = file_exists(public_path($bgPath)) ? filemtime(public_path($bgPath)) : time();
    @endphp
    <div class="relative isolate flex min-h-screen items-center justify-center overflow-hidden bg-espresso px-4 py-12">
        <img src="{{ asset($bgPath) }}?v={{ $bgVersion }}" alt="ReadVerse Banner" class="pointer-events-none absolute inset-0 z-0 h-full w-full select-none object-cover object-center">
        <div class="rv-hero-vignette absolute inset-0 z-10"></div>

        <a href="{{ route('home') }}" aria-label="Về trang chính" title="Về trang chính" class="absolute left-4 top-4 z-20 inline-flex h-10 w-10 items-center justify-center rounded-lg border border-cream/40 text-cream transition hover:bg-cream/10 sm:left-8 sm:top-8">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>

        <div class="relative z-20 w-full max-w-md">
            <a href="{{ route('home') }}" class="mb-8 block text-center font-serif text-4xl font-semibold tracking-wide text-cream drop-shadow-[0_4px_16px_rgba(27,21,16,0.85)]">ReadVerse</a>
            <div class="rounded-xl bg-cream/95 p-8 shadow-2xl ring-1 ring-beige/40 backdrop-blur-sm">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>

</html>
