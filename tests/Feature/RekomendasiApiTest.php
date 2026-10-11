<?php

namespace Tests\Feature;

use App\Models\Destinasi;
use App\Models\KotaAsal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RekomendasiApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan kota asal keberangkatan
        KotaAsal::create(['nama' => 'Bandung', 'lat' => -6.9175, 'lon' => 107.6191]);
        KotaAsal::create(['nama' => 'Bogor', 'lat' => -6.5950, 'lon' => 106.8166]);

        // Siapkan 3 destinasi buatan sesuai SPEC
        Destinasi::create([
            'nama' => 'Kawah Putih Ciwidey',
            'jenis' => 'Pegunungan',
            'wilayah' => 'Ciwidey, Kab. Bandung',
            'lat' => -7.166,
            'lon' => 107.402,
            'biaya' => 30000,
            'rating' => 4.6,
            'suhu' => 17,
            'keramaian' => 4,
            'fasilitas' => 4,
            'deskripsi' => 'Danau kawah berair putih kehijauan.',
            'foto' => 'Kawah_putih.jpg',
            'sumber' => 'Google Maps',
        ]);

        Destinasi::create([
            'nama' => 'Pantai Pangandaran',
            'jenis' => 'Pantai',
            'wilayah' => 'Kab. Pangandaran',
            'lat' => -7.688,
            'lon' => 108.656,
            'biaya' => 20000,
            'rating' => 4.4,
            'suhu' => 30,
            'keramaian' => 5,
            'fasilitas' => 5,
            'deskripsi' => 'Pantai pasir putih eksotis.',
            'foto' => 'Pantai_pangandaran.jpg',
            'sumber' => 'Google Maps',
        ]);

        Destinasi::create([
            'nama' => 'Curug Cimahi',
            'jenis' => 'Air & Danau',
            'wilayah' => 'Cisarua, Kab. Bandung Barat',
            'lat' => -6.806,
            'lon' => 107.555,
            'biaya' => 20000,
            'rating' => 4.4,
            'suhu' => 21,
            'keramaian' => 3,
            'fasilitas' => 4,
            'deskripsi' => 'Air terjun alami pelangi.',
            'foto' => 'Curug_cimahi.jpg',
            'sumber' => 'Google Maps',
        ]);
    }

    /**
     * Test POST /api/rekomendasi mengembalikan 200 dengan urut terurut naik menurut Q.
     * Peringkat 1 memiliki persen = 100.0, dan tiap item memiliki persen = round((1 - Q) * 100, 1).
     * jumlahKandidat sama dengan banyak baris lolos filter dan totalAktif sama dengan jumlah baris tabel.
     */
    public function test_post_rekomendasi_berhasil_dan_terurut_menurut_q(): void
    {
        $payload = [
            'jenis' => ['Pegunungan', 'Air & Danau', 'Pantai'],
            'asal' => 'Bandung',
            'maxBiaya' => 50000,
            'target' => [
                'jarak' => 40,
                'suhu' => 20,
                'keramaian' => 3,
                'fasilitas' => 4,
            ],
            'penting' => [
                'biaya' => 1,
                'rating' => 1,
                'jarak' => 2,
                'suhu' => 2,
                'keramaian' => 1,
                'fasilitas' => 1,
            ],
            'v' => 0.5,
        ];

        $response = $this->postJson('/api/rekomendasi', $payload);

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertArrayHasKey('mode', $json);
        $this->assertArrayHasKey('jumlahKandidat', $json);
        $this->assertArrayHasKey('totalAktif', $json);
        $this->assertArrayHasKey('asal', $json);
        $this->assertArrayHasKey('v', $json);
        $this->assertArrayHasKey('bobotTernormalisasi', $json);
        $this->assertArrayHasKey('DQ', $json);
        $this->assertArrayHasKey('keunggulan', $json);
        $this->assertArrayHasKey('kestabilan', $json);
        $this->assertArrayHasKey('urut', $json);

        // jumlahKandidat sama dengan yang lolos filter (3) dan totalAktif (3)
        $this->assertEquals(3, $json['jumlahKandidat']);
        $this->assertEquals(3, $json['totalAktif']);
        $this->assertEquals('Bandung', $json['asal']);
        $this->assertEquals(0.5, $json['v']);

        $urut = $json['urut'];
        $this->assertCount(3, $urut);

        // Terurut naik menurut Q
        for ($i = 0; $i < count($urut) - 1; $i++) {
            $this->assertLessThanOrEqual($urut[$i + 1]['Q'], $urut[$i]['Q']);
        }

        // Peringkat 1 memiliki persen = 100.0
        $this->assertEquals(100.0, $urut[0]['persen']);
        $this->assertEquals(1, $urut[0]['peringkat']);

        // Tiap item memiliki persen = round((1 - Q) * 100, 1)
        foreach ($urut as $item) {
            $expectedPersen = round((1.0 - (float) $item['Q']) * 100.0, 1);
            $this->assertEquals($expectedPersen, $item['persen']);
            $this->assertArrayHasKey('alasan', $item);
        }
    }

    /**
     * Test alasan berisi paling banyak 3 kalimat, tidak ada kalimat untuk kriteria dengan penting = 0,
     * dan kosong bila semua d > 0.4.
     */
    public function test_alasan_paling_banyak_3_dan_tidak_memuat_kriteria_dengan_penting_nol(): void
    {
        // Tetapkan kriteria biaya dan rating = 0, hanya jarak dan suhu yang penting
        $payload = [
            'jenis' => [],
            'asal' => 'Bandung',
            'maxBiaya' => null,
            'target' => [
                'jarak' => 10,
                'suhu' => 20,
                'keramaian' => 3,
                'fasilitas' => 4,
            ],
            'penting' => [
                'biaya' => 0,
                'rating' => 0,
                'jarak' => 3,
                'suhu' => 2,
                'keramaian' => 0,
                'fasilitas' => 0,
            ],
            'v' => 0.5,
        ];

        $response = $this->postJson('/api/rekomendasi', $payload);
        $response->assertStatus(200);

        $urut = $response->json('urut');
        foreach ($urut as $item) {
            $this->assertLessThanOrEqual(3, count($item['alasan']));
            foreach ($item['alasan'] as $kalimat) {
                // Tidak boleh memuat alasan tentang biaya, rating, keramaian, fasilitas karena bobotnya 0
                $this->assertStringNotContainsStringIgnoringCase('Tiket', $kalimat);
                $this->assertStringNotContainsStringIgnoringCase('Rating', $kalimat);
                $this->assertStringNotContainsStringIgnoringCase('Keramaian', $kalimat);
                $this->assertStringNotContainsStringIgnoringCase('Fasilitas', $kalimat);
            }
        }
    }

    /**
     * Test masukan tidak valid mengembalikan status 422:
     * - semua penting 0
     * - v = 2
     * - asal tidak dikenal
     * - jenis di luar 5 nilai
     */
    public function test_masukan_tidak_valid_mengembalikan_422(): void
    {
        // 1. Semua penting = 0
        $response1 = $this->postJson('/api/rekomendasi', [
            'asal' => 'Bandung',
            'v' => 0.5,
            'penting' => [
                'biaya' => 0, 'rating' => 0, 'jarak' => 0,
                'suhu' => 0, 'keramaian' => 0, 'fasilitas' => 0,
            ],
        ]);
        $response1->assertStatus(422);
        $response1->assertJsonValidationErrors(['penting']);

        // 2. v = 2 (di luar 0..1)
        $response2 = $this->postJson('/api/rekomendasi', [
            'asal' => 'Bandung',
            'v' => 2,
            'penting' => ['biaya' => 1, 'rating' => 1, 'jarak' => 1, 'suhu' => 1, 'keramaian' => 1, 'fasilitas' => 1],
        ]);
        $response2->assertStatus(422);
        $response2->assertJsonValidationErrors(['v']);

        // 3. asal tidak dikenal di tabel kota_asal
        $response3 = $this->postJson('/api/rekomendasi', [
            'asal' => 'Surabaya',
            'v' => 0.5,
            'penting' => ['biaya' => 1, 'rating' => 1, 'jarak' => 1, 'suhu' => 1, 'keramaian' => 1, 'fasilitas' => 1],
        ]);
        $response3->assertStatus(422);
        $response3->assertJsonValidationErrors(['asal']);

        // 4. jenis di luar 5 nilai
        $response4 = $this->postJson('/api/rekomendasi', [
            'asal' => 'Bandung',
            'v' => 0.5,
            'jenis' => ['Gunung & Kawah'],
            'penting' => ['biaya' => 1, 'rating' => 1, 'jarak' => 1, 'suhu' => 1, 'keramaian' => 1, 'fasilitas' => 1],
        ]);
        $response4->assertStatus(422);
        $response4->assertJsonValidationErrors(['jenis.0']);
    }

    /**
     * Test filter yang tidak meloloskan siapa pun mengembalikan 200 dengan mode: "kosong" dan urut: [].
     */
    public function test_filter_tidak_ada_yang_cocok_mengembalikan_mode_kosong(): void
    {
        $payload = [
            'jenis' => ['Pegunungan'],
            'asal' => 'Bandung',
            'maxBiaya' => 5000, // Tidak ada pegunungan dengan biaya <= 5000
            'penting' => ['biaya' => 1, 'rating' => 1, 'jarak' => 1, 'suhu' => 1, 'keramaian' => 1, 'fasilitas' => 1],
            'v' => 0.5,
        ];

        $response = $this->postJson('/api/rekomendasi', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'mode' => 'kosong',
            'jumlahKandidat' => 0,
            'totalAktif' => 3,
            'urut' => [],
        ]);
    }

    /**
     * Test endpoint katalog GET /api/destinasi, GET /api/destinasi/{id}, dan GET /api/kota.
     */
    public function test_endpoint_katalog_dan_kota(): void
    {
        // GET /api/destinasi
        $resAll = $this->getJson('/api/destinasi');
        $resAll->assertStatus(200);
        $this->assertCount(3, $resAll->json());

        // GET /api/destinasi?jenis=Pantai
        $resFilter = $this->getJson('/api/destinasi?jenis=Pantai');
        $resFilter->assertStatus(200);
        $this->assertCount(1, $resFilter->json());
        $this->assertEquals('Pantai Pangandaran', $resFilter->json('0.nama'));

        // GET /api/destinasi/{id} ada
        $firstId = Destinasi::first()->id;
        $resShow = $this->getJson("/api/destinasi/{$firstId}");
        $resShow->assertStatus(200);
        $this->assertEquals($firstId, $resShow->json('id'));

        // GET /api/destinasi/{id} 404
        $res404 = $this->getJson('/api/destinasi/99999');
        $res404->assertStatus(404);

        // GET /api/kota
        $resKota = $this->getJson('/api/kota');
        $resKota->assertStatus(200);
        $this->assertCount(2, $resKota->json());
    }
}
