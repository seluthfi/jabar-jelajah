git @extends('layouts.app')

@section('content')
<div class="flex flex-col w-full">
    <!-- Subtle Parahyangan Background Elements -->
    <div class="relative w-full max-w-[1280px] mx-auto px-6 lg:px-12 py-10 lg:py-16">
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-primary-fixed/30 rounded-full blur-3xl pointer-events-none -z-10"></div>
        <div class="absolute top-1/3 left-10 w-80 h-80 bg-secondary-fixed/40 rounded-full blur-3xl pointer-events-none -z-10"></div>

        <!-- 1. Header & Page Introduction -->
        <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-10">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-surface-container-high text-primary mb-4 shadow-sm">
                    <span class="material-symbols-outlined text-[16px] text-primary">explore</span>
                    <span class="font-label-sm text-label-sm tracking-wide uppercase">Eksplorasi Destinasi Pasundan</span>
                </div>
                <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight mb-3">
                    Katalog Wisata Jawa Barat
                </h1>
                <p class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed">
                    Temukan keindahan alam, pegunungan sejuk, pantai tropis, hingga kearifan budaya lokal terbaik di Tanah Priangan.
                </p>
            </div>

            <!-- Info counter pill -->
            <div class="flex items-center self-start md:self-auto gap-3 px-5 py-3 rounded-2xl bg-surface-container-low shadow-[0_4px_20px_-2px_rgba(46,125,91,0.06)]">
                <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[20px]">verified</span>
                </div>
                <div>
                    <span class="block font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">Koleksi Terkurasi</span>
                    <span class="font-title text-title text-primary">{{ $destinasi->total() }} Destinasi Wisata</span>
                </div>
            </div>
        </header>

        <!-- 2. Interactive Category Filter Chips (Strictly Pill Links - No Form Inputs) -->
        <section class="mb-12">
            <div class="flex items-center gap-3 overflow-x-auto pb-2 scrollbar-none">
                <!-- Active Chip: Semua -->
                <a class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-full {{ empty($selectedJenis) ? 'bg-primary-container text-on-primary font-label-lg text-label-lg shadow-sm hover:opacity-95' : 'bg-surface-container-lowest text-on-surface-variant hover:text-primary hover:bg-surface-container font-label-lg text-label-lg shadow-sm' }} transition-all flex-shrink-0" href="{{ route('katalog') }}">
                    <span class="material-symbols-outlined text-[18px]">travel_explore</span>
                    <span>Semua</span>
                    <span class="px-2 py-0.5 rounded-full {{ empty($selectedJenis) ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant' }} font-label-sm text-label-sm ml-1">{{ $totalDestinasi }}</span>
                </a>

                @foreach ($kategoriList as $kat)
                    @php
                        $isActive = ($selectedJenis === $kat['nama']);
                        $count = $jenisCounts[$kat['nama']] ?? 0;
                    @endphp
                    <a class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full {{ $isActive ? 'bg-primary-container text-on-primary font-label-lg text-label-lg shadow-sm hover:opacity-95' : 'bg-surface-container-lowest text-on-surface-variant hover:text-primary hover:bg-surface-container font-label-lg text-label-lg shadow-sm' }} transition-all flex-shrink-0" href="{{ route('katalog', ['jenis' => $kat['nama']]) }}">
                        <span class="material-symbols-outlined text-[18px] {{ $isActive ? 'text-on-primary' : 'text-tertiary' }}">{{ $kat['ikon'] }}</span>
                        <span>{{ $kat['nama'] }}</span>
                        @if ($count > 0)
                            <span class="px-2 py-0.5 rounded-full {{ $isActive ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant' }} font-label-sm text-label-sm ml-1">{{ $count }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>

        <!-- 3. Destination Cards Grid (3 Columns) -->
        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @php
                $iconMap = [
                    'Pegunungan' => 'landscape',
                    'Air & Danau' => 'water_drop',
                    'Pantai' => 'beach_access',
                    'Taman & Keluarga' => 'park',
                    'Budaya & Sejarah' => 'museum',
                ];
            @endphp

            @forelse ($destinasi as $item)
                @php
                    $iconJenis = $iconMap[$item->jenis] ?? 'landscape';
                @endphp
                <article class="group flex flex-col bg-surface-container-lowest rounded-2xl overflow-hidden shadow-[0_4px_20px_-2px_rgba(46,125,91,0.06),0_2px_6px_-1px_rgba(31,41,55,0.04)] hover:shadow-[0_12px_28px_-4px_rgba(46,125,91,0.12),0_4px_12px_-2px_rgba(31,41,55,0.06)] transition-all duration-300">
                    <div class="relative w-full aspect-[4/3] overflow-hidden bg-surface-container">
                        <a href="{{ url('/destinasi/' . $item->id) }}" class="block w-full h-full">
                            <img alt="{{ $item->nama }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="{{ $item->foto_url }}"/>
                        </a>
                        <span class="absolute top-4 left-4 px-3 py-1.5 rounded-full bg-surface/90 backdrop-blur-md text-primary font-label-sm text-label-sm shadow-sm flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[14px]">{{ $iconJenis }}</span>
                            {{ $item->jenis }}
                        </span>
                        <button aria-label="Simpan destinasi favorit" class="absolute top-4 right-4 w-9 h-9 rounded-full bg-surface/90 backdrop-blur-md flex items-center justify-center text-on-surface-variant hover:text-error transition-colors shadow-sm" type="button">
                            <span class="material-symbols-outlined text-[18px]">favorite</span>
                        </button>
                    </div>
                    <div class="p-6 flex flex-col flex-1 justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="inline-flex items-center gap-1 font-body-sm text-body-sm text-tertiary">
                                    <span class="material-symbols-outlined text-[16px]">location_on</span>
                                    <span class="line-clamp-1">{{ $item->wilayah }}</span>
                                </span>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-secondary-fixed/50 font-label-sm text-label-sm text-on-secondary-fixed-variant flex-shrink-0">
                                    <span class="material-symbols-outlined text-[14px] text-secondary" style="font-variation-settings: 'FILL' 1;">star</span>
                                    {{ number_format($item->rating, 1) }}
                                </span>
                            </div>
                            <h2 class="font-headline-sm text-headline-sm text-on-surface tracking-tight group-hover:text-primary transition-colors line-clamp-1">
                                <a href="{{ url('/destinasi/' . $item->id) }}">
                                    {{ $item->nama }}
                                </a>
                            </h2>
                            <p class="font-body-sm text-body-sm text-on-surface-variant mt-2 line-clamp-2">
                                {{ $item->deskripsi ?? 'Nikmati pesona wisata alam dan budaya di Jawa Barat dengan pengalaman berkesan.' }}
                            </p>
                        </div>
                        <div class="pt-6 mt-6 flex items-center justify-between bg-surface-container-low/60 -mx-6 -mb-6 px-6 py-4">
                            <div>
                                <span class="block font-body-sm text-body-sm text-on-surface-variant text-[11px]">Tiket Masuk</span>
                                <span class="font-title text-title text-primary">
                                    @if($item->biaya > 0)
                                        Rp {{ number_format($item->biaya, 0, ',', '.') }}
                                    @else
                                        Gratis
                                    @endif
                                    <span class="font-body-sm text-body-sm text-on-surface-variant font-normal"> /org</span>
                                </span>
                            </div>
                            <a class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md transition-all shadow-sm" href="{{ url('/destinasi/' . $item->id) }}">
                                <span>Lihat Detail</span>
                                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full py-16 flex flex-col items-center justify-center text-center p-8 bg-surface-container-lowest rounded-2xl shadow-sm">
                    <div class="w-16 h-16 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-3xl">travel_explore</span>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface mb-2">Tidak ada destinasi ditemukan</h3>
                    <p class="font-body-md text-body-md text-on-surface-variant max-w-md mb-6">
                        Tidak ada destinasi yang cocok dengan filter "{{ $selectedJenis }}" saat ini.
                    </p>
                    <a href="{{ route('katalog') }}" class="px-6 py-2.5 rounded-full bg-primary-container text-on-primary font-label-lg text-label-lg shadow-sm hover:opacity-95 transition-all">
                        Lihat Semua Destinasi
                    </a>
                </div>
            @endforelse
        </section>

        <!-- 4. Navigasi Pagination -->
        @if ($destinasi->hasPages())
            <nav aria-label="Navigasi Halaman Destinasi" class="mt-16 pt-8 flex flex-col sm:flex-row items-center justify-between gap-6 border-t border-surface-container-highest">
                <!-- Status Keterangan Teks Halus -->
                <p class="font-body-md text-body-md text-on-surface-variant text-center sm:text-left">
                    Menampilkan <span class="font-title text-title text-on-surface">{{ $destinasi->firstItem() }} - {{ $destinasi->lastItem() }}</span> dari <span class="font-title text-title text-on-surface">{{ $destinasi->total() }}</span> destinasi wisata
                </p>

                <!-- Pagination Numbers & Actions -->
                <div class="flex items-center gap-2">
                    <!-- Tombol Sebelumnya -->
                    @if ($destinasi->onFirstPage())
                        <span aria-disabled="true" class="inline-flex items-center gap-1 px-4 py-2 rounded-xl bg-surface-container-low text-outline-variant font-label-md text-label-md cursor-not-allowed select-none">
                            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                            <span>Sebelumnya</span>
                        </span>
                    @else
                        <a class="inline-flex items-center gap-1 px-4 py-2 rounded-xl bg-surface-container-lowest hover:bg-primary-container text-on-surface hover:text-on-primary font-label-md text-label-md transition-all shadow-sm" href="{{ $destinasi->previousPageUrl() }}">
                            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                            <span>Sebelumnya</span>
                        </a>
                    @endif

                    <!-- Nomor Halaman -->
                    @foreach ($destinasi->getUrlRange(1, $destinasi->lastPage()) as $page => $url)
                        @if ($page == $destinasi->currentPage())
                            <span aria-current="page" class="w-10 h-10 rounded-xl bg-primary-container text-on-primary font-label-md text-label-md flex items-center justify-center shadow-sm">
                                {{ $page }}
                            </span>
                        @else
                            <a class="w-10 h-10 rounded-xl bg-surface-container-lowest text-on-surface hover:bg-surface-container hover:text-primary font-label-md text-label-md flex items-center justify-center transition-colors shadow-sm" href="{{ $url }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    <!-- Tombol Selanjutnya -->
                    @if ($destinasi->hasMorePages())
                        <a class="inline-flex items-center gap-1 px-4 py-2 rounded-xl bg-surface-container-lowest hover:bg-primary-container text-on-surface hover:text-on-primary font-label-md text-label-md transition-all shadow-sm" href="{{ $destinasi->nextPageUrl() }}">
                            <span>Selanjutnya</span>
                            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                        </a>
                    @else
                        <span aria-disabled="true" class="inline-flex items-center gap-1 px-4 py-2 rounded-xl bg-surface-container-low text-outline-variant font-label-md text-label-md cursor-not-allowed select-none">
                            <span>Selanjutnya</span>
                            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                        </span>
                    @endif
                </div>
            </nav>
        @elseif ($destinasi->total() > 0)
            <div class="mt-16 pt-8 flex items-center justify-between border-t border-surface-container-highest">
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Menampilkan <span class="font-title text-title text-on-surface">{{ $destinasi->firstItem() }} - {{ $destinasi->lastItem() }}</span> dari <span class="font-title text-title text-on-surface">{{ $destinasi->total() }}</span> destinasi wisata
                </p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Micro-interaction favorite buttons
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
