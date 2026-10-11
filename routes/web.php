<?php

use App\Models\Destinasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $populer = Destinasi::orderByDesc('rating')->take(4)->get();

    return view('beranda', compact('populer'));
})->name('beranda');

Route::get('/katalog', function (Request $request) {
    $selectedJenis = $request->query('jenis');
    $query = Destinasi::query();

    $validJenis = [
        'Pantai',
        'Pegunungan',
        'Air & Danau',
        'Taman & Keluarga',
        'Budaya & Sejarah',
    ];

    if ($selectedJenis && in_array($selectedJenis, $validJenis)) {
        $query->where('jenis', $selectedJenis);
    } else {
        $selectedJenis = null;
    }

    $destinasi = $query->orderBy('nama')->paginate(8)->withQueryString();
    $totalDestinasi = Destinasi::count();

    $jenisCounts = Destinasi::selectRaw('jenis, count(*) as count')
        ->groupBy('jenis')
        ->pluck('count', 'jenis')
        ->toArray();

    $kategoriList = [
        ['nama' => 'Pantai', 'ikon' => 'surfing'],
        ['nama' => 'Pegunungan', 'ikon' => 'landscape'],
        ['nama' => 'Air & Danau', 'ikon' => 'water_drop'],
        ['nama' => 'Taman & Keluarga', 'ikon' => 'park'],
        ['nama' => 'Budaya & Sejarah', 'ikon' => 'temple_buddhist'],
    ];

    return view('katalog', compact('destinasi', 'selectedJenis', 'totalDestinasi', 'jenisCounts', 'kategoriList'));
})->name('katalog');

Route::get('/destinasi/{id}', function ($id) {
    $destinasi = Destinasi::findOrFail($id);

    return view('destinasi.show', compact('destinasi'));
})->name('destinasi.show');

Route::get('/rekomendasi', function () {
    $kotaList = \App\Models\KotaAsal::orderBy('nama')->get();
    $totalAktif = Destinasi::count();

    return view('rekomendasi', compact('kotaList', 'totalAktif'));
})->name('rekomendasi');




