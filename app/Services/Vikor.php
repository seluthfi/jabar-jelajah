<?php

namespace App\Services;

use InvalidArgumentException;

class Vikor
{
    /**
     * Menghitung peringkat rekomendasi menggunakan metode VIKOR.
     *
     * @param array $data Array asosiatif destinasi (sudah difilter & memuat 'jarak')
     * @param array $kriteria Array kriteria: ['key' => ..., 'tipe' => 'cost'|'benefit'|'target', 'bobot' => ..., 'target' => ...]
     * @param float $v Bobot strategi kompromi (0.0 sampai 1.0)
     * @return array Hasil pemeringkatan lengkap sesuai kontrak SPEC
     *
     * @throws InvalidArgumentException Jika data kosong atau total bobot <= 0
     */
    public static function hitung(array $data, array $kriteria, float $v): array
    {
        // Validasi masukan dasar
        if (empty($data)) {
            throw new InvalidArgumentException("Data destinasi tidak boleh kosong.");
        }
        if (empty($kriteria)) {
            throw new InvalidArgumentException("Kriteria tidak boleh kosong.");
        }

        // 1. Normalisasi Bobot: w[j] = bobot[j] / jumlah bobot
        $totalBobot = 0.0;
        foreach ($kriteria as $k) {
            $totalBobot += (float) ($k['bobot'] ?? 0.0);
        }

        if ($totalBobot <= 0.0) {
            throw new InvalidArgumentException("Total bobot kriteria harus lebih besar dari 0.");
        }

        $bobotTernormalisasi = [];
        foreach ($kriteria as $k) {
            $key = $k['key'];
            $bobotTernormalisasi[$key] = ((float) $k['bobot']) / $totalBobot;
        }

        // 2. Bentuk Matriks Keputusan X[i][j]
        // Untuk tipe 'target': nilai yang dipakai adalah selisih mutlak |nilai - target| (diperlakukan sebagai cost)
        $X = [];
        foreach ($data as $i => $row) {
            foreach ($kriteria as $k) {
                $key = $k['key'];
                $val = (float) ($row[$key] ?? 0.0);
                $tipe = $k['tipe'] ?? 'cost';

                if ($tipe === 'target') {
                    $target = (float) ($k['target'] ?? 0.0);
                    $X[$i][$key] = abs($val - $target);
                } else {
                    $X[$i][$key] = $val;
                }
            }
        }

        // 3. Tentukan Nilai Terbaik f* dan Terburuk f- per Kriteria
        // - benefit: f* = nilai maksimum (makin besar makin baik), f- = nilai minimum
        // - cost & target: f* = nilai minimum (makin kecil makin baik), f- = nilai maksimum
        $fBest = [];
        $fWorst = [];
        foreach ($kriteria as $k) {
            $key = $k['key'];
            $tipe = $k['tipe'] ?? 'cost';
            $colValues = array_column($X, $key);

            if ($tipe === 'benefit') {
                $fBest[$key] = max($colValues);
                $fWorst[$key] = min($colValues);
            } else {
                $fBest[$key] = min($colValues);
                $fWorst[$key] = max($colValues);
            }
        }

        // 4. Hitung Jarak Ternormalisasi: d[i][j] = (f*[j] - X[i][j]) / (f*[j] - f-[j])
        // Jika f*[j] == f-[j] (semua baris bernilai sama), maka d = 0 untuk menghindari pembagian dengan nol
        $d = [];
        foreach ($data as $i => $row) {
            foreach ($kriteria as $k) {
                $key = $k['key'];
                $penyebut = $fBest[$key] - $fWorst[$key];

                if (abs($penyebut) < 1e-12) {
                    $d[$i][$key] = 0.0;
                } else {
                    $d[$i][$key] = ($fBest[$key] - $X[$i][$key]) / $penyebut;
                }
            }
        }

        // 5. Hitung Nilai Utilitas S[i] dan Nilai Penyesalan R[i]
        // S[i] = Σ (w[j] * d[i][j])  --> mengukur utilitas kepuasan mayoritas kriteria
        // R[i] = max (w[j] * d[i][j]) --> mengukur penyesalan maksimum / kelemahan terburuk
        $S = [];
        $R = [];
        foreach ($data as $i => $row) {
            $sumS = 0.0;
            $maxR = 0.0;

            foreach ($kriteria as $k) {
                $key = $k['key'];
                $wd = $bobotTernormalisasi[$key] * $d[$i][$key];
                $sumS += $wd;
                if ($wd > $maxR) {
                    $maxR = $wd;
                }
            }

            $S[$i] = $sumS;
            $R[$i] = $maxR;
        }

        // 6. Hitung Indeks Kompromi Q[i]
        // Q[i] = v * (S[i] - S*) / (S- - S*) + (1 - v) * (R[i] - R*) / (R- - R*)
        // Jika penyebut 0 (semua S sama atau semua R sama), suku tersebut bernilai 0.
        // Jika hanya 1 kandidat (m = 1), Q[i] langsung bernilai 0.
        $sMin = min($S);
        $sMax = max($S);
        $rMin = min($R);
        $rMax = max($R);

        $m = count($data);
        $Q = [];
        foreach ($data as $i => $row) {
            if ($m === 1) {
                $Q[$i] = 0.0;
            } else {
                $rentangS = $sMax - $sMin;
                $sukuS = abs($rentangS) < 1e-12 ? 0.0 : $v * (($S[$i] - $sMin) / $rentangS);

                $rentangR = $rMax - $rMin;
                $sukuR = abs($rentangR) < 1e-12 ? 0.0 : (1.0 - $v) * (($R[$i] - $rMin) / $rentangR);

                $Q[$i] = $sukuS + $sukuR;
            }
        }

        // 7. Urutkan Hasil:
        // Prioritas pengurutan: Q naik (makin kecil makin baik).
        // Tie-breaker: S naik, lalu rating turun, lalu nama alfabetis.
        $items = [];
        foreach ($data as $i => $row) {
            $items[] = [
                'data' => $row,
                'S' => $S[$i],
                'R' => $R[$i],
                'Q' => $Q[$i],
                'd' => $d[$i],
            ];
        }

        usort($items, function ($a, $b) {
            if (abs($a['Q'] - $b['Q']) > 1e-9) {
                return $a['Q'] <=> $b['Q'];
            }
            if (abs($a['S'] - $b['S']) > 1e-9) {
                return $a['S'] <=> $b['S'];
            }
            $ratingA = (float) ($a['data']['rating'] ?? 0.0);
            $ratingB = (float) ($b['data']['rating'] ?? 0.0);
            if (abs($ratingA - $ratingB) > 1e-9) {
                return $ratingB <=> $ratingA;
            }
            $namaA = (string) ($a['data']['nama'] ?? '');
            $namaB = (string) ($b['data']['nama'] ?? '');
            return strcmp($namaA, $namaB);
        });

        // 8. Uji Dua Syarat Kompromi VIKOR (hanya jika m >= 2)
        // Syarat 1 (Keunggulan / Acceptable Advantage): Q(A2) - Q(A1) >= 1 / (m - 1)
        // Syarat 2 (Kestabilan / Acceptable Stability): A1 terbaik di S atau R (S(A1) == min S atau R(A1) == min R)
        $dq = $m > 1 ? (1.0 / ($m - 1)) : 0.0;
        $keunggulan = true;
        $kestabilan = true;
        $mode = 'tunggal';

        if ($m >= 2) {
            $a1 = $items[0];
            $a2 = $items[1];
            $selisihQ = $a2['Q'] - $a1['Q'];

            // Uji keunggulan dengan batas toleransi 1e-9
            $keunggulan = ($selisihQ >= $dq - 1e-9);

            // Uji kestabilan: A1 memiliki S minimum atau R minimum
            $kestabilan = (abs($a1['S'] - $sMin) < 1e-9 || abs($a1['R'] - $rMin) < 1e-9);

            // Penentuan mode:
            // - Jika kedua syarat terpenuhi -> 'tunggal'
            // - Jika keunggulan gagal -> 'setara'
            // - Jika keunggulan lolos tapi kestabilan gagal -> 'dua_teratas'
            if ($keunggulan && $kestabilan) {
                $mode = 'tunggal';
            } elseif (!$keunggulan) {
                $mode = 'setara';
            } else {
                $mode = 'dua_teratas';
            }
        }

        // Tentukan status 'utama' untuk setiap destinasi
        $qA1 = $items[0]['Q'];
        $urut = [];
        foreach ($items as $rank => $item) {
            $peringkat = $rank + 1;
            $isUtama = false;

            if ($mode === 'tunggal') {
                $isUtama = ($peringkat === 1);
            } elseif ($mode === 'dua_teratas') {
                $isUtama = ($peringkat === 1 || $peringkat === 2);
            } elseif ($mode === 'setara') {
                // Semua destinasi dengan Q - Q(A1) < 1 / (m - 1)
                $isUtama = (($item['Q'] - $qA1) < $dq - 1e-9);
            }

            $urut[] = array_merge($item['data'], [
                'S' => $item['S'],
                'R' => $item['R'],
                'Q' => $item['Q'],
                'd' => $item['d'],
                'peringkat' => $peringkat,
                'utama' => $isUtama,
            ]);
        }

        return [
            'urut' => $urut,
            'Smin' => $sMin,
            'Smax' => $sMax,
            'Rmin' => $rMin,
            'Rmax' => $rMax,
            'bobotTernormalisasi' => $bobotTernormalisasi,
            'DQ' => $dq,
            'keunggulan' => $keunggulan,
            'kestabilan' => $kestabilan,
            'mode' => $mode,
        ];
    }
}
