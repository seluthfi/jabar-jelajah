<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RekomendasiRequest;
use App\Models\Destinasi;
use App\Models\KotaAsal;
use App\Services\AlasanBuilder;
use App\Services\Haversine;
use App\Services\Vikor;
use Illuminate\Http\JsonResponse;

class RekomendasiController extends Controller
{
    /**
     * Menghitung peringkat rekomendasi destinasi wisata berbasis VIKOR.
     */
    public function hitung(RekomendasiRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $asalNama = $validated['asal'];
        $v = (float) $validated['v'];
        $maxBiaya = $validated['maxBiaya'] ?? null;
        $jenisFilter = $validated['jenis'] ?? [];
        $penting = $validated['penting'] ?? [];
        $target = $validated['target'] ?? [];

        // Hitung total seluruh destinasi aktif di database
        $totalAktif = Destinasi::count();

        // Hitung bobot ternormalisasi awal untuk kriteria
        $kriteriaKeys = ['biaya', 'rating', 'jarak', 'suhu', 'keramaian', 'fasilitas'];
        $totalPenting = array_sum(array_map('floatval', $penting));
        $bobotTernormalisasi = [];
        foreach ($kriteriaKeys as $k) {
            $bobotTernormalisasi[$k] = $totalPenting > 0
                ? round(((float) ($penting[$k] ?? 0)) / $totalPenting, 4)
                : 0.0;
        }

        // 1. Ambil destinasi dengan Eloquent query builder
        $query = Destinasi::query();

        if (!empty($jenisFilter) && is_array($jenisFilter)) {
            $query->whereIn('jenis', $jenisFilter);
        }

        if ($maxBiaya !== null) {
            $query->where('biaya', '<=', (int) $maxBiaya);
        }

        $kandidatModels = $query->get();
        $jumlahKandidat = $kandidatModels->count();

        // Jika tidak ada destinasi yang lolos filter, kembalikan HTTP 200 dengan mode: "kosong" dan urut: []
        if ($jumlahKandidat === 0) {
            return response()->json([
                'mode' => 'kosong',
                'jumlahKandidat' => 0,
                'totalAktif' => $totalAktif,
                'asal' => $asalNama,
                'v' => $v,
                'bobotTernormalisasi' => $bobotTernormalisasi,
                'DQ' => 0.0,
                'keunggulan' => false,
                'kestabilan' => false,
                'urut' => [],
            ]);
        }

        // Ambil data koordinat kota asal keberangkatan
        $kotaAsal = KotaAsal::where('nama', $asalNama)->firstOrFail();

        // 2. Hitung jarak tiap destinasi menggunakan Haversine (R = 6371 km)
        $dataDestinasi = [];
        foreach ($kandidatModels as $item) {
            $rowArray = $item->toArray();
            $jarakKm = Haversine::hitung($kotaAsal->lat, $kotaAsal->lon, $item->lat, $item->lon);
            $rowArray['jarak'] = round($jarakKm, 1);
            $dataDestinasi[] = $rowArray;
        }

        // 3. Susun konfigurasi kriteria untuk metode VIKOR
        $kriteria = [
            [
                'key' => 'biaya',
                'tipe' => 'cost',
                'bobot' => (float) ($penting['biaya'] ?? 0),
            ],
            [
                'key' => 'rating',
                'tipe' => 'benefit',
                'bobot' => (float) ($penting['rating'] ?? 0),
            ],
            [
                'key' => 'jarak',
                'tipe' => 'target',
                'bobot' => (float) ($penting['jarak'] ?? 0),
                'target' => (float) ($target['jarak'] ?? 0),
            ],
            [
                'key' => 'suhu',
                'tipe' => 'target',
                'bobot' => (float) ($penting['suhu'] ?? 0),
                'target' => (float) ($target['suhu'] ?? 0),
            ],
            [
                'key' => 'keramaian',
                'tipe' => 'target',
                'bobot' => (float) ($penting['keramaian'] ?? 0),
                'target' => (float) ($target['keramaian'] ?? 0),
            ],
            [
                'key' => 'fasilitas',
                'tipe' => 'target',
                'bobot' => (float) ($penting['fasilitas'] ?? 0),
                'target' => (float) ($target['fasilitas'] ?? 0),
            ],
        ];

        // 4. Panggil Vikor::hitung()
        $hasilVikor = Vikor::hitung($dataDestinasi, $kriteria, $v);

        // 5. Tambahkan persen dan alasan ke tiap destinasi
        $urutFinal = [];
        foreach ($hasilVikor['urut'] as $item) {
            // Persen kecocokan relatif: round((1 - Q) * 100, 1)
            $persen = round((1.0 - (float) $item['Q']) * 100.0, 1);
            $item['persen'] = $persen;

            // Alasan disusun oleh AlasanBuilder
            $item['alasan'] = AlasanBuilder::build($item, $validated);

            $urutFinal[] = $item;
        }

        // 6. Format dan kembalikan respons JSON
        return response()->json([
            'mode' => $hasilVikor['mode'],
            'jumlahKandidat' => $jumlahKandidat,
            'totalAktif' => $totalAktif,
            'asal' => $asalNama,
            'v' => $v,
            'bobotTernormalisasi' => $hasilVikor['bobotTernormalisasi'],
            'DQ' => round((float) $hasilVikor['DQ'], 4),
            'keunggulan' => $hasilVikor['keunggulan'],
            'kestabilan' => $hasilVikor['kestabilan'],
            'urut' => $urutFinal,
        ]);
    }
}
