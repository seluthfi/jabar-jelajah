@extends('layouts.app')

@section('content')
<div class="flex flex-col w-full">
    <!-- Top Breadcrumb & Page Intro Section -->
    <section class="max-w-[1280px] w-full mx-auto px-6 lg:px-12 pt-6 pb-8">
        <!-- Breadcrumb -->
        <nav aria-label="Breadcrumb" class="flex items-center gap-2 font-label-md text-label-md text-on-surface-variant mb-4">
            <a class="hover:text-primary transition-colors flex items-center gap-1" href="{{ url('/') }}">
                <span class="material-symbols-outlined text-[16px]">home</span>
                <span>Beranda</span>
            </a>
            <span class="text-outline-variant">/</span>
            <span class="text-primary font-semibold">Cari Rekomendasi</span>
        </nav>

        <!-- Header Text Block -->
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 pb-6 border-b border-surface-variant/70">
            <div class="max-w-3xl">
                <h1 class="font-display text-display text-on-surface tracking-tight leading-tight">
                    Cari Rekomendasi Cerdas Wisata Pasundan
                </h1>
                <p class="font-body-lg text-body-lg text-on-surface-variant mt-3 leading-relaxed">
                    Sesuaikan preferensi perjalananmu dengan metode pendukung keputusan multikriteria (MCDM VIKOR) untuk rekomendasi destinasi yang objektif, transparan, dan terukur.
                </p>
            </div>
        </div>
    </section>

    <!-- Two-Column Main Workbench -->
    <section class="max-w-[1280px] w-full mx-auto px-6 lg:px-12 pb-20">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- SISI KIRI - Form Preferensi (5 of 12 cols) -->
            <div class="lg:col-span-5 lg:sticky lg:top-24 space-y-6">
                <div class="bg-surface-container-lowest rounded-2xl p-6 lg:p-7 shadow-[0_4px_24px_rgba(46,125,91,0.06)] border border-surface-variant/60">
                    <!-- Form Header -->
                    <div class="flex items-center justify-between pb-5 border-b border-surface-variant/50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary-container/15 flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined text-[22px]">tune</span>
                            </div>
                            <div>
                                <h2 class="font-headline-sm text-headline-sm text-on-surface">Preferensi Perjalanan</h2>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">Atur parameter &amp; bobot pertimbangan</p>
                            </div>
                        </div>
                        <button id="btn-reset" class="text-on-surface-variant hover:text-primary p-2 rounded-lg hover:bg-surface-container transition-colors" title="Reset Default" type="button">
                            <span class="material-symbols-outlined text-[20px]">refresh</span>
                        </button>
                    </div>

                    <form id="form-rekomendasi" class="space-y-6 mt-6">
                        <!-- (a) Dropdown Kota Keberangkatan -->
                        <div class="space-y-2">
                            <label for="f-asal" class="block font-label-lg text-label-lg text-on-surface flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-[18px]">location_on</span>
                                Berangkat dari Kota
                            </label>
                            <div class="relative">
                                <select id="f-asal" name="asal" class="w-full appearance-none bg-surface-container-low text-on-surface font-body-md text-body-md rounded-xl px-4 py-3 pr-10 focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all cursor-pointer">
                                    @foreach($kotaList as $kota)
                                        <option value="{{ $kota->nama }}" {{ $kota->nama === 'Bandung' ? 'selected' : '' }}>{{ $kota->nama }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant text-[20px]">
                                    expand_more
                                </span>
                            </div>
                        </div>

                        <!-- (b) Pilihan Kategori / Jenis Wisata (5 nilai kolom jenis) -->
                        <div class="space-y-2.5">
                            <label class="block font-label-lg text-label-lg text-on-surface flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-[18px]">category</span>
                                Jenis Wisata Impian
                            </label>
                            <div id="chips-jenis" class="flex flex-wrap gap-2">
                                @php
                                    $kategoriDaftar = [
                                        ['nama' => 'Pantai', 'ikon' => 'surfing'],
                                        ['nama' => 'Pegunungan', 'ikon' => 'landscape'],
                                        ['nama' => 'Air & Danau', 'ikon' => 'water_drop'],
                                        ['nama' => 'Taman & Keluarga', 'ikon' => 'park'],
                                        ['nama' => 'Budaya & Sejarah', 'ikon' => 'museum'],
                                    ];
                                @endphp
                                @foreach($kategoriDaftar as $kat)
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="jenis[]" value="{{ $kat['nama'] }}" class="peer hidden" checked>
                                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-label-md font-label-md bg-surface-container-low text-on-surface-variant peer-checked:bg-primary peer-checked:text-on-primary transition-all shadow-xs">
                                            <span class="material-symbols-outlined text-[15px]">{{ $kat['ikon'] }}</span>
                                            {{ $kat['nama'] }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Kriteria Slider & Tingkat Kepentingan (0 - 3) -->
                        <div class="pt-2 space-y-5">
                            <div class="flex items-center justify-between pb-1">
                                <span class="font-label-sm text-label-sm uppercase tracking-wider text-primary font-bold">Kriteria &amp; Pembobotan (0 - 3)</span>
                                <span class="font-body-sm text-body-sm text-on-surface-variant">0: Abaikan • 3: Sangat Penting</span>
                            </div>

                            <!-- (c) Anggaran Maksimal (Filter) & Bobot Biaya -->
                            <div class="bg-surface-container-low/60 rounded-xl p-3.5 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary text-[18px]">payments</span>
                                        <span class="font-label-md text-label-md text-on-surface">Anggaran Tiket Maksimal</span>
                                    </div>
                                    <span id="o-biaya" class="font-label-sm text-label-sm text-primary font-bold bg-primary/10 px-2 py-0.5 rounded">Rp 50.000</span>
                                </div>
                                <input id="f-biaya" class="w-full accent-primary h-1.5 bg-surface-variant rounded-lg cursor-pointer" max="250000" min="10000" step="5000" type="range" value="50000">
                                <div class="flex items-center justify-between pt-1">
                                    <span class="font-body-sm text-body-sm text-on-surface-variant">Seberapa penting biaya?</span>
                                    <div class="flex items-center gap-1.5" data-penting="biaya">
                                        <button type="button" data-val="0" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high">0</button>
                                        <button type="button" data-val="1" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-primary text-on-primary font-bold shadow-xs">1</button>
                                        <button type="button" data-val="2" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high">2</button>
                                        <button type="button" data-val="3" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high">3</button>
                                    </div>
                                </div>
                            </div>

                            <!-- (d) Jarak Tempuh Target & Bobot -->
                            <div class="bg-surface-container-low/60 rounded-xl p-3.5 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary text-[18px]">commute</span>
                                        <span class="font-label-md text-label-md text-on-surface">Target Jarak Tempuh</span>
                                    </div>
                                    <span id="o-jarak" class="font-label-sm text-label-sm text-primary font-bold bg-primary/10 px-2 py-0.5 rounded">60 km</span>
                                </div>
                                <input id="f-jarak" class="w-full accent-primary h-1.5 bg-surface-variant rounded-lg cursor-pointer" max="300" min="0" step="10" type="range" value="60">
                                <div class="flex items-center justify-between pt-1">
                                    <span class="font-body-sm text-body-sm text-on-surface-variant">Seberapa penting jarak?</span>
                                    <div class="flex items-center gap-1.5" data-penting="jarak">
                                        <button type="button" data-val="0" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high">0</button>
                                        <button type="button" data-val="1" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high">1</button>
                                        <button type="button" data-val="2" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-primary text-on-primary font-bold shadow-xs">2</button>
                                        <button type="button" data-val="3" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high">3</button>
                                    </div>
                                </div>
                            </div>

                            <!-- (e) Suhu & Kesejukan Udara Target & Bobot -->
                            <div class="bg-surface-container-low/60 rounded-xl p-3.5 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary text-[18px]">device_thermostat</span>
                                        <span class="font-label-md text-label-md text-on-surface">Target Suhu Udara</span>
                                    </div>
                                    <span id="o-suhu" class="font-label-sm text-label-sm text-primary font-bold bg-primary/10 px-2 py-0.5 rounded">20°C</span>
                                </div>
                                <input id="f-suhu" class="w-full accent-primary h-1.5 bg-surface-variant rounded-lg cursor-pointer" max="30" min="16" step="1" type="range" value="20">
                                <div class="flex items-center justify-between pt-1">
                                    <span class="font-body-sm text-body-sm text-on-surface-variant">Seberapa penting suhu?</span>
                                    <div class="flex items-center gap-1.5" data-penting="suhu">
                                        <button type="button" data-val="0" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high">0</button>
                                        <button type="button" data-val="1" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high">1</button>
                                        <button type="button" data-val="2" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high">2</button>
                                        <button type="button" data-val="3" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-primary text-on-primary font-bold shadow-xs">3</button>
                                    </div>
                                </div>
                            </div>

                            <!-- (f) Tingkat Keramaian Target & Bobot -->
                            <div class="bg-surface-container-low/60 rounded-xl p-3.5 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary text-[18px]">groups</span>
                                        <span class="font-label-md text-label-md text-on-surface">Target Keramaian</span>
                                    </div>
                                    <span id="o-ramai" class="font-label-sm text-label-sm text-primary font-bold bg-primary/10 px-2 py-0.5 rounded">Sepi</span>
                                </div>
                                <input id="f-ramai" class="w-full accent-primary h-1.5 bg-surface-variant rounded-lg cursor-pointer" max="5" min="1" step="1" type="range" value="2">
                                <div class="flex items-center justify-between pt-1">
                                    <span class="font-body-sm text-body-sm text-on-surface-variant">Seberapa penting keramaian?</span>
                                    <div class="flex items-center gap-1.5" data-penting="keramaian">
                                        <button type="button" data-val="0" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high">0</button>
                                        <button type="button" data-val="1" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high">1</button>
                                        <button type="button" data-val="2" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-primary text-on-primary font-bold shadow-xs">2</button>
                                        <button type="button" data-val="3" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high">3</button>
                                    </div>
                                </div>
                            </div>

                            <!-- (g) Kelengkapan Fasilitas Target & Bobot -->
                            <div class="bg-surface-container-low/60 rounded-xl p-3.5 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary text-[18px]">park</span>
                                        <span class="font-label-md text-label-md text-on-surface">Target Kelengkapan Fasilitas</span>
                                    </div>
                                    <span id="o-fas" class="font-label-sm text-label-sm text-primary font-bold bg-primary/10 px-2 py-0.5 rounded">Cukup</span>
                                </div>
                                <input id="f-fas" class="w-full accent-primary h-1.5 bg-surface-variant rounded-lg cursor-pointer" max="5" min="1" step="1" type="range" value="3">
                                <div class="flex items-center justify-between pt-1">
                                    <span class="font-body-sm text-body-sm text-on-surface-variant">Seberapa penting fasilitas?</span>
                                    <div class="flex items-center gap-1.5" data-penting="fasilitas">
                                        <button type="button" data-val="0" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high">0</button>
                                        <button type="button" data-val="1" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-primary text-on-primary font-bold shadow-xs">1</button>
                                        <button type="button" data-val="2" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high">2</button>
                                        <button type="button" data-val="3" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high">3</button>
                                    </div>
                                </div>
                            </div>

                            <!-- (h) Tingkat Kepentingan Rating Wisatawan (Benefit) -->
                            <div class="bg-surface-container-low/60 rounded-xl p-3.5 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-secondary text-[18px]">star</span>
                                        <span class="font-label-md text-label-md text-on-surface">Tingkat Rating Pengunjung</span>
                                    </div>
                                    <span class="font-label-sm text-label-sm text-secondary font-bold bg-secondary/10 px-2 py-0.5 rounded">Makin tinggi makin baik</span>
                                </div>
                                <div class="flex items-center justify-between pt-1">
                                    <span class="font-body-sm text-body-sm text-on-surface-variant">Seberapa penting rating?</span>
                                    <div class="flex items-center gap-1.5" data-penting="rating">
                                        <button type="button" data-val="0" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high">0</button>
                                        <button type="button" data-val="1" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-primary text-on-primary font-bold shadow-xs">1</button>
                                        <button type="button" data-val="2" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high">2</button>
                                        <button type="button" data-val="3" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high">3</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- (i) Tiga Radio Cards Gaya Keputusan (v persis SPEC 6.1) -->
                        <div class="space-y-2.5 pt-2">
                            <label class="block font-label-lg text-label-lg text-on-surface">Gaya Keputusan (Bobot Strategi v)</label>
                            <div class="flex flex-col gap-2">
                                <!-- Option 1: Seimbang (v = 0.5) -->
                                <label class="gaya-card relative p-3.5 rounded-xl border border-primary/30 bg-primary/5 cursor-pointer flex items-start gap-3 transition-colors">
                                    <input type="radio" name="gaya_v" value="0.5" class="mt-1 accent-primary" checked>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-title text-title text-primary text-[15px]">Seimbang</span>
                                            <span class="font-label-sm text-label-sm font-mono text-primary bg-primary-fixed/50 px-2 py-0.5 rounded">v = 0.5</span>
                                        </div>
                                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">Menyeimbangkan kepuasan rata-rata dan kekurangan terburuk</p>
                                    </div>
                                </label>

                                <!-- Option 2: Utamakan Mayoritas (v = 0.8) -->
                                <label class="gaya-card relative p-3.5 rounded-xl border border-surface-variant/40 bg-surface-container-low hover:bg-surface-container cursor-pointer flex items-start gap-3 transition-colors">
                                    <input type="radio" name="gaya_v" value="0.8" class="mt-1 accent-primary">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-title text-title text-on-surface text-[15px]">Utamakan mayoritas</span>
                                            <span class="font-label-sm text-label-sm font-mono text-on-surface-variant bg-surface-container px-2 py-0.5 rounded">v = 0.8</span>
                                        </div>
                                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">Mengutamakan kecocokan rata-rata di sebagian besar kriteria</p>
                                    </div>
                                </label>

                                <!-- Option 3: Hindari Kekurangan (v = 0.2) -->
                                <label class="gaya-card relative p-3.5 rounded-xl border border-surface-variant/40 bg-surface-container-low hover:bg-surface-container cursor-pointer flex items-start gap-3 transition-colors">
                                    <input type="radio" name="gaya_v" value="0.2" class="mt-1 accent-primary">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-title text-title text-on-surface text-[15px]">Hindari kekurangan</span>
                                            <span class="font-label-sm text-label-sm font-mono text-on-surface-variant bg-surface-container px-2 py-0.5 rounded">v = 0.2</span>
                                        </div>
                                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">Menghindari destinasi yang sangat buruk di satu kriteria</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- (j) Tombol Utama Cari Rekomendasi -->
                        <div class="space-y-2 pt-2">
                            <button id="btn-submit" class="w-full py-4 px-6 rounded-xl bg-secondary-container hover:bg-secondary text-on-secondary-container hover:text-on-secondary font-headline-sm text-headline-sm transition-all duration-200 flex items-center justify-center gap-3 shadow-[0_8px_20px_rgba(242,155,56,0.25)] active:scale-[0.99] cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed" type="submit">
                                <span class="material-symbols-outlined text-[24px]">explore</span>
                                <span id="btn-submit-text">Tampilkan Rekomendasi</span>
                            </button>
                            <p class="font-body-sm text-body-sm text-center text-on-surface-variant">Menghitung {{ $totalAktif }} titik wisata aktif di Jawa Barat</p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SISI KANAN - Lima Kondisi Panel Hasil (7 of 12 cols) -->
            <div class="lg:col-span-7 space-y-6">
                <!-- ================= KONDISI 1: AWAL (Belum Mencari) ================= -->
                <div id="panel-awal" class="bg-surface-container-lowest rounded-2xl border border-surface-variant/60 shadow-[0_4px_24px_rgba(46,125,91,0.06)] p-8 lg:p-12 flex flex-col items-center justify-center text-center">
                    <div class="w-20 h-20 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-6 shadow-sm">
                        <span class="material-symbols-outlined text-4xl">explore</span>
                    </div>
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-3 max-w-lg">
                        Atur preferensimu, lalu tekan Tampilkan Rekomendasi
                    </h2>
                    <p class="font-body-md text-body-md text-on-surface-variant max-w-md mb-8 leading-relaxed">
                        Sistem pendukung keputusan Jelajah Jabar akan mengolah preferensimu menggunakan algoritma kompromi multikriteria VIKOR.
                    </p>

                    <!-- 3 Langkah Panduan -->
                    <div class="w-full max-w-md grid grid-cols-1 gap-3.5 text-left">
                        <div class="flex items-start gap-3.5 p-4 rounded-xl bg-surface-container-low border border-surface-variant/40">
                            <span class="w-6 h-6 rounded-full bg-primary text-on-primary font-bold text-xs flex items-center justify-center flex-shrink-0 mt-0.5">1</span>
                            <p class="font-body-sm text-body-sm text-on-surface">Atur jarak, suhu, keramaian, fasilitas, dan anggaran</p>
                        </div>
                        <div class="flex items-start gap-3.5 p-4 rounded-xl bg-surface-container-low border border-surface-variant/40">
                            <span class="w-6 h-6 rounded-full bg-primary text-on-primary font-bold text-xs flex items-center justify-center flex-shrink-0 mt-0.5">2</span>
                            <p class="font-body-sm text-body-sm text-on-surface">Sistem menghitung peringkat dengan metode VIKOR</p>
                        </div>
                        <div class="flex items-start gap-3.5 p-4 rounded-xl bg-surface-container-low border border-surface-variant/40">
                            <span class="w-6 h-6 rounded-full bg-primary text-on-primary font-bold text-xs flex items-center justify-center flex-shrink-0 mt-0.5">3</span>
                            <p class="font-body-sm text-body-sm text-on-surface">Lihat destinasi paling cocok beserta alasannya</p>
                        </div>
                    </div>
                </div>

                <!-- ================= KONDISI 2: MEMUAT (Skeleton Loading) ================= -->
                <div id="panel-memuat" class="hidden bg-surface-container-lowest rounded-2xl border border-surface-variant/60 shadow-sm p-6 lg:p-8 space-y-6">
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                        <span class="font-headline-sm text-headline-sm text-primary">Menghitung...</span>
                    </div>

                    <div class="space-y-4">
                        @for($i = 0; $i < 3; $i++)
                            <div class="p-4 rounded-2xl bg-surface-container-low border border-surface-variant/40 animate-pulse flex flex-col md:flex-row gap-4">
                                <div class="w-full md:w-44 h-32 bg-surface-variant/70 rounded-xl flex-shrink-0"></div>
                                <div class="flex-1 space-y-3 py-1">
                                    <div class="h-4 bg-surface-variant/70 rounded w-1/3"></div>
                                    <div class="h-6 bg-surface-variant/70 rounded w-2/3"></div>
                                    <div class="h-4 bg-surface-variant/70 rounded w-1/2"></div>
                                    <div class="h-4 bg-surface-variant/50 rounded w-full"></div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>

                <!-- ================= KONDISI 3: HASIL REKOMENDASI ================= -->
                <div id="panel-hasil" class="hidden space-y-6">
                    <!-- Header Hasil -->
                    <div class="bg-surface-container-lowest rounded-2xl p-5 shadow-sm border border-surface-variant/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-primary-container"></span>
                                <h2 id="hasil-judul" class="font-headline-md text-headline-md text-on-surface">Menampilkan 5 teratas dari 12 destinasi</h2>
                            </div>
                            <p id="hasil-subjudul" class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">Berdasarkan keberangkatan Bandung</p>
                        </div>
                        <div class="flex items-center gap-2 self-start sm:self-auto">
                            <span id="hasil-badge-mode" class="px-3 py-1 rounded-full bg-primary-fixed text-on-primary-fixed font-label-sm text-label-sm flex items-center gap-1.5 font-semibold">
                                Satu pilihan terbaik
                            </span>
                        </div>
                    </div>

                    <!-- Daftar Kartu Hasil -->
                    <div id="hasil-cards-container" class="space-y-5">
                        <!-- Kartu hasil dirender secara dinamis oleh JavaScript -->
                    </div>

                    <!-- Bagian Edukatif: "Lihat Cara Menghitung" -->
                    <div class="bg-surface-container-lowest rounded-2xl border border-surface-variant/60 p-6 shadow-sm">
                        <details class="group cursor-pointer">
                            <summary class="flex items-center justify-between font-headline-sm text-headline-sm text-primary list-none select-none">
                                <span class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[22px]">calculate</span>
                                    Lihat cara menghitung
                                </span>
                                <span class="material-symbols-outlined transition-transform duration-200 group-open:rotate-180">
                                    expand_more
                                </span>
                            </summary>

                            <div class="pt-6 mt-4 border-t border-surface-variant/60 space-y-6 text-on-surface font-body-md text-body-md cursor-auto">
                                <!-- Rumus Persen -->
                                <div class="p-4 rounded-xl bg-surface-container-low border border-surface-variant/40 space-y-2">
                                    <h3 class="font-title text-title text-on-surface font-semibold">Rumus Kecocokan Relatif (Persen)</h3>
                                    <p class="font-mono text-sm bg-surface-container px-3 py-2 rounded-lg text-primary font-bold inline-block">
                                        persen = round((1 - Q) × 100, 1)
                                    </p>
                                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
                                        Karena nilai indeks kompromi Q dinormalisasi antar kandidat (makin kecil makin baik), persen bersifat <strong>relatif terhadap kandidat saat ini</strong>: kandidat peringkat 1 selalu bernilai 100% dan yang paling bawah 0%.
                                    </p>
                                </div>

                                <!-- Hasil Uji Keunggulan & Kestabilan -->
                                <div class="p-4 rounded-xl bg-surface-container-low border border-surface-variant/40 space-y-3">
                                    <h3 class="font-title text-title text-on-surface font-semibold">Hasil Uji Kompromi VIKOR</h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-body-sm">
                                        <div class="p-3 rounded-lg bg-surface-container border border-surface-variant/30 space-y-1">
                                            <span class="font-label-sm text-label-sm text-on-surface-variant block">Uji Keunggulan (Acceptable Advantage)</span>
                                            <span id="calc-keunggulan" class="font-semibold text-on-surface block">-</span>
                                            <span id="calc-dq" class="text-xs text-on-surface-variant block font-mono">Batas DQ = 1/(m-1)</span>
                                        </div>
                                        <div class="p-3 rounded-lg bg-surface-container border border-surface-variant/30 space-y-1">
                                            <span class="font-label-sm text-label-sm text-on-surface-variant block">Uji Kestabilan (Acceptable Stability)</span>
                                            <span id="calc-kestabilan" class="font-semibold text-on-surface block">-</span>
                                            <span class="text-xs text-on-surface-variant block">A1 terbaik pada utilitas S atau penyesalan R</span>
                                        </div>
                                    </div>
                                    <div class="pt-2 text-body-sm text-on-surface-variant">
                                        Mode keputusan yang dihasilkan: <strong id="calc-mode-hasil" class="text-primary font-bold">-</strong>
                                    </div>
                                </div>

                                <!-- Bobot Ternormalisasi -->
                                <div class="space-y-2">
                                    <h3 class="font-title text-title text-on-surface font-semibold">Bobot Ternormalisasi Kriteria</h3>
                                    <div id="calc-bobot-container" class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                        <!-- Bobot ternormalisasi dari API -->
                                    </div>
                                </div>

                                <!-- Tabel Nilai Ideal (f*) dan Terburuk (f-) -->
                                <div class="space-y-2">
                                    <h3 class="font-title text-title text-on-surface font-semibold">Nilai Ideal (f*) dan Terburuk (f-)</h3>
                                    <div class="overflow-x-auto rounded-xl border border-surface-variant/50">
                                        <table class="w-full text-left text-body-sm border-collapse">
                                            <thead class="bg-surface-container font-label-md text-label-md text-on-surface">
                                                <tr>
                                                    <th class="p-3">Kriteria</th>
                                                    <th class="p-3">Tipe</th>
                                                    <th class="p-3">Target Pilihan</th>
                                                    <th class="p-3 text-primary">Nilai Ideal (f*)</th>
                                                    <th class="p-3 text-secondary">Nilai Terburuk (f-)</th>
                                                </tr>
                                            </thead>
                                            <tbody id="calc-tabel-ideal" class="divide-y divide-surface-variant/40 bg-surface-container-lowest">
                                                <!-- Nilai ideal f* dan f- -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Tabel S, R, Q Semua Kandidat -->
                                <div class="space-y-2">
                                    <h3 class="font-title text-title text-on-surface font-semibold">Tabel Nilai S, R, dan Q Semua Kandidat</h3>
                                    <div class="overflow-x-auto rounded-xl border border-surface-variant/50">
                                        <table class="w-full text-left text-body-sm border-collapse">
                                            <thead class="bg-surface-container font-label-md text-label-md text-on-surface">
                                                <tr>
                                                    <th class="p-3 text-center">Peringkat</th>
                                                    <th class="p-3">Destinasi</th>
                                                    <th class="p-3">S (Utilitas)</th>
                                                    <th class="p-3">R (Penyesalan)</th>
                                                    <th class="p-3">Q (Kompromi)</th>
                                                    <th class="p-3">Kecocokan Relatif</th>
                                                    <th class="p-3">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="calc-tabel-srq" class="divide-y divide-surface-variant/40 bg-surface-container-lowest">
                                                <!-- Nilai S, R, Q -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </details>
                    </div>
                </div>

                <!-- ================= KONDISI 4: TIDAK ADA HASIL ================= -->
                <div id="panel-kosong" class="hidden bg-surface-container-lowest rounded-2xl border border-surface-variant/60 shadow-sm p-8 lg:p-12 flex flex-col items-center justify-center text-center">
                    <div class="w-16 h-16 rounded-full bg-secondary/10 text-secondary flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-3xl">search_off</span>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface mb-2">
                        Tidak ada destinasi yang cocok
                    </h3>
                    <p class="font-body-md text-body-md text-on-surface-variant max-w-md mb-6 leading-relaxed">
                        Saran: naikkan anggaran maksimal atau tambah jenis wisata agar destinasi yang sesuai dapat ditemukan.
                    </p>
                    <button type="button" onclick="document.getElementById('f-biaya').value=250000; document.getElementById('o-biaya').textContent='Tanpa batas'; document.querySelectorAll('#chips-jenis input').forEach(c=>c.checked=true); document.getElementById('btn-submit').click();" class="px-6 py-2.5 rounded-full bg-primary-container text-on-primary font-label-lg text-label-lg shadow-sm hover:opacity-95 transition-all cursor-pointer">
                        Reset Anggaran &amp; Cari Ulang
                    </button>
                </div>

                <!-- ================= KONDISI 5: ERROR ================= -->
                <div id="panel-error" class="hidden bg-surface-container-lowest rounded-2xl border border-error/40 shadow-sm p-8 flex flex-col items-center justify-center text-center">
                    <div class="w-14 h-14 rounded-full bg-error-container text-on-error-container flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-3xl">error_outline</span>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface mb-2">Terjadi Kesalahan</h3>
                    <div id="error-pesan" class="font-body-md text-body-md text-error max-w-md mb-6 leading-relaxed">
                        Tidak dapat menghubungi server, coba lagi.
                    </div>
                    <button type="button" onclick="document.getElementById('btn-submit').click();" class="px-5 py-2.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md transition-colors cursor-pointer">
                        Coba Lagi
                    </button>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document(() => {}); // placeholder
</script>
<script>
(function() {
    // 1. DOM Elements
    const form = document.getElementById('form-rekomendasi');
    const fAsal = document.getElementById('f-asal');
    const fBiaya = document.getElementById('f-biaya');
    const oBiaya = document.getElementById('o-biaya');
    const fJarak = document.getElementById('f-jarak');
    const oJarak = document.getElementById('o-jarak');
    const fSuhu = document.getElementById('f-suhu');
    const oSuhu = document.getElementById('o-suhu');
    const fRamai = document.getElementById('f-ramai');
    const oRamai = document.getElementById('o-ramai');
    const fFas = document.getElementById('f-fas');
    const oFas = document.getElementById('o-fas');
    const btnSubmit = document.getElementById('btn-submit');
    const btnReset = document.getElementById('btn-reset');

    const LBL_RAMAI = ['', 'Sangat Sepi', 'Sepi', 'Sedang', 'Ramai', 'Sangat Ramai'];
    const LBL_FAS = ['', 'Sangat Sederhana', 'Sederhana', 'Cukup', 'Lengkap', 'Sangat Lengkap'];

    // Bobot penting state
    const pentingState = {
        biaya: 1,
        rating: 1,
        jarak: 2,
        suhu: 3,
        keramaian: 2,
        fasilitas: 1
    };

    // 2. Readout Sliders
    function updateReadouts() {
        const biaya = parseInt(fBiaya.value, 10);
        oBiaya.textContent = (biaya >= 250000) ? 'Tanpa batas' : 'Rp ' + biaya.toLocaleString('id-ID');

        const jarak = parseInt(fJarak.value, 10);
        oJarak.textContent = (jarak === 0) ? 'Sedekat mungkin' : jarak + ' km';

        const suhu = parseInt(fSuhu.value, 10);
        oSuhu.textContent = suhu + '°C';

        const ramai = parseInt(fRamai.value, 10);
        oRamai.textContent = LBL_RAMAI[ramai] || 'Sedang';

        const fas = parseInt(fFas.value, 10);
        oFas.textContent = LBL_FAS[fas] || 'Cukup';
    }

    [fBiaya, fJarak, fSuhu, fRamai, fFas].forEach(input => {
        input.addEventListener('input', updateReadouts);
    });
    updateReadouts();

    // 3. Tombol Bobot Penting (0 - 3)
    document.querySelectorAll('[data-penting]').forEach(container => {
        const key = container.getAttribute('data-penting');
        const buttons = container.querySelectorAll('.btn-penting');

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                const val = parseInt(btn.getAttribute('data-val'), 10);
                pentingState[key] = val;

                buttons.forEach(b => {
                    b.className = 'btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high';
                });
                btn.className = 'btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-primary text-on-primary font-bold shadow-xs';
            });
        });
    });

    // 4. Radio Card Gaya Keputusan
    const gayaCards = document.querySelectorAll('.gaya-card');
    document.querySelectorAll('input[name="gaya_v"]').forEach(radio => {
        radio.addEventListener('change', () => {
            gayaCards.forEach(card => {
                const r = card.querySelector('input[type="radio"]');
                if (r && r.checked) {
                    card.className = 'gaya-card relative p-3.5 rounded-xl border border-primary/30 bg-primary/5 cursor-pointer flex items-start gap-3 transition-colors';
                } else {
                    card.className = 'gaya-card relative p-3.5 rounded-xl border border-surface-variant/40 bg-surface-container-low hover:bg-surface-container cursor-pointer flex items-start gap-3 transition-colors';
                }
            });
        });
    });

    // 5. Reset Handler
    btnReset.addEventListener('click', () => {
        fAsal.value = 'Bandung';
        document.querySelectorAll('#chips-jenis input').forEach(c => c.checked = true);
        fBiaya.value = 50000;
        fJarak.value = 60;
        fSuhu.value = 20;
        fRamai.value = 2;
        fFas.value = 3;
        updateReadouts();

        // Reset radio v ke 0.5
        const defaultRadio = document.querySelector('input[name="gaya_v"][value="0.5"]');
        if (defaultRadio) {
            defaultRadio.checked = true;
            defaultRadio.dispatchEvent(new Event('change'));
        }

        // Reset penting
        const defaultPenting = { biaya: 1, rating: 1, jarak: 2, suhu: 3, keramaian: 2, fasilitas: 1 };
        Object.assign(pentingState, defaultPenting);
        document.querySelectorAll('[data-penting]').forEach(container => {
            const k = container.getAttribute('data-penting');
            const targetVal = defaultPenting[k];
            container.querySelectorAll('.btn-penting').forEach(b => {
                const val = parseInt(b.getAttribute('data-val'), 10);
                if (val === targetVal) {
                    b.className = 'btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-primary text-on-primary font-bold shadow-xs';
                } else {
                    b.className = 'btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high';
                }
            });
        });

        switchPanel('awal');
    });

    // 6. Switch Panel State (5 Kondisi)
    function switchPanel(state) {
        ['awal', 'memuat', 'hasil', 'kosong', 'error'].forEach(s => {
            const el = document.getElementById('panel-' + s);
            if (el) {
                if (s === state) {
                    el.classList.remove('hidden');
                } else {
                    el.classList.add('hidden');
                }
            }
        });
    }

    // 7. Form Submit & Fetch
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const jenisChecked = Array.from(document.querySelectorAll('#chips-jenis input:checked')).map(c => c.value);
        const biayaInt = parseInt(fBiaya.value, 10);
        const maxBiaya = (biayaInt >= 250000) ? null : biayaInt;

        const payload = {
            asal: fAsal.value,
            jenis: jenisChecked,
            maxBiaya: maxBiaya,
            target: {
                jarak: parseInt(fJarak.value, 10),
                suhu: parseInt(fSuhu.value, 10),
                keramaian: parseInt(fRamai.value, 10),
                fasilitas: parseInt(fFas.value, 10),
            },
            penting: Object.assign({}, pentingState),
            v: parseFloat(document.querySelector('input[name="gaya_v"]:checked')?.value || '0.5')
        };

        // Tampilkan kondisi 2: Memuat
        switchPanel('memuat');
        btnSubmit.disabled = true;

        try {
            const response = await fetch('/api/rekomendasi', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            if (response.status === 422) {
                const errData = await response.json();
                let errorHtml = '<ul class="list-disc list-inside text-left space-y-1">';
                if (errData.errors) {
                    Object.values(errData.errors).flat().forEach(msg => {
                        errorHtml += '<li>' + escapeHtml(msg) + '</li>';
                    });
                } else if (errData.message) {
                    errorHtml += '<li>' + escapeHtml(errData.message) + '</li>';
                }
                errorHtml += '</ul>';
                document.getElementById('error-pesan').innerHTML = errorHtml;
                switchPanel('error');
                return;
            }

            if (!response.ok) {
                document.getElementById('error-pesan').innerText = 'Tidak dapat menghubungi server, coba lagi.';
                switchPanel('error');
                return;
            }

            const data = await response.json();

            // Kondisi 4: Tidak ada hasil (mode: "kosong" atau urut kosong)
            if (data.mode === 'kosong' || !data.urut || data.urut.length === 0) {
                switchPanel('kosong');
                return;
            }

            // Kondisi 3: Tampilkan hasil
            renderHasil(data, payload);
            switchPanel('hasil');

        } catch (error) {
            document.getElementById('error-pesan').innerText = 'Tidak dapat menghubungi server, coba lagi.';
            switchPanel('error');
        } finally {
            btnSubmit.disabled = false;
        }
    });

    // 8. Render Hasil (Kondisi 3)
    function renderHasil(data, payload) {
        const urut = data.urut || [];
        const n = Math.min(5, urut.length);

        // Header info
        document.getElementById('hasil-judul').textContent = `Menampilkan ${n} teratas dari ${data.jumlahKandidat} destinasi`;
        document.getElementById('hasil-subjudul').textContent = `Berdasarkan keberangkatan ${data.asal}`;

        // Badge mode
        const badgeMode = document.getElementById('hasil-badge-mode');
        const modeLabels = {
            'tunggal': 'Satu pilihan terbaik',
            'setara': 'Beberapa pilihan setara',
            'dua_teratas': 'Dua pilihan teratas',
            'kosong': 'Tidak ada hasil'
        };
        badgeMode.textContent = modeLabels[data.mode] || data.mode;

        // Render Kartu-kartu
        const container = document.getElementById('hasil-cards-container');
        container.innerHTML = '';

        const topCards = urut.slice(0, n);
        topCards.forEach((item, index) => {
            const cardEl = document.createElement('article');
            cardEl.className = 'bg-surface-container-lowest rounded-2xl overflow-hidden shadow-[0_4px_20px_-2px_rgba(46,125,91,0.06)] border border-surface-variant/50 transition-all duration-300';

            const fotoSrc = item.foto ? ('/images/destinasi/' + item.foto) : '/images/placeholder.jpg';
            const rankLabel = `#${item.peringkat}`;
            const isUtama = Boolean(item.utama);

            let alasanHtml = '';
            if (item.alasan && item.alasan.length > 0) {
                alasanHtml = `
                    <div class="space-y-1.5 pt-2 border-t border-surface-variant/40">
                        ${item.alasan.map(al => `
                            <div class="flex items-start gap-2 font-body-sm text-body-sm text-on-surface">
                                <span class="material-symbols-outlined text-primary text-[16px] mt-0.5 flex-shrink-0">check_circle</span>
                                <span>${escapeHtml(al)}</span>
                            </div>
                        `).join('')}
                    </div>
                `;
            }

            const htmFormatted = item.biaya > 0 ? ('Rp ' + item.biaya.toLocaleString('id-ID')) : 'Gratis';

            cardEl.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-12">
                    <div class="relative md:col-span-5 min-h-[200px] md:min-h-full">
                        <img alt="${escapeHtml(item.nama)}" class="w-full h-full object-cover" src="${fotoSrc}" onerror="this.src='/images/placeholder.jpg'">
                        <div class="absolute top-3 left-3 px-3 py-1.5 rounded-full ${index === 0 ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-highest text-on-surface'} font-headline-sm text-headline-sm flex items-center gap-1.5 shadow-md">
                            <span class="material-symbols-outlined text-[18px]">${index === 0 ? 'workspace_premium' : 'military_tech'}</span>
                            <span>${rankLabel}</span>
                        </div>
                    </div>
                    <div class="p-5 md:p-6 md:col-span-7 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2.5 py-0.5 rounded-full bg-surface-container text-on-surface-variant font-label-sm text-label-sm">${escapeHtml(item.jenis)}</span>
                                        <span class="text-outline-variant">•</span>
                                        <span class="font-body-sm text-body-sm text-on-surface-variant">${escapeHtml(item.wilayah)}</span>
                                    </div>
                                    <h3 class="font-headline-md text-headline-md text-on-surface hover:text-primary transition-colors cursor-pointer">
                                        <a href="/destinasi/${item.id}">${escapeHtml(item.nama)}</a>
                                    </h3>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="font-display text-[26px] leading-tight font-extrabold text-primary">${Number(item.persen).toFixed(1)}%</div>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant block">Kecocokan relatif</span>
                                    <span class="text-[11px] text-outline-variant block font-mono" title="Makin kecil makin baik">Q: ${Number(item.Q).toFixed(4)}</span>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2 my-3.5">
                                <div class="px-2.5 py-1 rounded-lg bg-surface-container font-label-sm text-label-sm text-on-surface flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[15px] text-primary">distance</span>
                                    <span>${Number(item.jarak).toFixed(1)} km (garis lurus)</span>
                                </div>
                                <div class="px-2.5 py-1 rounded-lg bg-surface-container font-label-sm text-label-sm text-on-surface flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[15px] text-primary">thermostat</span>
                                    <span>${item.suhu}°C</span>
                                </div>
                                <div class="px-2.5 py-1 rounded-lg bg-surface-container font-label-sm text-label-sm text-on-surface flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[15px] text-secondary">confirmation_number</span>
                                    <span>HTM ${htmFormatted}</span>
                                </div>
                            </div>
                            ${alasanHtml}
                        </div>
                        <div class="flex items-center justify-between gap-3 pt-4 mt-3 border-t border-surface-variant/40">
                            <a href="/destinasi/${item.id}" class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-container text-on-primary font-label-lg text-label-lg transition-colors flex items-center gap-1.5 shadow-sm">
                                <span>Lihat Detail</span>
                                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                            </a>
                            ${isUtama ? `
                                <span class="font-label-sm text-label-sm text-primary font-semibold flex items-center gap-1 bg-primary/10 px-2.5 py-1 rounded-full">
                                    <span class="material-symbols-outlined text-[14px]">star</span>
                                    Rekomendasi Utama
                                </span>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(cardEl);
        });

        // 9. Render Bagian "Lihat Cara Menghitung"
        renderKalkulasiDetails(data, payload);
    }

    function renderKalkulasiDetails(data, payload) {
        // Uji Keunggulan & Kestabilan
        const dqVal = Number(data.DQ || 0).toFixed(4);
        document.getElementById('calc-dq').textContent = `Batas DQ = 1/(m-1) = ${dqVal}`;
        document.getElementById('calc-keunggulan').textContent = data.keunggulan
            ? 'Terpenuhi (Keunggulan C1 terpenuhi)'
            : 'Tidak Terpenuhi (Selisih Q < DQ)';
        document.getElementById('calc-kestabilan').textContent = data.kestabilan
            ? 'Terpenuhi (Kestabilan C2 terpenuhi)'
            : 'Tidak Terpenuhi';
        document.getElementById('calc-mode-hasil').textContent = data.mode;

        // Bobot Ternormalisasi
        const bobotContainer = document.getElementById('calc-bobot-container');
        bobotContainer.innerHTML = '';
        if (data.bobotTernormalisasi) {
            Object.entries(data.bobotTernormalisasi).forEach(([key, val]) => {
                const box = document.createElement('div');
                box.className = 'p-3 rounded-lg bg-surface-container border border-surface-variant/30 flex items-center justify-between';
                box.innerHTML = `
                    <span class="capitalize text-xs font-medium">${escapeHtml(key)}</span>
                    <span class="font-mono text-sm font-bold text-primary">${(Number(val) * 100).toFixed(1)}%</span>
                `;
                bobotContainer.appendChild(box);
            });
        }

        // Tabel Nilai Ideal & Terburuk
        const idealTbody = document.getElementById('calc-tabel-ideal');
        idealTbody.innerHTML = '';
        const urut = data.urut || [];

        const kriteriaMeta = [
            { key: 'biaya', nama: 'Biaya', tipe: 'cost', target: '-' },
            { key: 'rating', nama: 'Rating', tipe: 'benefit', target: '-' },
            { key: 'jarak', nama: 'Jarak', tipe: 'target', target: payload.target.jarak + ' km' },
            { key: 'suhu', nama: 'Suhu', tipe: 'target', target: payload.target.suhu + '°C' },
            { key: 'keramaian', nama: 'Keramaian', tipe: 'target', target: LBL_RAMAI[payload.target.keramaian] },
            { key: 'fasilitas', nama: 'Fasilitas', tipe: 'target', target: LBL_FAS[payload.target.fasilitas] },
        ];

        kriteriaMeta.forEach(k => {
            let fBest = '-';
            let fWorst = '-';

            if (urut.length > 0) {
                if (k.key === 'biaya') {
                    const vals = urut.map(u => u.biaya);
                    fBest = 'Rp ' + Math.min(...vals).toLocaleString('id-ID');
                    fWorst = 'Rp ' + Math.max(...vals).toLocaleString('id-ID');
                } else if (k.key === 'rating') {
                    const vals = urut.map(u => u.rating);
                    fBest = Math.max(...vals).toFixed(1);
                    fWorst = Math.min(...vals).toFixed(1);
                } else if (k.key === 'jarak') {
                    const diffs = urut.map(u => Math.abs(u.jarak - payload.target.jarak));
                    fBest = 'Selisih ' + Math.min(...diffs).toFixed(1) + ' km';
                    fWorst = 'Selisih ' + Math.max(...diffs).toFixed(1) + ' km';
                } else if (k.key === 'suhu') {
                    const diffs = urut.map(u => Math.abs(u.suhu - payload.target.suhu));
                    fBest = 'Selisih ' + Math.min(...diffs) + '°C';
                    fWorst = 'Selisih ' + Math.max(...diffs) + '°C';
                } else if (k.key === 'keramaian') {
                    const diffs = urut.map(u => Math.abs(u.keramaian - payload.target.keramaian));
                    fBest = 'Selisih ' + Math.min(...diffs);
                    fWorst = 'Selisih ' + Math.max(...diffs);
                } else if (k.key === 'fasilitas') {
                    const diffs = urut.map(u => Math.abs(u.fasilitas - payload.target.fasilitas));
                    fBest = 'Selisih ' + Math.min(...diffs);
                    fWorst = 'Selisih ' + Math.max(...diffs);
                }
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="p-3 font-medium text-on-surface">${escapeHtml(k.nama)}</td>
                <td class="p-3 font-mono text-xs text-on-surface-variant uppercase">${escapeHtml(k.tipe)}</td>
                <td class="p-3 text-xs text-on-surface-variant">${escapeHtml(k.target)}</td>
                <td class="p-3 font-mono text-primary font-bold">${escapeHtml(fBest)}</td>
                <td class="p-3 font-mono text-secondary">${escapeHtml(fWorst)}</td>
            `;
            idealTbody.appendChild(tr);
        });

        // Tabel Nilai S, R, Q
        const srqTbody = document.getElementById('calc-tabel-srq');
        srqTbody.innerHTML = '';
        urut.forEach(item => {
            const tr = document.createElement('tr');
            tr.className = item.utama ? 'bg-primary/5 font-medium' : '';
            tr.innerHTML = `
                <td class="p-3 text-center font-bold">#${item.peringkat}</td>
                <td class="p-3 text-on-surface">${escapeHtml(item.nama)}</td>
                <td class="p-3 font-mono">${Number(item.S).toFixed(4)}</td>
                <td class="p-3 font-mono">${Number(item.R).toFixed(4)}</td>
                <td class="p-3 font-mono font-bold text-primary">${Number(item.Q).toFixed(4)}</td>
                <td class="p-3 font-bold text-primary">${Number(item.persen).toFixed(1)}%</td>
                <td class="p-3">
                    ${item.utama ? '<span class="px-2 py-0.5 rounded bg-primary text-on-primary text-xs font-bold">Utama</span>' : '<span class="text-xs text-on-surface-variant">Alternatif</span>'}
                </td>
            `;
            srqTbody.appendChild(tr);
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
})();
</script>
@endpush
