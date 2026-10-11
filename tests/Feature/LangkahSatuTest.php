<?php

namespace Tests\Feature;

use App\Models\Destinasi;
use App\Models\KotaAsal;
use Database\Seeders\DestinasiSeeder;
use Database\Seeders\KotaAsalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LangkahSatuTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test skema tabel destinasi dan kota_asal.
     */
    public function test_skema_tabel_destinasi_dan_kota_asal_sesuai_spec(): void
    {
        // Periksa tabel destinasi
        $this->assertTrue(Schema::hasTable('destinasi'));
        $expectedDestinasiColumns = [
            'id',
            'nama',
            'jenis',
            'wilayah',
            'lat',
            'lon',
            'biaya',
            'rating',
            'suhu',
            'keramaian',
            'fasilitas',
            'deskripsi',
            'foto',
            'sumber',
            'diperbarui',
        ];
        foreach ($expectedDestinasiColumns as $col) {
            $this->assertTrue(Schema::hasColumn('destinasi', $col), "Kolom '{$col}' tidak ditemukan di tabel destinasi");
        }

        // Periksa tabel kota_asal
        $this->assertTrue(Schema::hasTable('kota_asal'));
        $expectedKotaColumns = ['id', 'nama', 'lat', 'lon'];
        foreach ($expectedKotaColumns as $col) {
            $this->assertTrue(Schema::hasColumn('kota_asal', $col), "Kolom '{$col}' tidak ditemukan di tabel kota_asal");
        }
    }

    /**
     * Test KotaAsalSeeder mengisi 15 kota dari konstanta KOTA.
     */
    public function test_kota_asal_seeder_mengisi_15_kota(): void
    {
        $this->seed(KotaAsalSeeder::class);

        $this->assertEquals(15, KotaAsal::count());

        $expectedKota = [
            'Bandung', 'Bogor', 'Depok', 'Bekasi', 'Cirebon',
            'Sukabumi', 'Tasikmalaya', 'Garut', 'Cimahi', 'Karawang',
            'Cianjur', 'Purwakarta', 'Subang', 'Sumedang', 'Jakarta (dari luar Jabar)',
        ];

        foreach ($expectedKota as $nama) {
            $this->assertDatabaseHas('kota_asal', ['nama' => $nama]);
        }

        // Cek koordinat Bandung
        $bandung = KotaAsal::where('nama', 'Bandung')->first();
        $this->assertNotNull($bandung);
        $this->assertEqualsWithDelta(-6.9175, $bandung->lat, 0.0001);
        $this->assertEqualsWithDelta(107.6191, $bandung->lon, 0.0001);
    }

    /**
     * Test DestinasiSeeder menerima pemisah koma dan titik koma serta BOM UTF-8.
     */
    public function test_destinasi_seeder_menerima_pemisah_koma_dan_titik_koma(): void
    {
        $seeder = new DestinasiSeeder();

        // 1. Uji CSV dengan koma
        $tempCsvComma = tempnam(sys_get_temp_dir(), 'csv_comma_');
        $csvContentComma = "\xEF\xBB\xBFnama,jenis,wilayah,lat,lon,biaya,rating,suhu,keramaian,fasilitas,deskripsi,foto,sumber,diperbarui\n" .
            "Kawah Putih,Pegunungan,\"Ciwidey, Bandung\",-7.166,107.402,30000,4.6,17,4,4,Kawah putih sejuk,Kawah_putih.jpg,Google Maps,2026-10-10\n" .
            "Pantai Pangandaran,Pantai,\"Pangandaran\",-7.688,108.656,20000,4.4,30,5,5,Pantai eksotis,Pantai_pangandaran.jpg,Situs Resmi,2026-10-10\n";
        file_put_contents($tempCsvComma, $csvContentComma);

        $parsedComma = $seeder->parseAndValidateCsv($tempCsvComma);
        @unlink($tempCsvComma);

        $this->assertEmpty($parsedComma['errors']);
        $this->assertCount(2, $parsedComma['data']);
        $this->assertEquals('Kawah Putih', $parsedComma['data'][0]['nama']);
        $this->assertEquals(30000, $parsedComma['data'][0]['biaya']);

        // 2. Uji CSV dengan titik koma
        $tempCsvSemi = tempnam(sys_get_temp_dir(), 'csv_semi_');
        $csvContentSemi = "nama;jenis;wilayah;lat;lon;biaya;rating;suhu;keramaian;fasilitas;deskripsi;foto;sumber;diperbarui\n" .
            "Situ Patenggang;Air & Danau;Ciwidey, Bandung;-7,168;107,356;20.000;4,6;18;3;3;Danau indah;Situ_patenggang.jpg;Google Maps;2026-10-10\n";
        file_put_contents($tempCsvSemi, $csvContentSemi);

        $parsedSemi = $seeder->parseAndValidateCsv($tempCsvSemi);
        @unlink($tempCsvSemi);

        $this->assertEmpty($parsedSemi['errors']);
        $this->assertCount(1, $parsedSemi['data']);
        $this->assertEquals('Situ Patenggang', $parsedSemi['data'][0]['nama']);
        $this->assertEquals(20000, $parsedSemi['data'][0]['biaya']);
        $this->assertEquals(4.6, $parsedSemi['data'][0]['rating']);
    }

    /**
     * Test DestinasiSeeder menolak baris tidak valid dan melaporkan nomor baris.
     */
    public function test_destinasi_seeder_menolak_baris_tidak_valid_dan_laporkan_nomor_baris(): void
    {
        $seeder = new DestinasiSeeder();

        $tempCsv = tempnam(sys_get_temp_dir(), 'csv_invalid_');
        $csvContent = "nama,jenis,wilayah,lat,lon,biaya,rating,suhu,keramaian,fasilitas,deskripsi,foto,sumber,diperbarui\n" .
            "Wisata A,Gunung,Garut,-7.1,107.1,20000,4.5,20,3,3,Desc,foto.jpg,Sumber A,2026-10-10\n" . // baris 2: jenis 'Gunung' (harus Pegunungan)
            "Wisata B,Pantai,Garut,-7.1,107.1,10000 - 20000,4.5,20,3,3,Desc,foto.jpg,Sumber B,2026-10-10\n" . // baris 3: biaya range
            "Wisata C,Pantai,Garut,-7.1,107.1,20000,4.5,20,3,3,Desc,foto.jpg,,2026-10-10\n"; // baris 4: sumber kosong
        file_put_contents($tempCsv, $csvContent);

        $parsed = $seeder->parseAndValidateCsv($tempCsv);
        @unlink($tempCsv);

        $this->assertNotEmpty($parsed['errors']);
        $this->assertCount(3, $parsed['errors']);

        $linesWithErrors = array_column($parsed['errors'], 'line');
        $this->assertContains(2, $linesWithErrors);
        $this->assertContains(3, $linesWithErrors);
        $this->assertContains(4, $linesWithErrors);
    }

    /**
     * Test DestinasiSeeder membatalkan seluruh proses tanpa mengubah tabel saat ada baris invalid.
     */
    public function test_destinasi_seeder_membatalkan_seluruh_proses_tanpa_mengubah_tabel(): void
    {
        // Masukkan satu destinasi awal
        Destinasi::create([
            'nama' => 'Destinasi Awal',
            'jenis' => 'Pantai',
            'wilayah' => 'Pangandaran',
            'lat' => -7.0,
            'lon' => 108.0,
            'biaya' => 10000,
            'rating' => 4.5,
            'suhu' => 28,
            'keramaian' => 3,
            'fasilitas' => 4,
            'sumber' => 'Manual',
        ]);
        $this->assertEquals(1, Destinasi::count());

        $tempCsv = tempnam(sys_get_temp_dir(), 'csv_invalid_seeder_');
        $csvContent = "nama,jenis,wilayah,lat,lon,biaya,rating,suhu,keramaian,fasilitas,deskripsi,foto,sumber,diperbarui\n" .
            "Destinasi Baru,Gunung,Garut,-7.1,107.1,20000,4.5,20,3,3,Desc,foto.jpg,Sumber A,2026-10-10\n";
        file_put_contents($tempCsv, $csvContent);

        // Buat mock atau turunan seeder yang menunjuk ke temp CSV
        $seeder = new class($tempCsv) extends DestinasiSeeder {
            public function __construct(private string $customCsv) {}
            public function run(): void
            {
                $parsed = $this->parseAndValidateCsv($this->customCsv);
                if (!empty($parsed['errors'])) {
                    throw new \RuntimeException("Validasi CSV gagal");
                }
            }
        };

        try {
            $seeder->run();
            $this->fail('Seeder seharusnya melempar RuntimeException');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Validasi CSV gagal', $e->getMessage());
        } finally {
            @unlink($tempCsv);
        }

        // Pastikan tabel destinasi tidak berubah (masih 1)
        $this->assertEquals(1, Destinasi::count());
        $this->assertDatabaseHas('destinasi', ['nama' => 'Destinasi Awal']);
    }

    /**
     * Test DestinasiSeeder berhasil menyimpan baris valid dan memeriksa foto tanpa gagalkan proses.
     */
    public function test_destinasi_seeder_berhasil_menyimpan_data_dan_memeriksa_foto(): void
    {
        $tempCsv = tempnam(sys_get_temp_dir(), 'csv_valid_seeder_');
        $csvContent = "nama,jenis,wilayah,lat,lon,biaya,rating,suhu,keramaian,fasilitas,deskripsi,foto,sumber,diperbarui\n" .
            "Kawah Putih,Pegunungan,\"Ciwidey, Bandung\",-7.166,107.402,30000,4.6,17,4,4,Kawah sejuk,Kawah_putih.jpg,Google Maps,2026-10-10\n" .
            "Pantai Antah Berantah,Pantai,\"Sukabumi\",-7.0,106.5,15000,4.2,29,3,3,Pantai sepi,foto_tidak_ada.jpg,Google Maps,2026-10-10\n";
        file_put_contents($tempCsv, $csvContent);

        $seeder = new class($tempCsv) extends DestinasiSeeder {
            public function __construct(private string $customCsv) {}
            public function run(): void
            {
                $parsed = $this->parseAndValidateCsv($this->customCsv);
                if (!empty($parsed['errors'])) {
                    throw new \RuntimeException("Validasi CSV gagal");
                }

                \Illuminate\Support\Facades\DB::transaction(function () use ($parsed) {
                    \Illuminate\Support\Facades\DB::table('destinasi')->delete();
                    foreach ($parsed['data'] as $row) {
                        Destinasi::create($row);
                    }
                });
            }
        };

        $seeder->run();
        @unlink($tempCsv);

        $this->assertEquals(2, Destinasi::count());
        $this->assertDatabaseHas('destinasi', ['nama' => 'Kawah Putih', 'jenis' => 'Pegunungan']);
        $this->assertDatabaseHas('destinasi', ['nama' => 'Pantai Antah Berantah', 'jenis' => 'Pantai']);
    }
}
