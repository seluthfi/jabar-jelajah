<footer class="w-full bg-surface-container-low mt-20">
    <div class="max-w-[1280px] mx-auto px-6 lg:px-12 py-16">
        <div class="flex flex-col md:flex-row items-center justify-between gap-8 pb-10">
            <div class="flex flex-col items-center md:items-start gap-2">
                <div class="flex items-center gap-3">
                    <img alt="Logo Jelajah Jabar" class="h-7 w-auto object-contain" src="{{ asset('images/Jelajah Jabar Mosque Train Logo.png') }}"/>
                    <span class="font-title text-title text-primary">Jelajah Jabar</span>
                </div>
                <p class="font-body-sm text-body-sm text-on-surface-variant max-w-sm text-center md:text-left">Kurasi destinasi alam terbuka terbaik, keindahan pegunungan Parahyangan, dan pesona wisata Jawa Barat.</p>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-6">
                <a class="font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors" data-path="tentang-kami" href="#">Tentang Kami</a>
                <a class="font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors" data-path="kontak" href="#">Kontak</a>
                <a class="font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors" data-path="kebijakan-privasi" href="#">Kebijakan Privasi</a>
            </div>
            <div class="flex items-center gap-3">
                <a aria-label="Eksplorasi Peta" class="w-9 h-9 rounded-full bg-surface-container-high hover:bg-primary-container text-on-surface-variant hover:text-on-primary flex items-center justify-center transition-colors" href="#">
                    <span class="material-symbols-outlined text-[18px]">map</span>
                </a>
                <a aria-label="Komunitas Jelajah" class="w-9 h-9 rounded-full bg-surface-container-high hover:bg-primary-container text-on-surface-variant hover:text-on-primary flex items-center justify-center transition-colors" href="#">
                    <span class="material-symbols-outlined text-[18px]">groups</span>
                </a>
                <a aria-label="Pemberitahuan Cuaca" class="w-9 h-9 rounded-full bg-surface-container-high hover:bg-primary-container text-on-surface-variant hover:text-on-primary flex items-center justify-center transition-colors" href="#">
                    <span class="material-symbols-outlined text-[18px]">landscape</span>
                </a>
            </div>
        </div>
        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 font-body-sm text-body-sm text-on-surface-variant text-center">
            <p>© {{ date('Y') }} Jelajah Jabar • Pesona Pariwisata Jawa Barat. Seluruh hak cipta dilindungi.</p>
            <p class="text-tertiary font-label-sm text-label-sm tracking-wide uppercase">Nuansa Parahyangan Asri</p>
        </div>
    </div>
</footer>
