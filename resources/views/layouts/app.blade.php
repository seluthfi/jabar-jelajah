<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta content="web_standard" name="shell-type"/>
    <title>{{ $title ?? 'Jelajah Jabar - Rekomendasi Wisata Jawa Barat' }}</title>

    <!-- Google Fonts: Poppins & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    <script>document.documentElement.classList.add('js')</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background font-body-md text-on-surface antialiased selection:bg-primary-fixed selection:text-on-primary-fixed">
    <x-navbar />

    <main class="w-full pt-20 bg-background min-h-screen">
        @isset($slot)
            {{ $slot }}
        @else
            @yield('content')
        @endisset
    </main>

    <x-footer />

    @stack('scripts')
</body>
</html>
