@extends('layouts.app')

@section('content')
@php
    $fotoUrl = (!empty($destinasi->foto) && file_exists(public_path('images/destinasi/' . $destinasi->foto)))
        ? asset('images/destinasi/' . $destinasi->foto)
        : asset('images/placeholder.jpg');

    $keramaianLabels = [
        1 => 'Sangat Sepi',
        2 => 'Sepi',
        3 => 'Sedang',
        4 => 'Ramai',
        5 => 'Sangat Ramai',
    ];
    $keramaianLabel = $keramaianLabels[$destinasi->keramaian] ?? 'Sedang';

    $fasilitasLabels = [
        1 => 'Sangat Sederhana',
        2 => 'Sederhana',
        3 => 'Cukup',
        4 => 'Lengkap',
        5 => 'Sangat Lengkap',
    ];
    $fasilitasLabel = $fasilitasLabels[$destinasi->fasilitas] ?? 'Cukup';

    $iconMap = [
        'Pegunungan' => 'landscape',
        'Air & Danau' => 'water_drop',
        'Pantai' => 'beach_access',
        'Taman & Keluarga' => 'park',
        'Budaya & Sejarah' => 'museum',
    ];
    $iconJenis = $iconMap[$destinasi->jenis] ?? 'landscape';
@endphp

<div class="flex flex-col w-full">
    <!-- Content Container -->
    <div class="max-w-[1280px] w-full mx-auto px-6 lg:px-12 py-8">
        <!-- 1. Breadcrumb & Navigation -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6">
            <nav aria-label="Breadcrumb" class="flex items-center gap-2 font-label-md text-label-md text-on-surface-variant flex-wrap">
                <a class="hover:text-primary transition-colors flex items-center gap-1" data-path="beranda" href="{{ url('/') }}">
                    <span class="material-symbols-outlined text-[16px]">home</span>
                    <span>Beranda</span>
                </a>
                <span class="material-symbols-outlined text-[14px] text-outline-variant">chevron_right</span>
                <a class="hover:text-primary transition-colors" data-path="katalog" href="{{ url('/katalog') }}">Katalog Wisata</a>
                <span class="material-symbols-outlined text-[14px] text-outline-variant">chevron_right</span>
                <span class="text-primary font-semibold truncate max-w-[200px] sm:max-w-none">{{ $destinasi->nama }}</span>
            </nav>
            <a class="inline-flex items-center gap-2 self-start sm:self-auto px-4 py-2 rounded-full bg-surface-container hover:bg-surface-container-high text-primary font-label-md text-label-md transition-all duration-200 group shadow-sm" data-path="katalog" href="{{ url('/katalog') }}">
                <span class="material-symbols-outlined text-[18px] transition-transform duration-200 group-hover:-translate-x-1">arrow_back</span>
                <span>Kembali ke Katalog Wisata</span>
            </a>
        </div>

        <!-- 2. Hero Visual Showcase -->
        <div class="w-full relative rounded-3xl overflow-hidden shadow-xl aspect-[16/9] md:aspect-[21/9] bg-surface-container group mb-10">
            <img alt="{{ $destinasi->nama }}" class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.02]" src="{{ $fotoUrl }}">
            <!-- Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-on-surface/85 via-on-surface/25 to-transparent"></div>
            <!-- Top Badges & Actions -->
            <div class="absolute top-5 inset-x-5 flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-surface/90 backdrop-blur-md text-primary font-label-sm text-label-sm shadow-sm">
                    <span class="material-symbols-outlined text-[15px]">{{ $iconJenis }}</span>
                    {{ $destinasi->jenis }}
                </span>
                <div class="flex items-center gap-2">
                    <button aria-label="Simpan destinasi favorit" class="w-10 h-10 rounded-full bg-surface/90 backdrop-blur-md text-on-surface hover:text-error flex items-center justify-center transition-all shadow-md active:scale-95" type="button">
                        <span class="material-symbols-outlined text-[20px]">favorite_border</span>
                    </button>
                </div>
            </div>
            <!-- Bottom Details -->
            <div class="absolute bottom-6 left-6 right-6 flex items-end justify-between">
                <div class="text-surface">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface/20 backdrop-blur-md text-surface font-label-sm text-label-sm mb-2">
                        <span class="material-symbols-outlined text-[14px]">location_on</span>
                        {{ $destinasi->wilayah }}
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-surface drop-shadow-md leading-tight">{{ $destinasi->nama }}</h1>
                </div>
                <span class="hidden sm:inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-surface-container-lowest/90 backdrop-blur-md text-primary font-label-sm text-label-sm shadow">
                    <span class="material-symbols-outlined text-[16px]">photo_camera</span>
                    Dokumentasi Resmi
                </span>
            </div>
        </div>

        <!-- 3. Destination Header & Stats Summary -->
        <div class="bg-surface-container-lowest rounded-3xl p-6 lg:p-8 shadow-sm mb-10">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-surface-container/60">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="px-3 py-1 rounded-full bg-surface-container text-primary font-label-md text-label-md">Destinasi Wisata Jawa Barat</span>
                        <span class="px-3 py-1 rounded-full bg-secondary-container/20 text-on-secondary-container font-label-md text-label-md">{{ $destinasi->jenis }}</span>
                    </div>
                    <h2 class="font-headline-lg text-headline-lg text-primary tracking-tight">{{ $destinasi->nama }}</h2>
                    <p class="font-title text-title text-on-surface-variant flex items-center gap-1.5 mt-1">
                        <span class="material-symbols-outlined text-[20px] text-secondary">location_on</span>
                        {{ $destinasi->wilayah }}
                    </p>
                </div>
                <!-- Rating Score Pill (Tanpa ulasan rekaan) -->
                <div class="flex items-center gap-4 bg-surface-container-low px-5 py-3 rounded-2xl self-start lg:self-auto">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-secondary-container text-on-secondary-container font-headline-sm text-headline-sm font-bold shadow-sm">
                        {{ number_format($destinasi->rating, 1) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-1 text-secondary">
                            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star_half</span>
                        </div>
                        <p class="font-body-sm text-body-sm text-on-surface-variant font-medium mt-0.5">Rating Kepuasan Pengunjung</p>
                    </div>
                </div>
            </div>

            <!-- Quick Highlights Bar -->
            <div class="pt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-surface-container-low">
                    <div class="w-10 h-10 rounded-xl bg-surface-container flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-[22px]">{{ $iconJenis }}</span>
                    </div>
                    <div>
                        <span class="font-label-sm text-label-sm text-on-surface-variant block">Kategori</span>
                        <span class="font-label-lg text-label-lg text-on-surface font-semibold">{{ $destinasi->jenis }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-surface-container-low">
                    <div class="w-10 h-10 rounded-xl bg-surface-container flex items-center justify-center text-secondary">
                        <span class="material-symbols-outlined text-[22px]">thermostat</span>
                    </div>
                    <div>
                        <span class="font-label-sm text-label-sm text-on-surface-variant block">Suhu Udara</span>
                        <span class="font-label-lg text-label-lg text-on-surface font-semibold">{{ $destinasi->suhu }}°C</span>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-surface-container-low">
                    <div class="w-10 h-10 rounded-xl bg-surface-container flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-[22px]">payments</span>
                    </div>
                    <div>
                        <span class="font-label-sm text-label-sm text-on-surface-variant block">Tiket Masuk</span>
                        <span class="font-label-lg text-label-lg text-on-surface font-semibold">{{ $destinasi->biaya > 0 ? 'Rp ' . number_format($destinasi->biaya, 0, ',', '.') : 'Gratis' }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-surface-container-low">
                    <div class="w-10 h-10 rounded-xl bg-surface-container flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-[22px]">groups</span>
                    </div>
                    <div>
                        <span class="font-label-sm text-label-sm text-on-surface-variant block">Keramaian</span>
                        <span class="font-label-lg text-label-lg text-on-surface font-semibold">{{ $keramaianLabel }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. MAIN GRID: Left Content & Right Spec Box (Grid 3:2, items-stretch, mt-auto pada Daya Tarik Utama) -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-stretch mb-10">
            <!-- Kartu Kiri: Pesona Keindahan Alam (3 cols) -->
            <div class="lg:col-span-3 bg-surface-container-lowest rounded-3xl p-8 border border-surface-container-high/60 shadow-sm flex flex-col justify-between h-full">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-surface-container/60 mb-6">
                        <div class="flex items-center gap-3">
                            <span class="w-2.5 h-6 rounded-full bg-primary"></span>
                            <h2 class="font-headline-sm text-headline-sm text-on-surface tracking-tight">Pesona Keindahan Alam</h2>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-container-low text-primary font-label-sm text-label-sm border border-primary/10">
                            <span class="material-symbols-outlined text-[14px]">eco</span>{{ $destinasi->jenis }}
                        </span>
                    </div>
                    <div class="space-y-4 text-on-surface-variant font-body-md text-body-md leading-relaxed mb-8">
                        <p>{{ $destinasi->deskripsi ?? 'Destinasi wisata yang menyuguhkan keindahan panorama alam serta suasana sejuk khas bumi Parahyangan Jawa Barat.' }}</p>
                    </div>
                </div>

                <!-- Blok Daya Tarik Utama didorong ke dasar kartu kiri dengan mt-auto -->
                <div class="mt-auto pt-6 border-t border-surface-container/60">
                    <h3 class="font-title text-title text-on-surface mb-4 font-semibold">Daya Tarik Utama Destinasi</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl bg-surface-container-low border border-surface-container-high/40 flex flex-col justify-between h-full">
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[18px]">thermostat</span>
                                </div>
                                <h4 class="font-label-lg text-label-lg font-bold text-on-surface">Kondisi Udara</h4>
                            </div>
                            <p class="text-on-surface-variant text-body-sm leading-snug">Suhu rata-rata kawasan sekitar {{ $destinasi->suhu }}°C yang sejuk dan menyegarkan tubuh.</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-surface-container-low border border-surface-container-high/40 flex flex-col justify-between h-full">
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[18px]">groups</span>
                                </div>
                                <h4 class="font-label-lg text-label-lg font-bold text-on-surface">Atmosfer Kawasan</h4>
                            </div>
                            <p class="text-on-surface-variant text-body-sm leading-snug">Tingkat keramaian berskala {{ $destinasi->keramaian }} dari 5 ({{ strtolower($keramaianLabel) }}).</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-surface-container-low border border-surface-container-high/40 flex flex-col justify-between h-full">
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[18px]">deck</span>
                                </div>
                                <h4 class="font-label-lg text-label-lg font-bold text-on-surface">Fasilitas Wisata</h4>
                            </div>
                            <p class="text-on-surface-variant text-body-sm leading-snug">Kelengkapan fasilitas bertaraf {{ strtolower($fasilitasLabel) }} (skor {{ $destinasi->fasilitas }}/5).</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-surface-container-low border border-surface-container-high/40 flex flex-col justify-between h-full">
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[18px]">pin_drop</span>
                                </div>
                                <h4 class="font-label-lg text-label-lg font-bold text-on-surface">Letak Geografis</h4>
                            </div>
                            <p class="text-on-surface-variant text-body-sm leading-snug">Koordinat titik: {{ $destinasi->lat }}, {{ $destinasi->lon }} di {{ $destinasi->wilayah }}.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Kanan: Informasi & Tarif (2 cols) -->
            <div class="lg:col-span-2 bg-surface-container-lowest rounded-3xl p-8 border border-surface-container-high/60 shadow-sm flex flex-col justify-between h-full space-y-5">
                <div class="space-y-5">
                    <div class="flex items-center justify-between pb-4 border-b border-surface-container/60">
                        <div>
                            <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-0.5">RINGKASAN WISATA</p>
                            <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold tracking-tight">Informasi &amp; Tarif</h3>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary font-label-sm text-label-sm font-semibold">
                            <span class="material-symbols-outlined text-[14px]">verified</span>Terverifikasi
                        </span>
                    </div>

                    <!-- Harga Tiket Masuk (HTM) -->
                    <div class="p-4 rounded-2xl bg-surface-container-low border border-surface-container-high/40 space-y-2">
                        <p class="font-label-sm text-label-sm font-bold text-outline tracking-wider uppercase mb-1">HARGA TIKET MASUK (HTM)</p>
                        <div class="flex items-center justify-between text-body-sm">
                            <span class="flex items-center gap-2 text-on-surface-variant">
                                <span class="material-symbols-outlined text-[16px] text-primary">person</span>
                                Tiket Masuk / Orang
                            </span>
                            <span class="font-title text-title font-bold text-primary">
                                {{ $destinasi->biaya > 0 ? 'Rp ' . number_format($destinasi->biaya, 0, ',', '.') : 'Gratis' }}
                            </span>
                        </div>
                    </div>

                    <!-- Suhu & Keramaian Grid -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3.5 rounded-2xl bg-surface-container-low border border-surface-container-high/30">
                            <div class="flex items-center gap-1.5 text-secondary mb-1">
                                <span class="material-symbols-outlined text-[16px]">thermostat</span>
                                <span class="font-label-sm text-label-sm text-outline">Suhu Udara</span>
                            </div>
                            <p class="font-title text-title font-bold text-on-surface">{{ $destinasi->suhu }}°C</p>
                            <p class="text-[11px] text-on-surface-variant">Iklim Kawasan</p>
                        </div>
                        <div class="p-3.5 rounded-2xl bg-surface-container-low border border-surface-container-high/30">
                            <div class="flex items-center gap-1.5 text-primary mb-1">
                                <span class="material-symbols-outlined text-[16px]">groups</span>
                                <span class="font-label-sm text-label-sm text-outline">Keramaian</span>
                            </div>
                            <p class="font-title text-title font-bold text-on-surface">{{ $keramaianLabel }}</p>
                            <p class="text-[11px] text-on-surface-variant">Level {{ $destinasi->keramaian }} dari 5</p>
                        </div>
                    </div>

                    <!-- Kelengkapan Fasilitas -->
                    <div class="p-3.5 rounded-2xl bg-surface-container-low border border-surface-container-high/30 space-y-1">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5 text-primary">
                                <span class="material-symbols-outlined text-[16px]">deck</span>
                                <span class="font-label-sm text-label-sm text-outline uppercase font-semibold">Fasilitas Kawasan</span>
                            </div>
                            <span class="font-label-md text-label-md text-primary font-bold">Skor {{ $destinasi->fasilitas }}/5</span>
                        </div>
                        <p class="font-title text-title font-bold text-on-surface">{{ $fasilitasLabel }}</p>
                    </div>

                    <!-- Sumber Data & Koordinat -->
                    <div class="p-3.5 rounded-2xl bg-surface-container-low border border-surface-container-high/30 space-y-1 text-body-sm">
                        <div class="flex items-center justify-between text-on-surface-variant text-xs">
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">pin_drop</span> Koordinat</span>
                            <span class="font-mono text-on-surface">{{ $destinasi->lat }}, {{ $destinasi->lon }}</span>
                        </div>
                        @if($destinasi->sumber)
                        <div class="flex items-center justify-between text-on-surface-variant text-xs pt-1 border-t border-surface-container-high/40">
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">database</span> Sumber Data</span>
                            <span class="text-on-surface truncate max-w-[180px]">{{ $destinasi->sumber }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Action Button Petunjuk Rute Google Maps -->
                <div class="pt-4 mt-auto">
                    <a class="w-full flex items-center justify-center gap-2 py-3.5 px-4 rounded-2xl bg-primary hover:bg-surface-tint text-on-primary font-label-lg text-label-lg transition-all duration-200 shadow-md font-bold" href="https://www.google.com/maps/search/?api=1&query={{ $destinasi->lat }},{{ $destinasi->lon }}" target="_blank" rel="noopener noreferrer">
                        <span class="material-symbols-outlined text-[18px]">explore</span>
                        Petunjuk Rute Google Maps
                    </a>
                </div>
            </div>
        </div>

        <!-- 5. Navigasi Bawah: Kembali ke Katalog Wisata -->
        <div class="mt-16 pt-8 pb-4 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-surface-container-highest">
            <div class="text-center sm:text-left">
                <h4 class="font-title text-title text-on-surface">Masih ingin menjelajahi wisata Jawa Barat lainnya?</h4>
                <p class="font-body-sm text-body-sm text-on-surface-variant">Temukan puluhan destinasi terkurasi lainnya di katalog wisata Tanah Pasundan.</p>
            </div>
            <a class="inline-flex items-center gap-2.5 px-6 py-3 rounded-full bg-secondary-container hover:bg-secondary text-on-secondary-container hover:text-on-secondary font-label-lg text-label-lg font-semibold shadow-sm transition-all duration-200 active:scale-95" data-path="katalog" href="{{ url('/katalog') }}">
                <span class="material-symbols-outlined text-[18px]">grid_view</span>
                <span>Kembali ke Katalog Wisata</span>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Favorite toggle button
    const bookmarkBtn = document.querySelector('button[aria-label="Simpan destinasi favorit"]');
    if (bookmarkBtn) {
        bookmarkBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const icon = bookmarkBtn.querySelector('.material-symbols-outlined');
            if (icon) {
                const isFavorite = icon.innerText === 'favorite';
                if (isFavorite) {
                    icon.innerText = 'favorite_border';
                    bookmarkBtn.classList.remove('text-error');
                } else {
                    icon.innerText = 'favorite';
                    bookmarkBtn.classList.add('text-error');
                }
            }
        });
    }
</script>
@endpush
