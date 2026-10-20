@extends('layouts.app')

@section('content')
<div class="flex flex-col w-full">
    <!-- SECTION HERO -->
    <section class="relative w-full overflow-hidden bg-gradient-to-b from-surface to-surface-container-low/60 pt-8 pb-20 lg:pt-14 lg:pb-28">
        <!-- Subtle Ambient Blobs -->
        <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-[760px] h-[340px] bg-primary-fixed/30 rounded-full blur-3xl pointer-events-none -z-10"></div>
        <div class="absolute top-48 right-10 w-96 h-96 bg-secondary-fixed/35 rounded-full blur-3xl pointer-events-none -z-10"></div>
        <div class="max-w-[1280px] mx-auto px-6 lg:px-12 flex flex-col items-center text-center">
            <!-- Badge Ramah -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-surface-container shadow-sm mb-6 animate-pulse">
                <span class="text-sm">🌱</span>
                <span class="font-label-md text-label-md text-on-surface-variant tracking-wide font-medium">Rekomendasi Cerdas Wisata Tanah Pasundan</span>
            </div>
            <!-- Judul Utama -->
            <h1 class="font-display text-display text-on-surface max-w-4xl tracking-tight leading-tight mb-6" data-reveal style="--i:0">
                Temukan wisata Jawa Barat yang <span class="text-primary italic">pas untukmu</span>
            </h1>
            <!-- Subjudul Pendek -->
            <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mb-10 text-balance leading-relaxed" data-reveal style="--i:1">
                Jelajahi keindahan alam pegunungan, air terjun tersembunyi, hingga pantai eksotis di Jawa Barat sesuai preferensi liburanmu.
            </p>
            <!-- CTA Button & Fast Filter -->
            <div class="flex flex-col sm:flex-row items-center gap-4 mb-14" data-reveal style="--i:2">
                <a class="inline-flex items-center justify-center gap-3 px-8 py-4 rounded-xl bg-secondary-container hover:bg-secondary text-on-secondary-container hover:text-on-secondary font-headline-sm text-headline-sm transition-all duration-300 shadow-[0_8px_24px_rgba(254,165,65,0.35)] hover:shadow-[0_12px_28px_rgba(139,80,0,0.3)] hover:-translate-y-0.5 active:scale-95 group" data-path="cari-rekomendasi" href="{{ url('/rekomendasi') }}">
                    <span class="material-symbols-outlined text-2xl transition-transform duration-300 group-hover:rotate-45">explore</span>
                    <span>Cari Rekomendasi</span>
                </a>
                <a class="inline-flex items-center justify-center gap-2 px-6 py-4 rounded-xl bg-surface-container-lowest hover:bg-surface-container text-on-surface font-label-lg text-label-lg transition-all duration-200 shadow-sm hover:shadow" data-path="katalog" href="{{ url('/katalog') }}">
                    <span class="material-symbols-outlined text-[20px] text-primary">travel_explore</span>
                    <span>Jelajah Semua Wisata</span>
                </a>
            </div>
            <!-- Statistik Ringkas -->
            <div class="inline-flex flex-wrap items-center justify-center gap-6 sm:gap-10 py-3.5 px-8 rounded-full bg-surface-container-lowest/80 backdrop-blur-md shadow-sm">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-primary text-[20px]">landscape</span>
                    <span class="font-label-lg text-label-lg text-on-surface font-bold">25++</span>
                    <span class="font-body-sm text-body-sm text-on-surface-variant">Destinasi Terkurasi</span>
                </div>
                <div class="w-1.5 h-1.5 rounded-full bg-outline-variant hidden sm:block"></div>
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-secondary text-[20px]">map</span>
                    <span class="font-label-lg text-label-lg text-on-surface font-bold">10+</span>
                    <span class="font-body-sm text-body-sm text-on-surface-variant">Kota & Kabupaten</span>
                </div>
                <div class="w-1.5 h-1.5 rounded-full bg-outline-variant hidden sm:block"></div>
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-primary-container text-[20px]">verified</span>
                    <span class="font-label-lg text-label-lg text-on-surface font-bold">100%</span>
                    <span class="font-body-sm text-body-sm text-on-surface-variant">Ulasan Terpercaya</span>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION CARA KERJA -->
    <section class="w-full py-20 lg:py-24 max-w-[1280px] mx-auto px-6 lg:px-12">
        <!-- Header Section -->
        <div class="flex flex-col items-center text-center mb-16" data-reveal>
            <span class="px-3.5 py-1 rounded-full bg-tertiary-fixed text-on-tertiary-fixed-variant font-label-sm text-label-sm uppercase tracking-wider mb-3">Panduan Mudah</span>
            <h2 class="font-headline-lg text-headline-lg text-on-surface tracking-tight mb-3">Cara Kerja</h2>
            <p class="font-body-lg text-body-lg text-on-surface-variant max-w-xl">
                3 langkah mudah menemukan destinasi impianmu di bumi Parahyangan
            </p>
        </div>
        <!-- 3 Langkah Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative" data-reveal-group>
            <!-- Step 1 -->
            <div class="group relative flex flex-col p-8 rounded-2xl bg-surface-container-lowest shadow-[0_4px_20px_-2px_rgba(46,125,91,0.06),0_2px_6px_-1px_rgba(31,41,55,0.04)] hover:shadow-[0_12px_28px_-4px_rgba(46,125,91,0.12),0_4px_12px_-2px_rgba(31,41,55,0.06)] transition-all duration-300 hover:-translate-y-1">
                <div class="flex items-center justify-between mb-8">
                    <div class="w-14 h-14 rounded-xl bg-primary/10 text-primary flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <span class="material-symbols-outlined text-3xl">tune</span>
                    </div>
                    <span class="font-display text-headline-lg text-outline-variant/40 select-none group-hover:text-primary transition-colors">01</span>
                </div>
                <h3 class="font-headline-sm text-headline-sm text-on-surface mb-3">Atur Preferensi</h3>
                <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                    Pilih kategori wisata alam favoritmu, lokasi wilayah, tingkat keramaian, hingga anggaran perjalanan secara leluasa.
                </p>
            </div>
            <!-- Step 2 -->
            <div class="group relative flex flex-col p-8 rounded-2xl bg-surface-container-lowest shadow-[0_4px_20px_-2px_rgba(46,125,91,0.06),0_2px_6px_-1px_rgba(31,41,55,0.04)] hover:shadow-[0_12px_28px_-4px_rgba(139,80,0,0.12),0_4px_12px_-2px_rgba(31,41,55,0.06)] transition-all duration-300 hover:-translate-y-1">
                <div class="flex items-center justify-between mb-8">
                    <div class="w-14 h-14 rounded-xl bg-secondary/10 text-secondary flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <span class="material-symbols-outlined text-3xl">psychology</span>
                    </div>
                    <span class="font-display text-headline-lg text-outline-variant/40 select-none group-hover:text-secondary transition-colors">02</span>
                </div>
                <h3 class="font-headline-sm text-headline-sm text-on-surface mb-3">Sistem Menghitung</h3>
                <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                    Algoritma rekomendasi kami mencocokkan kriteria dan menyaring pilihan terbaik secara otomatis dalam hitungan detik.
                </p>
            </div>
            <!-- Step 3 -->
            <div class="group relative flex flex-col p-8 rounded-2xl bg-surface-container-lowest shadow-[0_4px_20px_-2px_rgba(46,125,91,0.06),0_2px_6px_-1px_rgba(31,41,55,0.04)] hover:shadow-[0_12px_28px_-4px_rgba(46,125,91,0.12),0_4px_12px_-2px_rgba(31,41,55,0.06)] transition-all duration-300 hover:-translate-y-1">
                <div class="flex items-center justify-between mb-8">
                    <div class="w-14 h-14 rounded-xl bg-primary/10 text-primary flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;">stars</span>
                    </div>
                    <span class="font-display text-headline-lg text-outline-variant/40 select-none group-hover:text-primary transition-colors">03</span>
                </div>
                <h3 class="font-headline-sm text-headline-sm text-on-surface mb-3">Lihat Peringkat</h3>
                <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                    Dapatkan daftar rekomendasi tempat wisata teratas lengkap dengan ulasan, rute, dan estimasi biaya terperinci.
                </p>
            </div>
        </div>
    </section>

    <!-- SECTION DESTINASI POPULER -->
    <section class="w-full py-16 lg:py-24 bg-surface-container-low/40">
        <div class="max-w-[1280px] mx-auto px-6 lg:px-12">
            <!-- Section Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between gap-4 mb-12" data-reveal>
                <div>
                    <span class="px-3.5 py-1 rounded-full bg-primary-fixed text-on-primary-fixed font-label-sm text-label-sm uppercase tracking-wider mb-2.5 inline-block">Favorit Petualang</span>
                    <h2 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Destinasi Populer Jawa Barat</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant mt-1">Pilihan lokasi eksotis yang paling banyak dikunjungi dan mendapat rating memuaskan.</p>
                </div>
                <a class="inline-flex items-center gap-1.5 font-label-lg text-label-lg text-primary hover:text-primary-container font-semibold transition-colors group" data-path="katalog" href="{{ url('/katalog') }}">
                    <span>Lihat Semua Katalog</span>
                    <span class="material-symbols-outlined text-[18px] transition-transform group-hover:translate-x-1">arrow_forward</span>
                </a>
            </div>
            <!-- 4 Kolom Destinasi Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" data-reveal-group>
                @foreach ($populer as $item)
                <article class="card-lift group flex flex-col rounded-2xl bg-surface-container-lowest overflow-hidden shadow-[0_4px_20px_-2px_rgba(46,125,91,0.06),0_2px_6px_-1px_rgba(31,41,55,0.04)] hover:shadow-[0_16px_32px_-4px_rgba(46,125,91,0.14)] transition-all duration-300 hover:-translate-y-1.5">
                    <div class="relative w-full aspect-[4/3] overflow-hidden">
                        <img alt="{{ $item->nama }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105 img-fade" src="{{ $item->foto_url }}"/>
                        <div class="absolute inset-0 bg-gradient-to-t from-on-surface/60 via-transparent to-transparent opacity-60"></div>
                        <div class="absolute top-3 left-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-primary-fixed text-on-primary-fixed font-label-sm text-label-sm shadow-sm">
                                {{ $item->jenis }}
                            </span>
                        </div>
                        <button aria-label="Simpan destinasi favorit" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-surface-container-lowest/90 backdrop-blur-sm text-on-surface flex items-center justify-center hover:text-error transition-colors shadow-sm" type="button">
                            <span class="material-symbols-outlined text-[18px]">favorite</span>
                        </button>
                        <div class="absolute bottom-3 left-3 flex items-center gap-1 text-on-primary text-xs font-medium bg-black/40 px-2 py-0.5 rounded-full backdrop-blur-sm">
                            <span class="material-symbols-outlined text-[14px] text-secondary-container" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span>{{ number_format($item->rating, 1) }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col flex-1 p-5">
                        <div class="flex items-center gap-1 text-on-surface-variant font-body-sm text-body-sm mb-1.5">
                            <span class="material-symbols-outlined text-[16px] text-tertiary">location_on</span>
                            <span class="line-clamp-1">{{ $item->wilayah }}</span>
                        </div>
                        <h3 class="font-headline-sm text-headline-sm text-on-surface group-hover:text-primary transition-colors line-clamp-1 mb-4">
                            {{ $item->nama }}
                        </h3>
                        <div class="mt-auto pt-3 flex items-center justify-between border-t border-surface-container-high/60">
                            <div class="flex flex-col">
                                <span class="font-body-sm text-body-sm text-on-surface-variant text-[11px]">Tiket Masuk Mulai</span>
                                <span class="font-label-lg text-label-lg text-primary font-bold">
                                    @if($item->biaya > 0)
                                        Rp {{ number_format($item->biaya, 0, ',', '.') }}
                                    @else
                                        Gratis
                                    @endif
                                    <span class="font-normal text-xs text-on-surface-variant">/ orang</span>
                                </span>
                            </div>
                            <a class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-on-primary transition-colors" data-path="detail-wisata" href="{{ url('/destinasi/' . $item->id) }}" aria-label="Detail {{ $item->nama }}">
                                <span class="material-symbols-outlined text-[18px]">arrow_outward</span>
                            </a>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CALL TO ACTION BANNER -->
    <section class="w-full py-16 max-w-[1280px] mx-auto px-6 lg:px-12" data-reveal="scale">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-primary to-primary-container p-10 lg:p-14 text-on-primary shadow-xl flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="absolute -right-10 -bottom-10 w-72 h-72 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
            <div class="max-w-xl text-center md:text-left z-10">
                <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary-fixed mb-2 inline-block">Siap Memulai Liburan?</span>
                <h3 class="font-headline-lg text-headline-lg text-on-primary tracking-tight mb-3">
                    Temukan Rute Terbaik dalam 2 Menit
                </h3>
                <p class="font-body-md text-body-md text-on-primary/80">
                    Masukkan preferensi liburan keluargamu sekarang dan dapatkan itinerary otomatis khusus destinasi Pasundan.
                </p>
            </div>
            <div class="z-10 flex-shrink-0">
                <a class="inline-flex items-center gap-3 px-8 py-4 rounded-xl bg-secondary-container hover:bg-secondary text-on-secondary-container hover:text-on-secondary font-headline-sm text-headline-sm transition-all duration-300 shadow-md hover:scale-105" data-path="cari-rekomendasi" href="{{ url('/rekomendasi') }}">
                    <span class="material-symbols-outlined">map</span>
                    <span>Mulai Rekomendasi</span>
                </a>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    // Micro-interaction for favorite toggle buttons
    document.querySelectorAll('button[aria-label="Simpan destinasi favorit"]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const icon = btn.querySelector('.material-symbols-outlined');
            if (icon) {
                const isFilled = icon.style.fontVariationSettings?.includes("'FILL' 1");
                if (isFilled) {
                    icon.style.fontVariationSettings = "'FILL' 0";
                    btn.classList.remove('text-error');
                } else {
                    icon.style.fontVariationSettings = "'FILL' 1";
                    btn.classList.add('text-error');
                }
            }
        });
    });
</script>
@endpush
