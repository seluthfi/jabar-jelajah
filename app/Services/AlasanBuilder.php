<?php

namespace App\Services;

class AlasanBuilder
{
    /**
     * Ambang batas maksimum nilai d untuk kriteria yang layak dijadikan alasan (d <= 0.4)
     */
    public const BATAS_ALASAN = 0.4;

    /**
     * Label tingkat keramaian (skala 1-5)
     */
    protected const LABELS_KERAMAIAN = [
        1 => 'sangat sepi',
        2 => 'sepi',
        3 => 'sedang',
        4 => 'ramai',
        5 => 'sangat ramai',
    ];

    /**
     * Label kelengkapan fasilitas (skala 1-5)
     */
    protected const LABELS_FASILITAS = [
        1 => 'sangat sederhana',
        2 => 'sederhana',
        3 => 'cukup',
        4 => 'lengkap',
        5 => 'sangat lengkap',
    ];

    /**
     * Urutan prioritas tie-breaker bawaan
     */
    protected const URUTAN_BAKU = [
        'biaya' => 1,
        'rating' => 2,
        'jarak' => 3,
        'suhu' => 4,
        'keramaian' => 5,
        'fasilitas' => 6,
    ];

    /**
     * Menyusun kalimat alasan kecocokan destinasi berdasarkan hasil perhitungan VIKOR dan preferensi pengguna.
     *
     * @param array $destinasi Data destinasi hasil VIKOR (memuat d, biaya, rating, jarak, suhu, keramaian, fasilitas)
     * @param array $params Parameter request (memuat penting, target, asal, maxBiaya)
     * @return array Daftar alasan (maksimal 3 kalimat)
     */
    public static function build(array $destinasi, array $params): array
    {
        $d = $destinasi['d'] ?? [];
        $penting = $params['penting'] ?? [];
        $target = $params['target'] ?? [];
        $asal = $params['asal'] ?? '';
        $maxBiaya = $params['maxBiaya'] ?? null;

        // Kumpulkan kriteria yang memenuhi syarat: penting > 0 dan d <= BATAS_ALASAN (0.4)
        $kandidat = [];
        foreach (self::URUTAN_BAKU as $key => $prioritas) {
            $bobot = (int) ($penting[$key] ?? 0);
            if ($bobot > 0 && isset($d[$key]) && $d[$key] <= self::BATAS_ALASAN + 1e-9) {
                $kandidat[] = $key;
            }
        }

        if (empty($kandidat)) {
            return [];
        }

        // Urutkan kriteria:
        // 1. d naik (kecocokan terbaik lebih dulu)
        // 2. tie: bobot (penting) lebih besar dulu
        // 3. tie: urutan baku (biaya, rating, jarak, suhu, keramaian, fasilitas)
        usort($kandidat, function ($a, $b) use ($d, $penting) {
            $dA = (float) $d[$a];
            $dB = (float) $d[$b];
            if (abs($dA - $dB) > 1e-9) {
                return $dA <=> $dB;
            }

            $bobotA = (int) ($penting[$a] ?? 0);
            $bobotB = (int) ($penting[$b] ?? 0);
            if ($bobotA !== $bobotB) {
                return $bobotB <=> $bobotA;
            }

            return self::URUTAN_BAKU[$a] <=> self::URUTAN_BAKU[$b];
        });

        // Ambil paling banyak 3 kriteria terbaik
        $terpilih = array_slice($kandidat, 0, 3);

        $alasanList = [];
        foreach ($terpilih as $kriteria) {
            $kalimat = self::formatKalimat($kriteria, $destinasi, [
                'asal' => $asal,
                'target' => $target,
                'maxBiaya' => $maxBiaya,
            ]);

            if ($kalimat !== null) {
                $alasanList[] = $kalimat;
            }
        }

        return $alasanList;
    }

    /**
     * Memformat kalimat alasan untuk satu kriteria sesuai spesifikasi.
     */
    protected static function formatKalimat(string $kriteria, array $destinasi, array $context): ?string
    {
        $asal = $context['asal'];
        $target = $context['target'];
        $maxBiaya = $context['maxBiaya'];

        switch ($kriteria) {
            case 'biaya':
                $biaya = (int) ($destinasi['biaya'] ?? 0);
                if ($biaya === 0) {
                    $teks = "Tiket masuk gratis";
                } else {
                    $teks = "Tiket Rp" . number_format($biaya, 0, ',', '.');
                }
                if ($maxBiaya !== null && $maxBiaya !== '') {
                    $teks .= " (anggaran maksimal Rp" . number_format((int) $maxBiaya, 0, ',', '.') . ")";
                }
                return $teks;

            case 'rating':
                $rating = $destinasi['rating'] ?? 0;
                return "Rating {$rating} dari 5";

            case 'jarak':
                $jarak = round((float) ($destinasi['jarak'] ?? 0), 1);
                $targetJarak = $target['jarak'] ?? 0;
                return "Sekitar {$jarak} km dari {$asal} (garis lurus), dekat dengan jarak pilihanmu {$targetJarak} km";

            case 'suhu':
                $suhu = $destinasi['suhu'] ?? 0;
                $targetSuhu = $target['suhu'] ?? 0;
                return "Suhu sekitar {$suhu}°C, mendekati pilihanmu {$targetSuhu}°C";

            case 'keramaian':
                $tingkat = (int) ($destinasi['keramaian'] ?? 3);
                $label = self::LABELS_KERAMAIAN[$tingkat] ?? 'sedang';
                return "Tingkat keramaian {$label}, sesuai pilihanmu";

            case 'fasilitas':
                $tingkat = (int) ($destinasi['fasilitas'] ?? 3);
                $label = self::LABELS_FASILITAS[$tingkat] ?? 'cukup';
                return "Fasilitas {$label}, sesuai pilihanmu";

            default:
                return null;
        }
    }
}
