<?php

namespace Tests\Feature;

use App\Models\Destinasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HalamanTest extends TestCase
{
    use RefreshDatabase;

    protected Destinasi $destinasi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->destinasi = Destinasi::create([
            'nama' => 'Kawah Putih Ciwidey',
            'jenis' => 'Pegunungan',
            'wilayah' => 'Ciwidey, Kab. Bandung',
            'lat' => -7.166,
            'lon' => 107.402,
            'biaya' => 30000,
            'rating' => 4.8,
            'suhu' => 17,
            'keramaian' => 3,
            'fasilitas' => 4,
            'deskripsi' => 'Danau kawah vulkanik belerang putih kehijauan.',
            'foto' => 'kawah_putih.jpg',
            'sumber' => 'Disparbud',
            'diperbarui' => '2025-01-01',
        ]);
    }

    public function test_halaman_beranda_mengembalikan_status_200(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Jelajah Jabar');
        $response->assertSee('Destinasi Populer');
        $response->assertSee('Kawah Putih Ciwidey');
    }

    public function test_halaman_katalog_mengembalikan_status_200(): void
    {
        $response = $this->get('/katalog');

        $response->assertStatus(200);
        $response->assertSee('Katalog Wisata');
        $response->assertSee('Kawah Putih Ciwidey');
    }

    public function test_halaman_katalog_dengan_filter_jenis_mengembalikan_status_200(): void
    {
        $response = $this->get('/katalog?jenis=Pegunungan');

        $response->assertStatus(200);
        $response->assertSee('Kawah Putih Ciwidey');

        $responsePantai = $this->get('/katalog?jenis=Pantai');
        $responsePantai->assertStatus(200);
        $responsePantai->assertDontSee('Kawah Putih Ciwidey');
    }

    public function test_halaman_detail_destinasi_dengan_id_valid_mengembalikan_status_200(): void
    {
        $response = $this->get('/destinasi/' . $this->destinasi->id);

        $response->assertStatus(200);
        $response->assertSee('Kawah Putih Ciwidey');
        $response->assertSee('Pesona Keindahan Alam');
        $response->assertSee('Informasi &amp; Tarif', false);
        $response->assertSee('Daya Tarik Utama Destinasi');
        $response->assertSee('30.000');
    }

    public function test_halaman_detail_destinasi_dengan_id_tidak_ada_mengembalikan_status_404(): void
    {
        $response = $this->get('/destinasi/9999');

        $response->assertStatus(404);
    }

    public function test_halaman_rekomendasi_mengembalikan_status_200_dan_tidak_memuat_skor_sebelum_pencarian(): void
    {
        $response = $this->get('/rekomendasi');

        $response->assertStatus(200);
        $response->assertSee('Cari Rekomendasi');
        $response->assertSee('Rekomendasi Wisata untukmu');
        $response->assertSee('Ringkasan Preferensi Aktif');
        $response->assertSee('Prioritas Penilaian');
        $response->assertSee('Destinasi Populer Siap Dikalkulasi');
        // Kartu destinasi populer boleh ada di database
        $response->assertSee('Kawah Putih Ciwidey');
        // Pastikan peringkat hasil rekomendasi, nilai Q, dan persen kecocokan belum ada sebelum pencarian
        $response->assertDontSee('#1 Terbaik');
        $response->assertDontSee('Q = 0.');
        $response->assertDontSee('100.0%');
    }
}

