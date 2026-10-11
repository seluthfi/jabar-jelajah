<?php

namespace Database\Seeders;

use App\Models\Destinasi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class DestinasiSeeder extends Seeder
{
    /**
     * Allowed enum values for 'jenis' as specified in SPEC.md
     */
    protected const ALLOWED_JENIS = [
        'Pantai',
        'Pegunungan',
        'Air & Danau',
        'Taman & Keluarga',
        'Budaya & Sejarah',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = base_path('data/destinasi.csv');
        if (!file_exists($csvPath)) {
            $fallbackPath = base_path('data/destinasi_wisata.csv');
            if (file_exists($fallbackPath)) {
                $csvPath = $fallbackPath;
            } else {
                throw new RuntimeException("File CSV tidak ditemukan di: data/destinasi.csv atau data/destinasi_wisata.csv");
            }
        }

        $parsed = $this->parseAndValidateCsv($csvPath);

        if (!empty($parsed['errors'])) {
            $errorSummary = "Ditemukan " . count($parsed['errors']) . " baris tidak valid pada file CSV:\n";
            foreach ($parsed['errors'] as $error) {
                $errorSummary .= " - Baris {$error['line']}: {$error['message']}\n";
            }
            $errorSummary .= "Seluruh proses seeding dibatalkan tanpa mengubah tabel 'destinasi'.";

            if ($this->command) {
                $this->command->error($errorSummary);
            }

            throw new RuntimeException($errorSummary);
        }

        $records = $parsed['data'];

        // Kosongkan tabel destinasi lalu masukkan data secara atomik
        DB::transaction(function () use ($records) {
            DB::table('destinasi')->delete();

            foreach ($records as $record) {
                Destinasi::create($record);
            }
        });

        if ($this->command) {
            $this->command->info("DestinasiSeeder berhasil memasukkan " . count($records) . " destinasi.");
        }

        // Periksa keberadaan foto di public/images/destinasi/
        $missingPhotos = [];
        $imagesDir = public_path('images/destinasi');

        foreach ($records as $record) {
            $foto = trim($record['foto'] ?? '');
            if ($foto !== '') {
                $fotoPath = $imagesDir . DIRECTORY_SEPARATOR . $foto;
                if (!File::exists($fotoPath)) {
                    $missingPhotos[] = [
                        'nama' => $record['nama'],
                        'foto' => $foto,
                    ];
                }
            } else {
                $missingPhotos[] = [
                    'nama' => $record['nama'],
                    'foto' => '(tidak ada nama file foto)',
                ];
            }
        }

        if (!empty($missingPhotos)) {
            if ($this->command) {
                $this->command->warn("\nDaftar destinasi yang fotonya tidak ada di public/images/destinasi/ (" . count($missingPhotos) . " destinasi):");
                foreach ($missingPhotos as $item) {
                    $this->command->line(" - {$item['nama']}: {$item['foto']}");
                }
            }
        } else {
            if ($this->command) {
                $this->command->info("Semua foto destinasi ditemukan di public/images/destinasi/.");
            }
        }
    }

    /**
     * Membaca dan memvalidasi file CSV.
     * Mengembalikan array ['data' => [...], 'errors' => [...]]
     */
    public function parseAndValidateCsv(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new RuntimeException("Tidak dapat membaca file: {$filePath}");
        }

        // Buang BOM UTF-8 bila ada
        $bom = pack('H*', 'EFBBBF');
        $content = preg_replace("/^$bom/", '', $content);

        // Deteksi baris untuk menentukan baris awal dan delimiter
        $rawLines = preg_split('/\r\n|\r|\n/', $content);

        // Cari baris header dan tentukan delimiter
        $delimiter = ',';
        $headerLineIndex = -1;
        $headerMap = [];

        foreach ($rawLines as $idx => $line) {
            $lineTrim = trim($line);
            if ($lineTrim === '') {
                continue;
            }

            // Uji delimiter , atau ;
            $countComma = substr_count($line, ',');
            $countSemi = substr_count($line, ';');
            $testDelimiter = $countSemi > $countComma ? ';' : ',';

            $cols = str_getcsv($line, $testDelimiter);
            $normalizedCols = array_map(fn($c) => $this->normalizeColumnName($c), $cols);

            // Periksa apakah baris ini memuat kolom wajib minimal 'nama' dan 'jenis'
            if (in_array('nama', $normalizedCols, true) && in_array('jenis', $normalizedCols, true)) {
                $delimiter = $testDelimiter;
                $headerLineIndex = $idx;
                $headerMap = array_flip($normalizedCols);
                break;
            }
        }

        if ($headerLineIndex === -1) {
            return [
                'data' => [],
                'errors' => [
                    [
                        'line' => 1,
                        'message' => 'Header CSV tidak ditemukan atau tidak memuat kolom nama dan jenis.',
                    ],
                ],
            ];
        }

        // Baca file menggunakan fgetcsv untuk menangani multi-line string dengan benar
        $tempStream = fopen('php://memory', 'r+');
        fwrite($tempStream, $content);
        rewind($tempStream);

        $currentLine = 1;
        $dataRows = [];
        $errors = [];
        $existingNames = [];

        while (($row = fgetcsv($tempStream, 0, $delimiter, '"', '\\')) !== false) {
            // Hitung nomor baris sebenarnya
            // fgetcsv bisa memakan beberapa baris fisik jika ada newline di dalam tanda kutip
            $rowPhysicalLines = 1;
            foreach ($row as $cell) {
                if (is_string($cell)) {
                    $rowPhysicalLines += substr_count($cell, "\n");
                }
            }

            $lineNum = $currentLine;
            $currentLine += $rowPhysicalLines;

            // Abaikan baris sebelum atau sama dengan header
            if ($lineNum <= $headerLineIndex + 1) {
                continue;
            }

            // Cek apakah baris kosong
            $allEmpty = true;
            foreach ($row as $val) {
                if (trim($val ?? '') !== '') {
                    $allEmpty = false;
                    break;
                }
            }
            if ($allEmpty) {
                continue;
            }

            // Ambil nilai per kolom
            $getValue = function (string $key) use ($row, $headerMap): ?string {
                if (isset($headerMap[$key]) && isset($row[$headerMap[$key]])) {
                    return trim($row[$headerMap[$key]]);
                }
                return null;
            };

            $nama = $getValue('nama') ?? '';
            $jenis = $getValue('jenis') ?? '';
            $wilayah = $getValue('wilayah') ?? '';
            $latRaw = $getValue('lat') ?? '';
            $lonRaw = $getValue('lon') ?? '';
            $biayaRaw = $getValue('biaya') ?? '';
            $ratingRaw = $getValue('rating') ?? '';
            $suhuRaw = $getValue('suhu') ?? '';
            $keramaianRaw = $getValue('keramaian') ?? '';
            $fasilitasRaw = $getValue('fasilitas') ?? '';
            $deskripsi = $getValue('deskripsi');
            $foto = $getValue('foto');
            $sumber = $getValue('sumber') ?? '';
            $diperbarui = $getValue('diperbarui');

            $rowErrors = [];

            // 1. Validasi nama (wajib, unik)
            if ($nama === '') {
                $rowErrors[] = "Kolom 'nama' wajib diisi";
            } elseif (isset($existingNames[mb_strtolower($nama)])) {
                $rowErrors[] = "Nama '{$nama}' duplikat (sudah muncul di baris {$existingNames[mb_strtolower($nama)]})";
            } else {
                $existingNames[mb_strtolower($nama)] = $lineNum;
            }

            // 2. Validasi jenis (hanya 5 nilai enum)
            if (!in_array($jenis, self::ALLOWED_JENIS, true)) {
                $rowErrors[] = "Kolom 'jenis' bernilai '{$jenis}', harus salah satu dari: " . implode(', ', self::ALLOWED_JENIS);
            }

            // 3. Validasi wilayah (wajib)
            if ($wilayah === '') {
                $rowErrors[] = "Kolom 'wilayah' wajib diisi";
            }

            // 4. Validasi lat & lon (angka, format desimal koma/titik)
            $lat = $this->parseDecimal($latRaw);
            if ($lat === null || $lat < -90 || $lat > 90) {
                $rowErrors[] = "Kolom 'lat' bernilai '{$latRaw}' tidak valid (harus desimal antara -90 dan 90)";
            }

            $lon = $this->parseDecimal($lonRaw);
            if ($lon === null || $lon < -180 || $lon > 180) {
                $rowErrors[] = "Kolom 'lon' bernilai '{$lonRaw}' tidak valid (harus desimal antara -180 dan 180)";
            }

            // 5. Validasi biaya (unsigned integer >= 0)
            $biaya = $this->parseUnsignedInteger($biayaRaw);
            if ($biaya === null || $biaya < 0) {
                $rowErrors[] = "Kolom 'biaya' bernilai '{$biayaRaw}' tidak valid (harus bilangan bulat non-negatif)";
            }

            // 6. Validasi rating (1.0 sampai 5.0)
            $rating = $this->parseDecimal($ratingRaw);
            if ($rating === null || $rating < 1.0 || $rating > 5.0) {
                $rowErrors[] = "Kolom 'rating' bernilai '{$ratingRaw}' tidak valid (harus desimal 1.0 sampai 5.0)";
            }

            // 7. Validasi suhu (integer Celsius)
            $suhu = $this->parseInteger($suhuRaw);
            if ($suhu === null) {
                $rowErrors[] = "Kolom 'suhu' bernilai '{$suhuRaw}' tidak valid (harus bilangan bulat)";
            }

            // 8. Validasi keramaian (unsigned tinyint 1 sampai 5)
            $keramaian = $this->parseInteger($keramaianRaw);
            if ($keramaian === null || $keramaian < 1 || $keramaian > 5) {
                $rowErrors[] = "Kolom 'keramaian' bernilai '{$keramaianRaw}' tidak valid (harus angka 1 sampai 5)";
            }

            // 9. Validasi fasilitas (unsigned tinyint 1 sampai 5)
            $fasilitas = $this->parseInteger($fasilitasRaw);
            if ($fasilitas === null || $fasilitas < 1 || $fasilitas > 5) {
                $rowErrors[] = "Kolom 'fasilitas' bernilai '{$fasilitasRaw}' tidak valid (harus angka 1 sampai 5)";
            }

            // 10. Validasi sumber (wajib)
            if ($sumber === '') {
                $rowErrors[] = "Kolom 'sumber' wajib diisi (baris tanpa sumber ditolak)";
            }

            if (!empty($rowErrors)) {
                $errors[] = [
                    'line' => $lineNum,
                    'message' => implode('; ', $rowErrors),
                ];
            } else {
                $dataRows[] = [
                    'nama' => $nama,
                    'jenis' => $jenis,
                    'wilayah' => $wilayah,
                    'lat' => $lat,
                    'lon' => $lon,
                    'biaya' => $biaya,
                    'rating' => round($rating, 1),
                    'suhu' => $suhu,
                    'keramaian' => $keramaian,
                    'fasilitas' => $fasilitas,
                    'deskripsi' => $deskripsi !== '' ? $deskripsi : null,
                    'foto' => $foto !== '' ? $foto : null,
                    'sumber' => $sumber,
                    'diperbarui' => $diperbarui !== '' ? $diperbarui : null,
                ];
            }
        }

        fclose($tempStream);

        return [
            'data' => $dataRows,
            'errors' => $errors,
        ];
    }

    /**
     * Normalisasi nama header kolom ke kunci baku.
     */
    protected function normalizeColumnName(string $name): string
    {
        $clean = mb_strtolower(trim($name));
        $clean = preg_replace('/\s+/', ' ', $clean);

        if (str_contains($clean, 'nama')) {
            return 'nama';
        }
        if (str_contains($clean, 'jenis')) {
            return 'jenis';
        }
        if (str_contains($clean, 'wilayah')) {
            return 'wilayah';
        }
        if ($clean === 'lat' || str_contains($clean, 'latitude')) {
            return 'lat';
        }
        if ($clean === 'lon' || str_contains($clean, 'long') || str_contains($clean, 'longitude')) {
            return 'lon';
        }
        if (str_contains($clean, 'biaya') || str_contains($clean, 'harga')) {
            return 'biaya';
        }
        if (str_contains($clean, 'rating')) {
            return 'rating';
        }
        if (str_contains($clean, 'suhu')) {
            return 'suhu';
        }
        if (str_contains($clean, 'keramaian') || str_contains($clean, 'ramai')) {
            return 'keramaian';
        }
        if (str_contains($clean, 'fasilitas')) {
            return 'fasilitas';
        }
        if (str_contains($clean, 'deskripsi') || str_contains($clean, 'keterangan')) {
            return 'deskripsi';
        }
        if (str_contains($clean, 'foto') || str_contains($clean, 'gambar')) {
            return 'foto';
        }
        if (str_contains($clean, 'sumber')) {
            return 'sumber';
        }
        if (str_contains($clean, 'diperbarui') || str_contains($clean, 'tanggal')) {
            return 'diperbarui';
        }

        return $clean;
    }

    /**
     * Parse bilangan desimal (mendukung format 1,23 atau 1.23).
     */
    protected function parseDecimal(?string $val): ?float
    {
        if ($val === null || trim($val) === '') {
            return null;
        }

        $val = trim($val);
        // Ganti koma dengan titik jika merupakan pemisah desimal
        $val = str_replace(',', '.', $val);

        if (!is_numeric($val)) {
            return null;
        }

        return (float) $val;
    }

    /**
     * Parse unsigned integer (mendukung pemisah ribuan titik seperti "25.000").
     */
    protected function parseUnsignedInteger(?string $val): ?int
    {
        if ($val === null || trim($val) === '') {
            return null;
        }

        $val = trim($val);

        // Jika ada rentang seperti "10.000 - 25.000", tolak
        if (str_contains($val, '-') && !str_starts_with($val, '-')) {
            return null;
        }

        // Bersihkan pemisah ribuan berupa titik (contoh "25.000" -> "25000")
        // Catatan: Jika ada desimal seperti "25.0", tolak karena biaya harus bilangan bulat
        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $val)) {
            $val = str_replace('.', '', $val);
        }

        if (!ctype_digit($val)) {
            return null;
        }

        $int = (int) $val;
        return $int >= 0 ? $int : null;
    }

    /**
     * Parse integer (bisa positif/negatif).
     */
    protected function parseInteger(?string $val): ?int
    {
        if ($val === null || trim($val) === '') {
            return null;
        }

        $val = trim($val);

        if (!is_numeric($val) || (int) $val != $val) {
            return null;
        }

        return (int) $val;
    }
}
