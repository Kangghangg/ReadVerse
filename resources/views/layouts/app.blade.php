<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ReadVerse') }}</title>
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
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
