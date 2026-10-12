@extends('layouts.app')

@php
    $populer = \App\Models\Destinasi::orderByDesc('rating')->take(4)->get();
    $totalDestinasi = \App\Models\Destinasi::count();
    $sejukCount = \App\Models\Destinasi::where('suhu', '<=', 20)->count();
    $pctSejuk = $totalDestinasi > 0 ? round(($sejukCount / $totalDestinasi) * 100) : 0;
    $terjangkauCount = \App\Models\Destinasi::where('biaya', '<=', 30000)->count();
    $pctTerjangkau = $totalDestinasi > 0 ? round(($terjangkauCount / $totalDestinasi) * 100) : 0;
    $ratingTinggiCount = \App\Models\Destinasi::where('rating', '>=', 4.5)->count();
    $pctRatingTinggi = $totalDestinasi > 0 ? round(($ratingTinggiCount / $totalDestinasi) * 100) : 0;
    $totalKabupatenKota = \App\Models\Destinasi::select('wilayah')->get()->map(function($d) {
        $parts = explode(',', $d->wilayah);
        return trim(end($parts));
    })->unique()->count();
@endphp

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
                    Sesuaikan preferensi perjalananmu dengan metode pendukung keputusan multikriteria untuk rekomendasi destinasi yang paling ideal, objektif, dan terukur.
                </p>
            </div>
        </div>
    </section>

    <!-- Two-Column Main Workbench -->
    <section class="max-w-[1280px] w-full mx-auto px-6 lg:px-12 pb-20">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            <!-- SISI KIRI - Form Preferensi (~40% desktop: 5 of 12 cols, h-full, tidak sticky) -->
            <div class="lg:col-span-5 flex flex-col h-full">
                <div id="panel-kiri-card" class="bg-surface-container-lowest rounded-2xl p-6 lg:p-7 shadow-[0_4px_24px_rgba(46,125,91,0.06)] border border-surface-variant/60 flex flex-col h-full">
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
                        <button id="btn-reset" class="text-on-surface-variant hover:text-primary p-2 rounded-lg hover:bg-surface-container transition-colors cursor-pointer" title="Reset Default" type="button">
                            <span class="material-symbols-outlined text-[20px]">refresh</span>
                        </button>
                    </div>

                    <form id="form-rekomendasi" class="flex flex-col flex-1 mt-6">
                        <div class="space-y-6 flex-1">
                            <!-- (a) Dropdown Kota Keberangkatan -->
                            <div class="space-y-2">
                                <label for="f-asal" class="block font-label-lg text-label-lg text-on-surface flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary text-[18px]">location_on</span>
                                    Berangkat dari Kota
                                </label>
                                <div class="relative">
                                    <select id="f-asal" name="asal" class="w-full appearance-none bg-surface-container-low text-on-surface font-body-md text-body-md rounded-xl px-4 py-3 pr-10 focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all cursor-pointer">
                                        @foreach($kotaList as $kota)
                                            <option value="{{ $kota->nama }}" {{ $kota->nama === 'Bandung' ? 'selected' : '' }}>
                                                {{ $kota->nama }}{{ $kota->nama === 'Bandung' ? ' (Titik Awal Terpilih)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="material-symbols-outlined absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant text-[20px]">
                                        expand_more
                                    </span>
                                </div>
                            </div>

                            <!-- (b) Pilihan Kategori / Jenis Wisata (5 nilai SPEC, default kosong = semua jenis) -->
                            <div class="space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <label class="block font-label-lg text-label-lg text-on-surface flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary text-[18px]">category</span>
                                        Jenis Wisata Impian
                                    </label>
                                    <span class="font-body-sm text-body-sm text-on-surface-variant text-xs">Tidak memilih = semua jenis</span>
                                </div>
                                <div id="chips-jenis" class="flex flex-wrap gap-2">
                                    @php
                                        $kategoriDaftar = [
                                            ['nama' => 'Pantai', 'ikon' => 'surfing'],
                                            ['nama' => 'Pegunungan', 'ikon' => 'landscape'],
                                            ['nama' => 'Air & Danau', 'ikon' => 'waterfall_chart'],
                                            ['nama' => 'Taman & Keluarga', 'ikon' => 'park'],
                                            ['nama' => 'Budaya & Sejarah', 'ikon' => 'museum'],
                                        ];
                                    @endphp
                                    @foreach($kategoriDaftar as $kat)
                                        <button type="button" data-val="{{ $kat['nama'] }}" data-ikon="{{ $kat['ikon'] }}" class="chip-jenis px-3.5 py-1.5 rounded-full text-label-md font-label-md bg-surface-container-low text-on-surface-variant hover:bg-surface-container transition-colors flex items-center gap-1.5 cursor-pointer">
                                            <span class="material-symbols-outlined text-[15px] chip-icon">{{ $kat['ikon'] }}</span>
                                            <span>{{ $kat['nama'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Kriteria Slider & Tingkat Kepentingan (Urutan desain persis) -->
                            <div class="pt-1 space-y-4">
                                <div class="flex items-center justify-between pb-1">
                                    <span class="font-label-sm text-label-sm uppercase tracking-wider text-primary font-bold">Kriteria &amp; Pembobotan (0 - 3)</span>
                                    <span class="font-body-sm text-body-sm text-on-surface-variant">0: Abaikan • 3: Sangat Penting</span>
                                </div>

                                <!-- 1. Jarak Tempuh -->
                                <div class="bg-surface-container-low/60 rounded-xl p-3.5 space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-primary text-[18px]">commute</span>
                                            <span class="font-label-md text-label-md text-on-surface">Jarak Tempuh</span>
                                        </div>
                                        <span id="o-jarak" class="font-label-sm text-label-sm text-primary font-bold bg-primary/10 px-2 py-0.5 rounded">~45 km (Dekat)</span>
                                    </div>
                                    <input id="f-jarak" class="w-full accent-primary h-1.5 bg-surface-variant rounded-lg cursor-pointer" max="150" min="10" step="5" type="range" value="45">
                                    <div class="flex items-center justify-between pt-1">
                                        <span class="font-body-sm text-body-sm text-on-surface-variant">Seberapa penting?</span>
                                        <div class="flex items-center gap-1.5" data-penting="jarak">
                                            <button type="button" data-val="0" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high cursor-pointer">0</button>
                                            <button type="button" data-val="1" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high cursor-pointer">1</button>
                                            <button type="button" data-val="2" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high cursor-pointer">2</button>
                                            <button type="button" data-val="3" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-primary text-on-primary font-bold shadow-xs cursor-pointer">3</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Suhu & Kesejukan Udara -->
                                <div class="bg-surface-container-low/60 rounded-xl p-3.5 space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-primary text-[18px]">device_thermostat</span>
                                            <span class="font-label-md text-label-md text-on-surface">Suhu &amp; Kesejukan Udara</span>
                                        </div>
                                        <span id="o-suhu" class="font-label-sm text-label-sm text-primary font-bold bg-primary/10 px-2 py-0.5 rounded">Sejuk 18°C</span>
                                    </div>
                                    <input id="f-suhu" class="w-full accent-primary h-1.5 bg-surface-variant rounded-lg cursor-pointer" max="32" min="15" step="1" type="range" value="18">
                                    <div class="flex items-center justify-between pt-1">
                                        <span class="font-body-sm text-body-sm text-on-surface-variant">Seberapa penting?</span>
                                        <div class="flex items-center gap-1.5" data-penting="suhu">
                                            <button type="button" data-val="0" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high cursor-pointer">0</button>
                                            <button type="button" data-val="1" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high cursor-pointer">1</button>
                                            <button type="button" data-val="2" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-primary text-on-primary font-bold shadow-xs cursor-pointer">2</button>
                                            <button type="button" data-val="3" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high cursor-pointer">3</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Tingkat Keramaian -->
                                <div class="bg-surface-container-low/60 rounded-xl p-3.5 space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-primary text-[18px]">groups</span>
                                            <span class="font-label-md text-label-md text-on-surface">Tingkat Keramaian</span>
                                        </div>
                                        <span id="o-ramai" class="font-label-sm text-label-sm text-primary font-bold bg-primary/10 px-2 py-0.5 rounded">Cenderung Sepi</span>
                                    </div>
                                    <input id="f-ramai" class="w-full accent-primary h-1.5 bg-surface-variant rounded-lg cursor-pointer" max="100" min="0" step="5" type="range" value="30">
                                    <div class="flex items-center justify-between pt-1">
                                        <span class="font-body-sm text-body-sm text-on-surface-variant">Seberapa penting?</span>
                                        <div class="flex items-center gap-1.5" data-penting="keramaian">
                                            <button type="button" data-val="0" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high cursor-pointer">0</button>
                                            <button type="button" data-val="1" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high cursor-pointer">1</button>
                                            <button type="button" data-val="2" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-primary text-on-primary font-bold shadow-xs cursor-pointer">2</button>
                                            <button type="button" data-val="3" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high cursor-pointer">3</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- 4. Kelengkapan Fasilitas -->
                                <div class="bg-surface-container-low/60 rounded-xl p-3.5 space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-primary text-[18px]">park</span>
                                            <span class="font-label-md text-label-md text-on-surface">Kelengkapan Fasilitas</span>
                                        </div>
                                        <span id="o-fas" class="font-label-sm text-label-sm text-primary font-bold bg-primary/10 px-2 py-0.5 rounded">Lengkap (Toilet, Shuttle, Parkir)</span>
                                    </div>
                                    <input id="f-fas" class="w-full accent-primary h-1.5 bg-surface-variant rounded-lg cursor-pointer" max="100" min="0" step="5" type="range" value="80">
                                    <div class="flex items-center justify-between pt-1">
                                        <span class="font-body-sm text-body-sm text-on-surface-variant">Seberapa penting?</span>
                                        <div class="flex items-center gap-1.5" data-penting="fasilitas">
                                            <button type="button" data-val="0" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high cursor-pointer">0</button>
                                            <button type="button" data-val="1" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high cursor-pointer">1</button>
                                            <button type="button" data-val="2" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high cursor-pointer">2</button>
                                            <button type="button" data-val="3" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-primary text-on-primary font-bold shadow-xs cursor-pointer">3</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- 5. Anggaran Tiket (per Orang) -->
                                <div class="bg-surface-container-low/60 rounded-xl p-3.5 space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-primary text-[18px]">payments</span>
                                            <span class="font-label-md text-label-md text-on-surface">Anggaran Tiket (per Orang)</span>
                                        </div>
                                        <span id="o-biaya" class="font-label-sm text-label-sm text-primary font-bold bg-primary/10 px-2 py-0.5 rounded">Rp 50.000</span>
                                    </div>
                                    <input id="f-biaya" class="w-full accent-primary h-1.5 bg-surface-variant rounded-lg cursor-pointer" max="150000" min="20000" step="5000" type="range" value="50000">
                                    <div class="flex items-center justify-between pt-1">
                                        <span class="font-body-sm text-body-sm text-on-surface-variant">Seberapa penting?</span>
                                        <div class="flex items-center gap-1.5" data-penting="biaya">
                                            <button type="button" data-val="0" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high cursor-pointer">0</button>
                                            <button type="button" data-val="1" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-primary text-on-primary font-bold shadow-xs cursor-pointer">1</button>
                                            <button type="button" data-val="2" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high cursor-pointer">2</button>
                                            <button type="button" data-val="3" class="btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high cursor-pointer">3</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- (i) Tiga Radio Cards Gaya Keputusan (v) -->
                            <div class="space-y-2.5 pt-1">
                                <label class="block font-label-lg text-label-lg text-on-surface">Gaya Keputusan (Bobot Strategi v)</label>
                                <div class="flex flex-col gap-2">
                                    <!-- Option 1: Seimbang (v = 0.5) -->
                                    <div data-v="0.5" class="gaya-card relative p-3.5 rounded-xl bg-primary/5 shadow-xs cursor-pointer flex items-start gap-3 transition-colors">
                                        <div class="mt-0.5 w-4 h-4 rounded-full bg-primary flex items-center justify-center radio-circle">
                                            <div class="w-1.5 h-1.5 rounded-full bg-surface-container-lowest"></div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <span class="font-title text-title text-primary text-[15px] gaya-title">Seimbang (Konsensus)</span>
                                                <span class="font-label-sm text-label-sm font-mono text-primary bg-primary-fixed/50 px-2 py-0.5 rounded gaya-badge">v = 0.5</span>
                                            </div>
                                            <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">Metode kompromi proporsional antara mayoritas kriteria &amp; mitigasi penyesalan.</p>
                                        </div>
                                    </div>

                                    <!-- Option 2: Utamakan Mayoritas (v = 0.8) -->
                                    <div data-v="0.8" class="gaya-card relative p-3.5 rounded-xl bg-surface-container-low hover:bg-surface-container cursor-pointer flex items-start gap-3 transition-colors">
                                        <div class="mt-0.5 w-4 h-4 rounded-full bg-surface-variant flex items-center justify-center radio-circle"></div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <span class="font-title text-title text-on-surface text-[15px] gaya-title">Utamakan Mayoritas</span>
                                                <span class="font-label-sm text-label-sm font-mono text-on-surface-variant bg-surface-container px-2 py-0.5 rounded gaya-badge">v = 0.8</span>
                                            </div>
                                            <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">Memaksimalkan kepuasan kriteria umum grup terbanyak.</p>
                                        </div>
                                    </div>

                                    <!-- Option 3: Hindari Kekurangan (v = 0.2) -->
                                    <div data-v="0.2" class="gaya-card relative p-3.5 rounded-xl bg-surface-container-low hover:bg-surface-container cursor-pointer flex items-start gap-3 transition-colors">
                                        <div class="mt-0.5 w-4 h-4 rounded-full bg-surface-variant flex items-center justify-center radio-circle"></div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <span class="font-title text-title text-on-surface text-[15px] gaya-title">Hindari Kekurangan</span>
                                                <span class="font-label-sm text-label-sm font-mono text-on-surface-variant bg-surface-container px-2 py-0.5 rounded gaya-badge">v = 0.2</span>
                                            </div>
                                            <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">Meminimalkan risiko pada kriteria paling buruk (anti-penyesalan).</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- (j) Tombol Utama & Footer Kiri (mt-auto) -->
                        <div class="mt-auto pt-6 space-y-2">
                            <button id="btn-submit" class="w-full py-4 px-6 rounded-xl bg-secondary-container hover:bg-secondary text-on-secondary-container hover:text-on-secondary font-headline-sm text-headline-sm transition-all duration-200 flex items-center justify-center gap-3 shadow-[0_8px_20px_rgba(242,155,56,0.25)] active:scale-[0.99] cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed" type="submit">
                                <span class="material-symbols-outlined text-[24px]">explore</span>
                                <span id="btn-submit-text">Tampilkan Rekomendasi</span>
                            </button>
                            <p class="font-body-sm text-body-sm text-center text-on-surface-variant">Menghitung {{ $totalAktif }} titik wisata aktif di Jawa Barat</p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SISI KANAN - Area Hasil Rekomendasi (~60% desktop: 7 of 12 cols, h-full) -->
            <div id="panel-kanan-kolom" class="lg:col-span-7 flex flex-col h-full">
                
                <!-- ================= KONDISI 1: AWAL (Persis desain rekomendasi.html) ================= -->
                <div id="panel-awal" class="bg-surface-container-lowest rounded-2xl border border-surface-variant/60 shadow-[0_4px_24px_rgba(46,125,91,0.06)] p-6 flex flex-col gap-4 h-full">
                    <!-- A. Header Rekomendasi -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-surface-variant/50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary-container/15 text-primary flex items-center justify-center flex-shrink-0 shadow-xs ring-4 ring-primary/5">
                                <span class="material-symbols-outlined text-[22px]">explore</span>
                            </div>
                            <div>
                                <h2 class="font-headline-sm text-headline-sm text-on-surface">Rekomendasi Wisata untukmu</h2>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">Destinasi terbaik berdasarkan preferensi dan bobot penilaianmu.</p>
                            </div>
                        </div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary font-label-sm text-label-sm font-semibold border border-primary/20 w-auto self-start sm:self-center">
                            <span class="material-symbols-outlined text-[16px]">verified</span>
                            <span>{{ $totalAktif }} destinasi dianalisis</span>
                        </div>
                    </div>

                    <!-- B. Ringkasan Preferensi Aktif -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-label-sm text-label-sm uppercase tracking-wider text-primary font-bold flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">tune</span>
                                Ringkasan Preferensi Aktif
                            </span>
                            <span id="ringkas-asal-header" class="font-body-sm text-body-sm text-on-surface-variant">Titik Asal: Bandung</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            <div class="p-2.5 rounded-xl bg-surface-container-low/70 border border-surface-variant/40 flex items-center gap-2.5">
                                <span class="material-symbols-outlined text-primary text-[18px] flex-shrink-0">location_on</span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-label-sm text-label-sm text-on-surface-variant">Keberangkatan</p>
                                    <p id="ringkas-asal" class="font-label-md text-label-md text-on-surface font-semibold truncate">Bandung</p>
                                </div>
                            </div>
                            <div class="p-2.5 rounded-xl bg-surface-container-low/70 border border-surface-variant/40 flex items-center gap-2.5">
                                <span class="material-symbols-outlined text-primary text-[18px] flex-shrink-0">landscape</span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-label-sm text-label-sm text-on-surface-variant">Jenis Wisata</p>
                                    <p id="ringkas-jenis" class="font-label-md text-label-md text-on-surface font-semibold truncate">Semua Jenis</p>
                                </div>
                            </div>
                            <div class="p-2.5 rounded-xl bg-surface-container-low/70 border border-surface-variant/40 flex items-center gap-2.5">
                                <span class="material-symbols-outlined text-primary text-[18px] flex-shrink-0">commute</span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-label-sm text-label-sm text-on-surface-variant">Jarak Diinginkan</p>
                                    <p id="ringkas-jarak" class="font-label-md text-label-md text-on-surface font-semibold truncate">~45 km (Dekat)</p>
                                </div>
                            </div>
                            <div class="p-2.5 rounded-xl bg-surface-container-low/70 border border-surface-variant/40 flex items-center gap-2.5">
                                <span class="material-symbols-outlined text-primary text-[18px] flex-shrink-0">nature_people</span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-label-sm text-label-sm text-on-surface-variant">Suasana</p>
                                    <p id="ringkas-suasana" class="font-label-md text-label-md text-on-surface font-semibold truncate">Cenderung Sepi</p>
                                </div>
                            </div>
                            <div class="p-2.5 rounded-xl bg-surface-container-low/70 border border-surface-variant/40 flex items-center gap-2.5 sm:col-span-2 lg:col-span-2">
                                <span class="material-symbols-outlined text-primary text-[18px] flex-shrink-0">payments</span>
                                <div class="min-w-0 flex-1 flex items-center justify-between">
                                    <div>
                                        <p class="font-label-sm text-label-sm text-on-surface-variant">Anggaran Tiket Masuk</p>
                                        <p id="ringkas-anggaran" class="font-label-md text-label-md text-on-surface font-semibold">Rp 50.000 / orang</p>
                                    </div>
                                    <span id="ringkas-anggaran-badge" class="font-label-sm text-label-sm text-primary bg-primary/10 px-2 py-0.5 rounded font-bold">Ekonomis</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- C. Visualisasi Bobot Kriteria ("Prioritas Penilaian") -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-label-sm text-label-sm uppercase tracking-wider text-primary font-bold flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px]">bar_chart</span>
                                    Prioritas Penilaian
                                </h3>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">Nilai pembobotan parameter saat ini</p>
                            </div>
                            <span class="font-label-sm text-label-sm font-mono text-primary bg-primary-fixed/50 px-2 py-0.5 rounded font-semibold">Skala 100%</span>
                        </div>
                        <div class="space-y-2 bg-surface-container-low/60 p-3 rounded-xl border border-surface-variant/40">
                            <!-- 1. Jarak Tempuh -->
                            <div class="space-y-1">
                                <div class="flex justify-between font-label-sm text-label-sm text-on-surface">
                                    <span class="flex items-center gap-1.5 font-medium"><span class="material-symbols-outlined text-primary text-[15px]">commute</span>Jarak Tempuh</span>
                                    <span id="bar-text-jarak" class="text-primary font-bold">27% <span class="text-on-surface-variant font-normal">(Sangat Tinggi)</span></span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-surface-variant overflow-hidden">
                                    <div id="bar-fill-jarak" class="h-full bg-primary rounded-full transition-all duration-300" style="width: 27%;"></div>
                                </div>
                            </div>
                            <!-- 2. Suhu & Kesejukan Udara -->
                            <div class="space-y-1">
                                <div class="flex justify-between font-label-sm text-label-sm text-on-surface">
                                    <span class="flex items-center gap-1.5 font-medium"><span class="material-symbols-outlined text-primary text-[15px]">device_thermostat</span>Suhu &amp; Kesejukan Udara</span>
                                    <span id="bar-text-suhu" class="text-primary font-bold">18% <span class="text-on-surface-variant font-normal">(Tinggi)</span></span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-surface-variant overflow-hidden">
                                    <div id="bar-fill-suhu" class="h-full bg-primary rounded-full transition-all duration-300" style="width: 18%;"></div>
                                </div>
                            </div>
                            <!-- 3. Tingkat Keramaian -->
                            <div class="space-y-1">
                                <div class="flex justify-between font-label-sm text-label-sm text-on-surface">
                                    <span class="flex items-center gap-1.5 font-medium"><span class="material-symbols-outlined text-primary text-[15px]">groups</span>Tingkat Keramaian</span>
                                    <span id="bar-text-keramaian" class="text-primary font-bold">18% <span class="text-on-surface-variant font-normal">(Tinggi)</span></span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-surface-variant overflow-hidden">
                                    <div id="bar-fill-keramaian" class="h-full bg-primary rounded-full transition-all duration-300" style="width: 18%;"></div>
                                </div>
                            </div>
                            <!-- 4. Kelengkapan Fasilitas -->
                            <div class="space-y-1">
                                <div class="flex justify-between font-label-sm text-label-sm text-on-surface">
                                    <span class="flex items-center gap-1.5 font-medium"><span class="material-symbols-outlined text-primary text-[15px]">park</span>Kelengkapan Fasilitas</span>
                                    <span id="bar-text-fasilitas" class="text-primary font-bold">27% <span class="text-on-surface-variant font-normal">(Sangat Tinggi)</span></span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-surface-variant overflow-hidden">
                                    <div id="bar-fill-fasilitas" class="h-full bg-primary rounded-full transition-all duration-300" style="width: 27%;"></div>
                                </div>
                            </div>
                            <!-- 5. Anggaran Tiket -->
                            <div class="space-y-1">
                                <div class="flex justify-between font-label-sm text-label-sm text-on-surface">
                                    <span class="flex items-center gap-1.5 font-medium"><span class="material-symbols-outlined text-primary text-[15px]">payments</span>Anggaran Tiket</span>
                                    <span id="bar-text-biaya" class="text-primary font-bold">9% <span class="text-on-surface-variant font-normal">(Sedang)</span></span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-surface-variant overflow-hidden">
                                    <div id="bar-fill-biaya" class="h-full bg-primary rounded-full transition-all duration-300" style="width: 9%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- D. Destinasi Populer Siap Dikalkulasi (4 kartu dari database) -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface font-bold flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-secondary text-[16px]">auto_awesome</span>
                                Destinasi Populer Siap Dikalkulasi
                            </span>
                            <span class="font-label-sm text-label-sm text-primary font-bold bg-primary/10 px-2 py-0.5 rounded">{{ $totalAktif }} Titik Aktif</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            @foreach($populer as $item)
                                @php
                                    $parts = explode(',', $item->wilayah);
                                    $kabKota = trim(end($parts));
                                @endphp
                                <a href="{{ url('/destinasi/' . $item->id) }}" class="p-2.5 rounded-xl bg-surface-container-low/70 border border-surface-variant/40 flex gap-2.5 hover:bg-surface-container-low transition-colors group cursor-pointer">
                                    <img alt="{{ $item->nama }}" class="w-16 h-14 rounded-lg object-cover flex-shrink-0" src="{{ asset('images/destinasi/' . $item->foto) }}" onerror="this.src='{{ asset('images/placeholder.jpg') }}'">
                                    <div class="min-w-0 flex-1 flex flex-col justify-between">
                                        <div>
                                            <div class="flex items-center justify-between gap-1">
                                                <h4 class="font-label-md text-label-md font-semibold text-on-surface truncate group-hover:text-primary transition-colors">{{ $item->nama }}</h4>
                                                <span class="font-label-sm text-label-sm bg-primary/10 text-primary font-semibold px-1.5 py-0.2 rounded flex-shrink-0">{{ $item->jenis }}</span>
                                            </div>
                                            <p class="font-body-sm text-body-sm text-on-surface-variant truncate mt-0.5">{{ $kabKota }} • {{ $item->suhu }}°C</p>
                                        </div>
                                        <p class="font-body-sm text-body-sm text-primary font-semibold">
                                            {{ $item->biaya > 0 ? 'Mulai Rp ' . number_format($item->biaya, 0, ',', '.') : 'Gratis' }}
                                        </p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- E. Panduan Metode VIKOR ("Bagaimana Rekomendasi Dihitung?") -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-label-sm text-label-sm uppercase tracking-wider text-primary font-bold flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">psychology</span>
                                Bagaimana Rekomendasi Dihitung?
                            </span>
                            <span class="font-label-sm text-label-sm text-on-surface-variant font-mono">Multi-Criteria VIKOR</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                            <div class="p-2.5 rounded-xl bg-surface-container-low/60 border border-surface-variant/40 flex flex-col justify-between">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-5 h-5 rounded-full bg-primary-container text-on-primary font-bold text-[11px] flex items-center justify-center flex-shrink-0">1</div>
                                    <span class="font-label-sm text-label-sm font-semibold text-on-surface">Prioritas Kriteria</span>
                                </div>
                                <p class="font-body-sm text-body-sm text-on-surface-variant leading-snug">Penilaian mengikuti pembobotan preferensi Anda.</p>
                            </div>
                            <div class="p-2.5 rounded-xl bg-surface-container-low/60 border border-surface-variant/40 flex flex-col justify-between">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-5 h-5 rounded-full bg-primary-container text-on-primary font-bold text-[11px] flex items-center justify-center flex-shrink-0">2</div>
                                    <span class="font-label-sm text-label-sm font-semibold text-on-surface">Perbandingan Alternatif</span>
                                </div>
                                <p class="font-body-sm text-body-sm text-on-surface-variant leading-snug">Destinasi dibandingkan menyeluruh berdasarkan matriks kriteria.</p>
                            </div>
                            <div class="p-2.5 rounded-xl bg-surface-container-low/60 border border-surface-variant/40 flex flex-col justify-between">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-5 h-5 rounded-full bg-primary-container text-on-primary font-bold text-[11px] flex items-center justify-center flex-shrink-0">3</div>
                                    <span class="font-label-sm text-label-sm font-semibold text-on-surface">Peringkat Optimal</span>
                                </div>
                                <p class="font-body-sm text-body-sm text-on-surface-variant leading-snug">Hasil disusun berdasarkan nilai indeks kompromi terbaik (Q-score).</p>
                            </div>
                        </div>
                    </div>

                    <!-- F. Status & Banner Transparansi -->
                    <div class="space-y-2.5 pt-1 border-t border-surface-variant/50">
                        <!-- Metrik Singkat -->
                        <div class="flex flex-wrap items-center justify-between gap-2 p-2 rounded-xl bg-surface-container-low/80 border border-surface-variant/40 font-label-sm text-label-sm text-on-surface">
                            <span class="inline-flex items-center gap-1"><span class="text-on-surface-variant">Metode:</span> <strong class="text-primary">VIKOR</strong></span>
                            <span class="text-outline-variant">•</span>
                            <span class="inline-flex items-center gap-1"><span class="text-on-surface-variant">Kriteria:</span> <strong class="text-primary">5 Parameter</strong></span>
                            <span class="text-outline-variant">•</span>
                            <span class="inline-flex items-center gap-1"><span class="text-on-surface-variant">Destinasi:</span> <strong class="text-primary">{{ $totalAktif }} Lokasi</strong></span>
                            <span class="text-outline-variant">•</span>
                            <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-primary text-[15px]">verified_user</span> <span class="text-primary font-semibold">Objektif &amp; Transparan</span></span>
                        </div>
                        <!-- Jaminan Banner -->
                        <div class="p-3 rounded-xl bg-primary/5 border border-primary/20 flex items-start gap-2.5 text-left">
                            <div class="w-6 h-6 rounded-lg bg-primary text-on-primary flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="material-symbols-outlined text-[16px]">verified</span>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-label-md text-label-md text-primary font-bold">Jaminan Rekomendasi Objektif &amp; Bebas Bias</h4>
                                <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5 leading-relaxed">Seluruh pemeringkatan dihitung murni berbasis kompromi multikriteria VIKOR dari preferensi Anda, tanpa intervensi promosi berbayar.</p>
                            </div>
                        </div>
                        <!-- Metadata Footer Baris -->
                        <div class="flex flex-wrap items-center justify-center gap-3 font-body-sm text-body-sm text-on-surface-variant text-center pt-1">
                            <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-primary text-[15px]">check_circle</span>{{ $totalDestinasi }} Destinasi</span>
                            <span>•</span>
                            <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-primary text-[15px]">location_city</span>{{ $totalKabupatenKota }} Wilayah Kabupaten/Kota</span>
                        </div>
                    </div>

                    <!-- 1. Section: Gambaran Data Destinasi (Data Nyata dari Database) -->
                    <div class="space-y-2 pt-2 border-t border-surface-variant/50">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-label-md text-label-md font-semibold text-on-surface flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-primary text-[17px]">analytics</span>
                                    Gambaran Data Destinasi
                                </h3>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">Komposisi {{ $totalDestinasi }} destinasi di database</p>
                            </div>
                            <span class="font-label-sm text-label-sm font-mono text-primary bg-primary/10 px-2 py-0.5 rounded font-bold">Data Nyata</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2.5">
                            <!-- Kartu 1: Destinasi Sejuk -->
                            <div class="p-3 rounded-xl bg-surface-container-low/70 border border-surface-variant/40 flex flex-col justify-between space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-primary-container/20 text-primary flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[16px]">eco</span>
                                        </div>
                                        <span class="font-label-md text-label-md font-semibold text-on-surface">Destinasi Sejuk</span>
                                    </div>
                                    <span class="font-label-sm text-label-sm font-bold text-primary">{{ $pctSejuk }}%</span>
                                </div>
                                <div class="w-full h-1.5 rounded-full bg-surface-variant overflow-hidden">
                                    <div class="h-full bg-primary rounded-full" style="width: {{ $pctSejuk }}%;"></div>
                                </div>
                                <p class="font-body-sm text-body-sm text-on-surface-variant leading-snug">Destinasi dengan suhu sejuk hingga dingin (≤ 20°C).</p>
                            </div>
                            <!-- Kartu 2: Tiket Terjangkau -->
                            <div class="p-3 rounded-xl bg-surface-container-low/70 border border-surface-variant/40 flex flex-col justify-between space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-primary-container/20 text-primary flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[16px]">wallet</span>
                                        </div>
                                        <span class="font-label-md text-label-md font-semibold text-on-surface">Tiket Terjangkau</span>
                                    </div>
                                    <span class="font-label-sm text-label-sm font-bold text-primary">{{ $pctTerjangkau }}%</span>
                                </div>
                                <div class="w-full h-1.5 rounded-full bg-surface-variant overflow-hidden">
                                    <div class="h-full bg-primary rounded-full" style="width: {{ $pctTerjangkau }}%;"></div>
                                </div>
                                <p class="font-body-sm text-body-sm text-on-surface-variant leading-snug">Destinasi dengan tiket masuk hemat (≤ Rp 30.000).</p>
                            </div>
                            <!-- Kartu 3: Rating Tinggi -->
                            <div class="p-3 rounded-xl bg-surface-container-low/70 border border-surface-variant/40 flex flex-col justify-between space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-primary-container/20 text-primary flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[16px]">near_me</span>
                                        </div>
                                        <span class="font-label-md text-label-md font-semibold text-on-surface">Rating Tinggi</span>
                                    </div>
                                    <span class="font-label-sm text-label-sm font-bold text-primary">{{ $pctRatingTinggi }}%</span>
                                </div>
                                <div class="w-full h-1.5 rounded-full bg-surface-variant overflow-hidden">
                                    <div class="h-full bg-primary rounded-full" style="width: {{ $pctRatingTinggi }}%;"></div>
                                </div>
                                <p class="font-body-sm text-body-sm text-on-surface-variant leading-snug">Destinasi dengan ulasan kepuasan tinggi (≥ 4.5).</p>
                            </div>
                        </div>
                    </div>

                    <!-- G. Insight Perjalanan -->
                    <div class="space-y-2 pt-2 border-t border-surface-variant/50">
                        <div class="flex items-center justify-between">
                            <h3 class="font-label-md text-label-md font-semibold text-on-surface flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-secondary text-[17px]">lightbulb</span>
                                Insight Perjalanan
                            </h3>
                            <span class="font-body-sm text-body-sm text-on-surface-variant">Panduan cerdas untuk Anda</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div class="p-4 rounded-xl bg-surface-container-low/60 border border-surface-variant/40 flex flex-col justify-between hover:bg-surface-container-low transition-colors">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-primary-container/20 text-primary flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <span class="material-symbols-outlined text-[18px]">explore</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="font-label-md text-label-md font-semibold text-on-surface">Pilihan Paling Sesuai</h4>
                                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-1 leading-relaxed">Prioritaskan destinasi yang memenuhi bobot kriteria perjalananmu dengan keseimbangan terbaik antara jarak, suasana, fasilitas, dan biaya.</p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2 mt-3 pl-11">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-primary/10 text-primary border border-primary/20">
                                        <span class="material-symbols-outlined text-[13px]">near_me</span>Jarak
                                    </span>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-primary/10 text-primary border border-primary/20">
                                        <span class="material-symbols-outlined text-[13px]">spa</span>Kenyamanan
                                    </span>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-primary/10 text-primary border border-primary/20">
                                        <span class="material-symbols-outlined text-[13px]">payments</span>Anggaran
                                    </span>
                                </div>
                            </div>
                            <div class="p-4 rounded-xl bg-surface-container-low/60 border border-surface-variant/40 flex flex-col justify-between hover:bg-surface-container-low transition-colors">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-secondary/15 text-secondary flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="font-label-md text-label-md font-semibold text-on-surface">Persiapkan Perjalanan</h4>
                                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-1 leading-relaxed">Periksa kondisi cuaca, jam operasional, biaya terbaru, dan akses menuju lokasi sebelum berangkat.</p>
                                    </div>
                                </div>
                                <div class="space-y-1.5 mt-3 pl-11">
                                    <div class="flex items-center gap-1.5 text-body-sm text-on-surface-variant font-medium">
                                        <span class="material-symbols-outlined text-primary text-[15px]">check_circle</span>
                                        <span>Periksa prakiraan cuaca.</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-body-sm text-on-surface-variant font-medium">
                                        <span class="material-symbols-outlined text-primary text-[15px]">check_circle</span>
                                        <span>Pastikan jam operasional.</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-body-sm text-on-surface-variant font-medium">
                                        <span class="material-symbols-outlined text-primary text-[15px]">check_circle</span>
                                        <span>Siapkan anggaran perjalanan.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= KONDISI 2: MEMUAT (Skeleton Loading) ================= -->
                <div id="panel-memuat" class="hidden space-y-6">
                    <div class="bg-surface-container-lowest rounded-2xl p-5 shadow-sm flex items-center gap-3">
                        <div class="w-5 h-5 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                        <span class="font-headline-sm text-headline-sm text-primary">Menghitung Rekomendasi VIKOR...</span>
                    </div>

                    <div class="space-y-5">
                        @for($i = 0; $i < 3; $i++)
                            <div class="bg-surface-container-lowest rounded-2xl p-6 border border-surface-variant/40 shadow-sm animate-pulse flex flex-col md:flex-row gap-5">
                                <div class="w-full md:w-5/12 h-48 bg-surface-variant/70 rounded-xl flex-shrink-0"></div>
                                <div class="flex-1 space-y-3 py-2">
                                    <div class="h-4 bg-surface-variant/70 rounded w-1/3"></div>
                                    <div class="h-6 bg-surface-variant/70 rounded w-2/3"></div>
                                    <div class="h-4 bg-surface-variant/70 rounded w-1/2"></div>
                                    <div class="h-4 bg-surface-variant/50 rounded w-full"></div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>

                <!-- ================= KONDISI 3: HASIL REKOMENDASI (Persis hasil.html) ================= -->
                <div id="panel-hasil" class="hidden space-y-6 flex-col">
                    <!-- Header Hasil & Quick Filter Info (Persis hasil.html baris 273) -->
                    <div class="bg-surface-container-lowest rounded-2xl p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-primary-container"></span>
                                <h2 class="font-headline-md text-headline-md text-on-surface">
                                    <span id="hasil-jumlah-top">5</span> Destinasi Terbaik Ditemukan
                                </h2>
                            </div>
                            <p id="hasil-subteks" class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                                Menampilkan 5 teratas dari 12 destinasi • Berdasarkan keberangkatan Bandung
                            </p>
                        </div>
                        <div class="flex items-center gap-2 self-start sm:self-auto">
                            <span id="hasil-badge-mode" class="px-3 py-1 rounded-full bg-primary-fixed text-on-primary-fixed font-label-sm text-label-sm flex items-center gap-1.5 font-semibold">
                                <span class="material-symbols-outlined text-[14px]">verified</span>
                                <span>Satu pilihan terbaik</span>
                            </span>
                            <button class="p-2 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface transition-colors cursor-pointer" title="Bagikan Hasil" type="button">
                                <span class="material-symbols-outlined text-[18px]">share</span>
                            </button>
                        </div>
                    </div>

                    <!-- Daftar Kartu Peringkat (Persis hasil.html baris 292, spasi 20px) -->
                    <div id="hasil-cards-container" class="space-y-5">
                        <!-- Kartu hasil dirender secara dinamis oleh JavaScript -->
                    </div>

                    <!-- 3. Bagian Edukatif: "Lihat Cara Menghitung & Matriks Keputusan VIKOR" (hasil.html baris 541) -->
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
                <div id="panel-kosong" class="hidden bg-surface-container-lowest rounded-2xl border border-surface-variant/60 shadow-sm flex-col items-center justify-center text-center p-8 lg:p-12">
                    <div class="w-16 h-16 rounded-full bg-secondary/10 text-secondary flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-3xl">search_off</span>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface mb-2">
                        Tidak ada destinasi yang cocok
                    </h3>
                    <p class="font-body-md text-body-md text-on-surface-variant max-w-md mb-6 leading-relaxed">
                        Saran: naikkan anggaran maksimal atau tambah jenis wisata agar destinasi yang sesuai dapat ditemukan.
                    </p>
                    <button type="button" id="btn-reset-kosong" class="px-6 py-2.5 rounded-full bg-primary-container text-on-primary font-label-lg text-label-lg shadow-sm hover:opacity-95 transition-all cursor-pointer">
                        Reset Anggaran &amp; Cari Ulang
                    </button>
                </div>

                <!-- ================= KONDISI 5: ERROR ================= -->
                <div id="panel-error" class="hidden bg-surface-container-lowest rounded-2xl border border-error/40 shadow-sm flex-col items-center justify-center text-center p-8">
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

    <!-- Section Interaktif Mini: Eksplorasi Wilayah Parahyangan (rekomendasi.html baris 424) -->
    <section class="w-full bg-surface-container-low py-12">
        <div class="max-w-[1280px] mx-auto px-6 lg:px-12">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <span class="text-tertiary font-label-sm text-label-sm tracking-wider uppercase font-bold">Inspirasi Perjalanan Lainnya</span>
                    <h2 class="font-headline-md text-headline-md text-on-surface mt-1">Eksplorasi Wilayah Parahyangan</h2>
                </div>
                <a class="inline-flex items-center gap-1.5 text-primary font-label-lg text-label-lg hover:underline" href="{{ url('/katalog') }}">
                    <span>Jelajahi Peta Wisata Terintegrasi</span>
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Mini Card 1 -->
                <div class="bg-surface-container-lowest rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined">foggy</span>
                        </div>
                        <div>
                            <h4 class="font-title text-title text-[15px] text-on-surface">Ciwidey &amp; Pangalengan</h4>
                            <p class="font-body-sm text-body-sm text-on-surface-variant">Wisata Alam &amp; Kebun Teh</p>
                        </div>
                    </div>
                </div>
                <!-- Mini Card 2 -->
                <div class="bg-surface-container-lowest rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-secondary/10 flex items-center justify-center text-secondary">
                            <span class="material-symbols-outlined">hiking</span>
                        </div>
                        <div>
                            <h4 class="font-title text-title text-[15px] text-on-surface">Lembang &amp; Tangkuban</h4>
                            <p class="font-body-sm text-body-sm text-on-surface-variant">Objek Wisata Keluarga</p>
                        </div>
                    </div>
                </div>
                <!-- Mini Card 3 -->
                <div class="bg-surface-container-lowest rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-tertiary/10 flex items-center justify-center text-tertiary">
                            <span class="material-symbols-outlined">water_drop</span>
                        </div>
                        <div>
                            <h4 class="font-title text-title text-[15px] text-on-surface">Geopark Ciletuh</h4>
                            <p class="font-body-sm text-body-sm text-on-surface-variant">Air Terjun &amp; Pesisir Alami</p>
                        </div>
                    </div>
                </div>
                <!-- Mini Card 4 -->
                <div class="bg-surface-container-lowest rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-primary-container/15 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined">landscape</span>
                        </div>
                        <div>
                            <h4 class="font-title text-title text-[15px] text-on-surface">Majalengka &amp; Kuningan</h4>
                            <p class="font-body-sm text-body-sm text-on-surface-variant">Agrowisata &amp; Telaga</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function() {
    // 1. DOM Elements
    const form = document.getElementById('form-rekomendasi');
    const fAsal = document.getElementById('f-asal');
    const fJarak = document.getElementById('f-jarak');
    const oJarak = document.getElementById('o-jarak');
    const fSuhu = document.getElementById('f-suhu');
    const oSuhu = document.getElementById('o-suhu');
    const fRamai = document.getElementById('f-ramai');
    const oRamai = document.getElementById('o-ramai');
    const fFas = document.getElementById('f-fas');
    const oFas = document.getElementById('o-fas');
    const fBiaya = document.getElementById('f-biaya');
    const oBiaya = document.getElementById('o-biaya');
    const btnSubmit = document.getElementById('btn-submit');
    const btnReset = document.getElementById('btn-reset');
    const btnResetKosong = document.getElementById('btn-reset-kosong');

    // Ringkasan DOM Elements (Panel Kanan Awal)
    const ringkasAsalHeader = document.getElementById('ringkas-asal-header');
    const ringkasAsal = document.getElementById('ringkas-asal');
    const ringkasJenis = document.getElementById('ringkas-jenis');
    const ringkasJarak = document.getElementById('ringkas-jarak');
    const ringkasSuasana = document.getElementById('ringkas-suasana');
    const ringkasAnggaran = document.getElementById('ringkas-anggaran');
    const ringkasAnggaranBadge = document.getElementById('ringkas-anggaran-badge');

    // Bar DOM Elements
    const barTextJarak = document.getElementById('bar-text-jarak');
    const barFillJarak = document.getElementById('bar-fill-jarak');
    const barTextSuhu = document.getElementById('bar-text-suhu');
    const barFillSuhu = document.getElementById('bar-fill-suhu');
    const barTextKeramaian = document.getElementById('bar-text-keramaian');
    const barFillKeramaian = document.getElementById('bar-fill-keramaian');
    const barTextFasilitas = document.getElementById('bar-text-fasilitas');
    const barFillFasilitas = document.getElementById('bar-fill-fasilitas');
    const barTextBiaya = document.getElementById('bar-text-biaya');
    const barFillBiaya = document.getElementById('bar-fill-biaya');

    // Bobot penting state
    const pentingState = {
        jarak: 3,
        suhu: 2,
        keramaian: 2,
        fasilitas: 3,
        biaya: 1,
        rating: 1
    };

    let selectedV = 0.5;
    const selectedJenis = new Set(); // Default: kosong = semua jenis

    // 2. Fungsi Deskripsi Nilai Slider
    function getDeskripsiJarak(val) {
        if (val <= 50) return `~${val} km (Dekat)`;
        if (val <= 100) return `~${val} km (Sedang)`;
        return `~${val} km (Jauh)`;
    }

    function getDeskripsiSuhu(val) {
        if (val < 20) return `Sejuk ${val}°C`;
        if (val <= 25) return `Nyaman ${val}°C`;
        return `Hangat ${val}°C`;
    }

    function getDeskripsiKeramaian(val) {
        if (val <= 20) return 'Sangat Sepi';
        if (val <= 40) return 'Cenderung Sepi';
        if (val <= 60) return 'Sedang';
        if (val <= 80) return 'Cenderung Ramai';
        return 'Sangat Ramai';
    }

    function getDeskripsiFasilitas(val) {
        if (val <= 25) return 'Sederhana (Dasar)';
        if (val <= 50) return 'Cukup (Toilet & Parkir)';
        if (val <= 80) return 'Lengkap (Toilet, Shuttle, Parkir)';
        return 'Sangat Lengkap (Fasilitas Penuh)';
    }

    function getDeskripsiBiaya(val) {
        return 'Rp ' + Number(val).toLocaleString('id-ID');
    }

    function getBadgeAnggaran(val) {
        if (val <= 40000) return 'Sangat Hemat';
        if (val <= 75000) return 'Ekonomis';
        if (val <= 120000) return 'Standar';
        return 'Fleksibel';
    }

    // 3. Update Readouts & Ringkasan Reaktif
    function updateAllReadouts() {
        const valJarak = parseInt(fJarak.value, 10);
        const textJarak = getDeskripsiJarak(valJarak);
        oJarak.textContent = textJarak;
        if (ringkasJarak) ringkasJarak.textContent = textJarak;

        const valSuhu = parseInt(fSuhu.value, 10);
        const textSuhu = getDeskripsiSuhu(valSuhu);
        oSuhu.textContent = textSuhu;

        const valRamai = parseInt(fRamai.value, 10);
        const textRamai = getDeskripsiKeramaian(valRamai);
        oRamai.textContent = textRamai;
        if (ringkasSuasana) ringkasSuasana.textContent = textRamai;

        const valFas = parseInt(fFas.value, 10);
        const textFas = getDeskripsiFasilitas(valFas);
        oFas.textContent = textFas;

        const valBiaya = parseInt(fBiaya.value, 10);
        const textBiaya = getDeskripsiBiaya(valBiaya);
        oBiaya.textContent = textBiaya;
        if (ringkasAnggaran) ringkasAnggaran.textContent = textBiaya + ' / orang';
        if (ringkasAnggaranBadge) ringkasAnggaranBadge.textContent = getBadgeAnggaran(valBiaya);

        const kotaAsal = fAsal.value;
        if (ringkasAsal) ringkasAsal.textContent = kotaAsal;
        if (ringkasAsalHeader) ringkasAsalHeader.textContent = 'Titik Asal: ' + kotaAsal;

        if (ringkasJenis) {
            if (selectedJenis.size === 0) {
                ringkasJenis.textContent = 'Semua Jenis';
            } else {
                ringkasJenis.textContent = Array.from(selectedJenis).join(', ');
            }
        }

        updatePrioritasPenilaian();
    }

    function getLabelTingkatBobot(val) {
        if (val === 3) return '(Sangat Tinggi)';
        if (val === 2) return '(Tinggi)';
        if (val === 1) return '(Sedang)';
        return '(Abaikan)';
    }

    function updatePrioritasPenilaian() {
        const total = pentingState.jarak + pentingState.suhu + pentingState.keramaian + pentingState.fasilitas + pentingState.biaya;

        const keys = [
            { k: 'jarak', textEl: barTextJarak, fillEl: barFillJarak },
            { k: 'suhu', textEl: barTextSuhu, fillEl: barFillSuhu },
            { k: 'keramaian', textEl: barTextKeramaian, fillEl: barFillKeramaian },
            { k: 'fasilitas', textEl: barTextFasilitas, fillEl: barFillFasilitas },
            { k: 'biaya', textEl: barTextBiaya, fillEl: barFillBiaya },
        ];

        keys.forEach(item => {
            const val = pentingState[item.k];
            const pct = total > 0 ? Math.round((val / total) * 100) : 0;
            const desc = getLabelTingkatBobot(val);

            if (item.textEl) {
                item.textEl.innerHTML = `${pct}% <span class="text-on-surface-variant font-normal">${desc}</span>`;
            }
            if (item.fillEl) {
                item.fillEl.style.width = pct + '%';
            }
        });
    }

    // Pasang listener slider
    [fJarak, fSuhu, fRamai, fFas, fBiaya].forEach(slider => {
        slider.addEventListener('input', updateAllReadouts);
    });
    fAsal.addEventListener('change', updateAllReadouts);

    // 4. Chip Jenis Wisata
    const chipButtons = document.querySelectorAll('.chip-jenis');
    chipButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const val = btn.getAttribute('data-val');
            const defaultIkon = btn.getAttribute('data-ikon');
            const iconEl = btn.querySelector('.chip-icon');

            if (selectedJenis.has(val)) {
                selectedJenis.delete(val);
                btn.className = 'chip-jenis px-3.5 py-1.5 rounded-full text-label-md font-label-md bg-surface-container-low text-on-surface-variant hover:bg-surface-container transition-colors flex items-center gap-1.5 cursor-pointer';
                if (iconEl) iconEl.textContent = defaultIkon;
            } else {
                selectedJenis.add(val);
                btn.className = 'chip-jenis px-3.5 py-1.5 rounded-full text-label-md font-label-md bg-primary text-on-primary shadow-sm flex items-center gap-1.5 cursor-pointer';
                if (iconEl) iconEl.textContent = 'check';
            }
            updateAllReadouts();
        });
    });

    // 5. Tombol Bobot Penting (0 - 3)
    document.querySelectorAll('[data-penting]').forEach(container => {
        const key = container.getAttribute('data-penting');
        const buttons = container.querySelectorAll('.btn-penting');

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                const val = parseInt(btn.getAttribute('data-val'), 10);
                pentingState[key] = val;

                buttons.forEach(b => {
                    b.className = 'btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high cursor-pointer';
                });
                btn.className = 'btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-primary text-on-primary font-bold shadow-xs cursor-pointer';

                updatePrioritasPenilaian();
            });
        });
    });

    // 6. Radio Card Gaya Keputusan
    const gayaCards = document.querySelectorAll('.gaya-card');
    gayaCards.forEach(card => {
        card.addEventListener('click', () => {
            selectedV = parseFloat(card.getAttribute('data-v'));

            gayaCards.forEach(c => {
                const isCurrent = c === card;
                const circle = c.querySelector('.radio-circle');
                const title = c.querySelector('.gaya-title');
                const badge = c.querySelector('.gaya-badge');

                if (isCurrent) {
                    c.className = 'gaya-card relative p-3.5 rounded-xl bg-primary/5 shadow-xs cursor-pointer flex items-start gap-3 transition-colors';
                    if (circle) {
                        circle.className = 'mt-0.5 w-4 h-4 rounded-full bg-primary flex items-center justify-center radio-circle';
                        circle.innerHTML = '<div class="w-1.5 h-1.5 rounded-full bg-surface-container-lowest"></div>';
                    }
                    if (title) title.className = 'font-title text-title text-primary text-[15px] gaya-title';
                    if (badge) badge.className = 'font-label-sm text-label-sm font-mono text-primary bg-primary-fixed/50 px-2 py-0.5 rounded gaya-badge';
                } else {
                    c.className = 'gaya-card relative p-3.5 rounded-xl bg-surface-container-low hover:bg-surface-container cursor-pointer flex items-start gap-3 transition-colors';
                    if (circle) {
                        circle.className = 'mt-0.5 w-4 h-4 rounded-full bg-surface-variant flex items-center justify-center radio-circle';
                        circle.innerHTML = '';
                    }
                    if (title) title.className = 'font-title text-title text-on-surface text-[15px] gaya-title';
                    if (badge) badge.className = 'font-label-sm text-label-sm font-mono text-on-surface-variant bg-surface-container px-2 py-0.5 rounded gaya-badge';
                }
            });
        });
    });

    // 7. Reset Form
    function resetFormToDefault() {
        fAsal.value = 'Bandung';
        fJarak.value = 45;
        fSuhu.value = 18;
        fRamai.value = 30;
        fFas.value = 80;
        fBiaya.value = 50000;

        selectedJenis.clear();
        chipButtons.forEach(btn => {
            const defaultIkon = btn.getAttribute('data-ikon');
            const iconEl = btn.querySelector('.chip-icon');
            btn.className = 'chip-jenis px-3.5 py-1.5 rounded-full text-label-md font-label-md bg-surface-container-low text-on-surface-variant hover:bg-surface-container transition-colors flex items-center gap-1.5 cursor-pointer';
            if (iconEl) iconEl.textContent = defaultIkon;
        });

        const defaultPenting = { jarak: 3, suhu: 2, keramaian: 2, fasilitas: 3, biaya: 1, rating: 1 };
        Object.assign(pentingState, defaultPenting);
        document.querySelectorAll('[data-penting]').forEach(container => {
            const k = container.getAttribute('data-penting');
            const targetVal = defaultPenting[k];
            container.querySelectorAll('.btn-penting').forEach(b => {
                const val = parseInt(b.getAttribute('data-val'), 10);
                if (val === targetVal) {
                    b.className = 'btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-primary text-on-primary font-bold shadow-xs cursor-pointer';
                } else {
                    b.className = 'btn-penting w-7 h-7 rounded-lg text-label-sm font-label-sm bg-surface-container text-on-surface-variant hover:bg-surface-container-high cursor-pointer';
                }
            });
        });

        const cardV05 = document.querySelector('.gaya-card[data-v="0.5"]');
        if (cardV05) cardV05.click();

        updateAllReadouts();
        switchPanel('awal');
    }

    btnReset.addEventListener('click', resetFormToDefault);
    if (btnResetKosong) {
        btnResetKosong.addEventListener('click', () => {
            fBiaya.value = 150000;
            selectedJenis.clear();
            chipButtons.forEach(btn => {
                const defaultIkon = btn.getAttribute('data-ikon');
                const iconEl = btn.querySelector('.chip-icon');
                btn.className = 'chip-jenis px-3.5 py-1.5 rounded-full text-label-md font-label-md bg-surface-container-low text-on-surface-variant hover:bg-surface-container transition-colors flex items-center gap-1.5 cursor-pointer';
                if (iconEl) iconEl.textContent = defaultIkon;
            });
            updateAllReadouts();
            btnSubmit.click();
        });
    }

    // 8. Switch Panel State
    function switchPanel(state) {
        ['awal', 'memuat', 'hasil', 'kosong', 'error'].forEach(s => {
            const el = document.getElementById('panel-' + s);
            if (el) {
                if (s === state) {
                    el.classList.remove('hidden');
                    if (s === 'hasil' || s === 'memuat') {
                        el.classList.add('flex');
                    }
                } else {
                    el.classList.add('hidden');
                    el.classList.remove('flex');
                }
            }
        });
    }

    // 9. Form Submit & Fetch API
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const targetKeramaian = 1 + Math.round((parseInt(fRamai.value, 10) / 100) * 4);
        const targetFasilitas = 1 + Math.round((parseInt(fFas.value, 10) / 100) * 4);

        const payload = {
            asal: fAsal.value,
            jenis: selectedJenis.size > 0 ? Array.from(selectedJenis) : [],
            maxBiaya: parseInt(fBiaya.value, 10),
            target: {
                jarak: parseInt(fJarak.value, 10),
                suhu: parseInt(fSuhu.value, 10),
                keramaian: targetKeramaian,
                fasilitas: targetFasilitas,
            },
            penting: Object.assign({}, pentingState, { rating: 1 }),
            v: selectedV
        };

        // Kondisi 2: Memuat
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

            // Kondisi 4: Kosong
            if (data.mode === 'kosong' || !data.urut || data.urut.length === 0) {
                switchPanel('kosong');
                return;
            }

            // Kondisi 3: Hasil
            renderHasil(data, payload);
            switchPanel('hasil');

        } catch (error) {
            document.getElementById('error-pesan').innerText = 'Tidak dapat menghubungi server, coba lagi.';
            switchPanel('error');
        } finally {
            btnSubmit.disabled = false;
        }
    });

    // 10. Render Hasil Sesuai Desain Persis hasil.html
    function renderHasil(data, payload) {
        const urut = data.urut || [];
        const n = Math.min(5, urut.length);

        document.getElementById('hasil-jumlah-top').textContent = n;
        document.getElementById('hasil-subteks').textContent = `Menampilkan ${n} teratas dari ${data.jumlahKandidat} destinasi • Berdasarkan keberangkatan ${data.asal}`;

        // Badge Mode
        const badgeMode = document.getElementById('hasil-badge-mode');
        const modeLabels = {
            'tunggal': 'Satu pilihan terbaik',
            'setara': 'Beberapa pilihan setara',
            'dua_teratas': 'Dua pilihan teratas',
            'kosong': 'Tidak ada hasil'
        };
        badgeMode.innerHTML = `
            <span class="material-symbols-outlined text-[14px]">verified</span>
            <span>${modeLabels[data.mode] || data.mode}</span>
        `;

        const container = document.getElementById('hasil-cards-container');
        container.innerHTML = '';

        const topCards = urut.slice(0, n);
        topCards.forEach((item, index) => {
            const cardEl = document.createElement('article');
            const fotoSrc = item.foto ? ('/images/destinasi/' + item.foto) : '/images/placeholder.jpg';
            const htmFormatted = item.biaya > 0 ? ('HTM Rp ' + Number(item.biaya).toLocaleString('id-ID')) : 'Gratis';
            const isUtama = Boolean(item.utama);

            let rankWord = index < 3 ? 'Rekomendasi' : 'Alternatif';
            let alasanHtml = '';
            if (item.alasan && item.alasan.length > 0) {
                alasanHtml = `
                    <div class="space-y-1.5 pt-1">
                        ${item.alasan.slice(0, 3).map(al => `
                            <div class="flex items-start gap-2 font-body-sm text-body-sm text-on-surface">
                                <span class="material-symbols-outlined text-primary text-[16px] mt-0.5 flex-shrink-0">check_circle</span>
                                <span>${escapeHtml(al)}</span>
                            </div>
                        `).join('')}
                    </div>
                `;
            }

            if (index === 0) {
                // Kartu #1 (Best Match) - persis hasil.html baris 294
                cardEl.className = 'bg-surface-container-lowest rounded-2xl overflow-hidden shadow-[0_12px_28px_-4px_rgba(46,125,91,0.12)] transition-all duration-300';
                cardEl.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-12">
                        <!-- Gambar & Floating Badge -->
                        <div class="relative md:col-span-5 min-h-[220px] md:min-h-full">
                            <img alt="${escapeHtml(item.nama)}" class="w-full h-full object-cover" src="${fotoSrc}" onerror="this.src='/images/placeholder.jpg'">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent md:hidden"></div>
                            <!-- Badge Peringkat #1 -->
                            <div class="absolute top-3 left-3 px-3 py-1.5 rounded-full bg-secondary-container text-on-secondary-container font-headline-sm text-headline-sm flex items-center gap-1.5 shadow-md">
                                <span class="material-symbols-outlined text-[18px]">workspace_premium</span>
                                <span>#1 Rekomendasi</span>
                            </div>
                        </div>
                        <!-- Content Body -->
                        <div class="p-5 md:p-6 md:col-span-7 flex flex-col justify-between">
                            <div>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="px-2.5 py-0.5 rounded-full bg-surface-container text-on-surface-variant font-label-sm text-label-sm whitespace-nowrap">${escapeHtml(item.jenis)}</span>
                                            <span class="text-outline-variant">•</span>
                                            <span class="font-body-sm text-body-sm text-on-surface-variant truncate">${escapeHtml(item.wilayah)}</span>
                                        </div>
                                        <h3 class="font-headline-md text-headline-md text-on-surface hover:text-primary transition-colors cursor-pointer">
                                            <a href="/destinasi/${item.id}">${escapeHtml(item.nama)}</a>
                                        </h3>
                                    </div>
                                    <!-- Skor Kompromi -->
                                    <div class="text-right flex-shrink-0">
                                        <div class="font-display text-[26px] leading-tight font-extrabold text-primary">${Number(item.persen).toFixed(1)}%</div>
                                        <span class="font-label-sm text-label-sm text-on-surface-variant">Q: ${Number(item.Q).toFixed(3)}</span>
                                    </div>
                                </div>
                                <!-- Quick Spec Pills (Satu baris chip ringkas dengan rating bintang) -->
                                <div class="flex flex-wrap gap-2 my-3.5">
                                    <div class="px-2.5 py-1 rounded-lg bg-surface-container font-label-sm text-label-sm text-on-surface flex items-center gap-1.5 whitespace-nowrap">
                                        <span>${Number(item.jarak).toFixed(1)} km</span>
                                        <span>•</span>
                                        <span>${item.suhu}°C</span>
                                        <span>•</span>
                                        <span>${htmFormatted}</span>
                                        <span>•</span>
                                        <span class="inline-flex items-center gap-0.5 text-secondary font-semibold">
                                            <span class="material-symbols-outlined text-[15px]">star</span>
                                            <span>${item.rating}</span>
                                        </span>
                                    </div>
                                </div>
                                <!-- Alasan -->
                                ${alasanHtml}
                            </div>
                            <!-- Action Footer -->
                            <div class="flex items-center justify-between gap-3 pt-4 mt-3 border-t border-surface-variant/40">
                                <div class="flex items-center gap-2">
                                    <a href="/destinasi/${item.id}" class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-container text-on-primary font-label-lg text-label-lg transition-colors flex items-center gap-1.5 shadow-sm">
                                        <span>Lihat Detail</span>
                                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                                    </a>
                                    <button class="p-2 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-primary transition-colors cursor-pointer" title="Simpan ke Rencana" type="button">
                                        <span class="material-symbols-outlined text-[20px]">bookmark</span>
                                    </button>
                                </div>
                                <span class="font-label-sm text-label-sm text-primary font-semibold flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">star</span>
                                    Rekomendasi Utama
                                </span>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                // Kartu #2, #3, #4, dst - persis hasil.html baris 382
                cardEl.className = index < 3
                    ? 'bg-surface-container-lowest rounded-2xl overflow-hidden shadow-[0_4px_20px_-2px_rgba(46,125,91,0.06)] hover:shadow-[0_12px_28px_-4px_rgba(46,125,91,0.12)] transition-all duration-300'
                    : 'bg-surface-container-lowest rounded-2xl border border-surface-variant/50 overflow-hidden shadow-sm hover:shadow-md transition-all duration-300';
                
                cardEl.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-12">
                        <div class="relative md:col-span-5 min-h-[190px] md:min-h-full">
                            <img alt="${escapeHtml(item.nama)}" class="w-full h-full object-cover" src="${fotoSrc}" onerror="this.src='/images/placeholder.jpg'">
                            <div class="absolute top-3 left-3 px-3 py-1 rounded-full ${index < 3 ? 'bg-surface-container-highest text-on-surface font-title text-title text-[14px]' : 'bg-surface-container text-on-surface font-label-md text-label-md'} flex items-center gap-1 shadow-sm">
                                ${index < 3 ? '<span class="material-symbols-outlined text-[16px] text-primary">military_tech</span>' : ''}
                                <span>#${item.peringkat} ${rankWord}</span>
                            </div>
                        </div>
                        <div class="p-5 md:p-6 md:col-span-7 flex flex-col justify-between">
                            <div>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="px-2.5 py-0.5 rounded-full bg-surface-container text-on-surface-variant font-label-sm text-label-sm whitespace-nowrap">${escapeHtml(item.jenis)}</span>
                                            <span class="text-outline-variant">•</span>
                                            <span class="font-body-sm text-body-sm text-on-surface-variant truncate">${escapeHtml(item.wilayah)}</span>
                                        </div>
                                        <h3 class="font-headline-md text-headline-md text-on-surface hover:text-primary transition-colors cursor-pointer">
                                            <a href="/destinasi/${item.id}">${escapeHtml(item.nama)}</a>
                                        </h3>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <div class="font-headline-md text-headline-md font-bold text-primary">${Number(item.persen).toFixed(1)}%</div>
                                        <span class="font-label-sm text-label-sm text-on-surface-variant">Q: ${Number(item.Q).toFixed(3)}</span>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2 my-3">
                                    <div class="px-2.5 py-0.5 rounded-lg bg-surface-container font-label-sm text-label-sm text-on-surface flex items-center gap-1.5 whitespace-nowrap">
                                        <span>${Number(item.jarak).toFixed(1)} km</span>
                                        <span>•</span>
                                        <span>${item.suhu}°C</span>
                                        <span>•</span>
                                        <span>${htmFormatted}</span>
                                        <span>•</span>
                                        <span class="inline-flex items-center gap-0.5 text-secondary font-semibold">
                                            <span class="material-symbols-outlined text-[15px]">star</span>
                                            <span>${item.rating}</span>
                                        </span>
                                    </div>
                                </div>
                                ${alasanHtml}
                            </div>
                            <div class="flex items-center justify-between gap-3 pt-3 mt-3 border-t border-surface-variant/40">
                                <a href="/destinasi/${item.id}" class="px-4 py-1.5 rounded-xl bg-surface-container hover:bg-primary hover:text-on-primary text-on-surface font-label-md text-label-md transition-colors flex items-center gap-1">
                                    <span>Lihat Detail</span>
                                    <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                                </a>
                                <span class="font-body-sm text-body-sm text-on-surface-variant truncate">${escapeHtml(item.wilayah)}</span>
                            </div>
                        </div>
                    </div>
                `;
            }

            container.appendChild(cardEl);
        });

        renderKalkulasiDetails(data, payload);
    }

    function renderKalkulasiDetails(data, payload) {
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
            { key: 'keramaian', nama: 'Keramaian', tipe: 'target', target: getDeskripsiKeramaian(fRamai.value) },
            { key: 'fasilitas', nama: 'Fasilitas', tipe: 'target', target: getDeskripsiFasilitas(fFas.value) },
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

    // Inisialisasi awal
    updateAllReadouts();
})();
</script>
@endpush
