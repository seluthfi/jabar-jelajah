<header class="fixed top-0 left-0 right-0 w-full z-50 bg-surface/85 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
    <div class="h-20 max-w-[1280px] mx-auto px-6 lg:px-12 flex items-center justify-between">
        <div class="flex items-center gap-8">
            <a class="flex items-center gap-3 group" data-path="beranda" href="{{ url('/') }}">
                <img alt="Logo Jelajah Jabar" class="h-8 w-auto object-contain" src="{{ asset('images/Jelajah Jabar Mosque Train Logo.png') }}"/>
                <span class="font-headline-sm text-headline-sm text-primary tracking-tight">Jelajah Jabar</span>
            </a>
            <nav class="hidden md:flex items-center gap-2" data-active-classes="bg-primary-container text-on-primary font-label-lg text-label-lg rounded-full px-4 py-2">
                <a class="transition-all duration-200 {{ request()->is('/') ? 'bg-primary-container text-on-primary' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container' }} font-label-lg text-label-lg rounded-full px-4 py-2" data-path="beranda" href="{{ url('/') }}" @if(request()->is('/')) aria-current="page" @endif>Beranda</a>
                <a class="px-4 py-2 rounded-full font-label-lg text-label-lg {{ request()->is('katalog*') ? 'bg-primary-container text-on-primary' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container' }} transition-all duration-200" data-path="katalog" href="{{ url('/katalog') }}">Katalog</a>
                <a class="px-4 py-2 rounded-full font-label-lg text-label-lg {{ request()->is('rekomendasi*') ? 'bg-primary-container text-on-primary' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container' }} transition-all duration-200" data-path="cari-rekomendasi" href="{{ url('/rekomendasi') }}">Cari Rekomendasi</a>
            </nav>
        </div>
        <div class="flex items-center gap-4">
            <a class="hidden sm:inline-flex items-center justify-center px-5 py-2.5 rounded-full bg-secondary-container hover:bg-secondary text-on-secondary-container hover:text-on-secondary font-label-lg text-label-lg transition-all duration-200 shadow-sm" data-path="cari-rekomendasi" href="{{ url('/rekomendasi') }}">Cari Wisata</a>
        </div>
    </div>
</header>
