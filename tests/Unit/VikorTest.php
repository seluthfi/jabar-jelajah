<?php

namespace Tests\Unit;

use App\Services\Vikor;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class VikorTest extends TestCase
{
    /**
     * Tes 1: tanpa target, 5 destinasi. v = 0,5.
     * Acuan SPEC bagian 8.
     */
    public function test_tes_1_tanpa_target_5_destinasi(): void
    {
        $data = [
            ['nama' => 'A', 'harga' => 5000, 'jarak' => 8, 'rating' => 4.3, 'fasilitas' => 4, 'keramaian' => 4],
            ['nama' => 'B', 'harga' => 10000, 'jarak' => 90, 'rating' => 4.6, 'fasilitas' => 3, 'keramaian' => 2],
            ['nama' => 'C', 'harga' => 0, 'jarak' => 3, 'rating' => 4.0, 'fasilitas' => 5, 'keramaian' => 5],
            ['nama' => 'D', 'harga' => 50000, 'jarak' => 40, 'rating' => 4.8, 'fasilitas' => 4, 'keramaian' => 3],
            ['nama' => 'E', 'harga' => 15000, 'jarak' => 15, 'rating' => 4.4, 'fasilitas' => 2, 'keramaian' => 1],
        ];

        $kriteria = [
            ['key' => 'harga', 'tipe' => 'cost', 'bobot' => 0.30],
            ['key' => 'jarak', 'tipe' => 'cost', 'bobot' => 0.20],
            ['key' => 'rating', 'tipe' => 'benefit', 'bobot' => 0.25],
            ['key' => 'fasilitas', 'tipe' => 'benefit', 'bobot' => 0.10],
            ['key' => 'keramaian', 'tipe' => 'cost', 'bobot' => 0.15],
        ];

        $v = 0.5;
        $hasil = Vikor::hitung($data, $kriteria, $v);

        // Petakan hasil berdasarkan nama destinasi
        $byNama = [];
        foreach ($hasil['urut'] as $item) {
            $byNama[$item['nama']] = $item;
        }

        // Nilai S, R, Q per destinasi (toleransi 0.0005)
        $this->assertEqualsWithDelta(0.3436, $byNama['A']['S'], 0.0005);
        $this->assertEqualsWithDelta(0.1563, $byNama['A']['R'], 0.0005);
        $this->assertEqualsWithDelta(0.0926, $byNama['A']['Q'], 0.0005);

        $this->assertEqualsWithDelta(0.4267, $byNama['B']['S'], 0.0005);
        $this->assertEqualsWithDelta(0.2000, $byNama['B']['R'], 0.0005);
        $this->assertEqualsWithDelta(0.4931, $byNama['B']['Q'], 0.0005);

        $this->assertEqualsWithDelta(0.4000, $byNama['C']['S'], 0.0005);
        $this->assertEqualsWithDelta(0.2500, $byNama['C']['R'], 0.0005);
        $this->assertEqualsWithDelta(0.5475, $byNama['C']['Q'], 0.0005);

        $this->assertEqualsWithDelta(0.4934, $byNama['D']['S'], 0.0005);
        $this->assertEqualsWithDelta(0.3000, $byNama['D']['R'], 0.0005);
        $this->assertEqualsWithDelta(1.0000, $byNama['D']['Q'], 0.0005);

        $this->assertEqualsWithDelta(0.3426, $byNama['E']['S'], 0.0005);
        $this->assertEqualsWithDelta(0.1250, $byNama['E']['R'], 0.0005);
        $this->assertEqualsWithDelta(0.0000, $byNama['E']['Q'], 0.0005);

        // Urutan: E, A, B, C, D
        $urutanNama = array_column($hasil['urut'], 'nama');
        $this->assertSame(['E', 'A', 'B', 'C', 'D'], $urutanNama);

        // Batas keunggulan 1/(m-1) = 0.25 dan selisih Q = 0.0926, jadi keunggulan gagal
        $this->assertEqualsWithDelta(0.25, $hasil['DQ'], 0.0005);
        $this->assertFalse($hasil['keunggulan']);
        $this->assertTrue($hasil['kestabilan']);
        $this->assertSame('setara', $hasil['mode']);

        // utama = E dan A
        $this->assertTrue($byNama['E']['utama']);
        $this->assertTrue($byNama['A']['utama']);
        $this->assertFalse($byNama['B']['utama']);
        $this->assertFalse($byNama['C']['utama']);
        $this->assertFalse($byNama['D']['utama']);
    }

    /**
     * Tes 2: dengan kriteria target, 4 destinasi. v = 0,5 dan v = 1.
     * Acuan SPEC bagian 8.
     */
    public function test_tes_2_dengan_kriteria_target_4_destinasi(): void
    {
        $data = [
            ['nama' => 'Alfa', 'biaya' => 10000, 'rating' => 4.0, 'jarak' => 50, 'suhu' => 18],
            ['nama' => 'Beta', 'biaya' => 20000, 'rating' => 4.5, 'jarak' => 100, 'suhu' => 22],
            ['nama' => 'Gama', 'biaya' => 30000, 'rating' => 4.8, 'jarak' => 20, 'suhu' => 28],
            ['nama' => 'Delta', 'biaya' => 15000, 'rating' => 4.2, 'jarak' => 80, 'suhu' => 20],
        ];

        $kriteria = [
            ['key' => 'biaya', 'tipe' => 'cost', 'bobot' => 1],
            ['key' => 'rating', 'tipe' => 'benefit', 'bobot' => 1],
            ['key' => 'jarak', 'tipe' => 'target', 'bobot' => 2, 'target' => 40],
            ['key' => 'suhu', 'tipe' => 'target', 'bobot' => 2, 'target' => 18],
        ];

        // Pengujian dengan v = 0.5
        $hasil = Vikor::hitung($data, $kriteria, 0.5);

        // Bobot ternormalisasi: 0,1667; 0,1667; 0,3333; 0,3333
        $this->assertEqualsWithDelta(0.1667, $hasil['bobotTernormalisasi']['biaya'], 0.0005);
        $this->assertEqualsWithDelta(0.1667, $hasil['bobotTernormalisasi']['rating'], 0.0005);
        $this->assertEqualsWithDelta(0.3333, $hasil['bobotTernormalisasi']['jarak'], 0.0005);
        $this->assertEqualsWithDelta(0.3333, $hasil['bobotTernormalisasi']['suhu'], 0.0005);

        $byNama = [];
        foreach ($hasil['urut'] as $item) {
            $byNama[$item['nama']] = $item;
        }

        $this->assertEqualsWithDelta(0.1667, $byNama['Alfa']['S'], 0.0005);
        $this->assertEqualsWithDelta(0.1667, $byNama['Alfa']['R'], 0.0005);
        $this->assertEqualsWithDelta(0.0000, $byNama['Alfa']['Q'], 0.0005);

        $this->assertEqualsWithDelta(0.6125, $byNama['Beta']['S'], 0.0005);
        $this->assertEqualsWithDelta(0.3333, $byNama['Beta']['R'], 0.0005);
        $this->assertEqualsWithDelta(1.0000, $byNama['Beta']['Q'], 0.0005);

        $this->assertEqualsWithDelta(0.5667, $byNama['Gama']['S'], 0.0005);
        $this->assertEqualsWithDelta(0.3333, $byNama['Gama']['R'], 0.0005);
        $this->assertEqualsWithDelta(0.9486, $byNama['Gama']['Q'], 0.0005);

        $this->assertEqualsWithDelta(0.4333, $byNama['Delta']['S'], 0.0005);
        $this->assertEqualsWithDelta(0.2000, $byNama['Delta']['R'], 0.0005);
        $this->assertEqualsWithDelta(0.3991, $byNama['Delta']['Q'], 0.0005);

        // Urutan: Alfa, Delta, Gama, Beta
        $urutanNama = array_column($hasil['urut'], 'nama');
        $this->assertSame(['Alfa', 'Delta', 'Gama', 'Beta'], $urutanNama);

        $this->assertTrue($hasil['keunggulan']);
        $this->assertTrue($hasil['kestabilan']);
        $this->assertSame('tunggal', $hasil['mode']);
        $this->assertTrue($byNama['Alfa']['utama']);
        $this->assertFalse($byNama['Delta']['utama']);

        // Pengujian dengan v = 1
        $hasilV1 = Vikor::hitung($data, $kriteria, 1.0);
        $byNamaV1 = [];
        foreach ($hasilV1['urut'] as $item) {
            $byNamaV1[$item['nama']] = $item;
        }

        $urutanNamaV1 = array_column($hasilV1['urut'], 'nama');
        $this->assertSame(['Alfa', 'Delta', 'Gama', 'Beta'], $urutanNamaV1);

        $this->assertEqualsWithDelta(0.0000, $byNamaV1['Alfa']['Q'], 0.0005);
        $this->assertEqualsWithDelta(0.5981, $byNamaV1['Delta']['Q'], 0.0005);
        $this->assertEqualsWithDelta(0.8972, $byNamaV1['Gama']['Q'], 0.0005);
        $this->assertEqualsWithDelta(1.0000, $byNamaV1['Beta']['Q'], 0.0005);
    }

    /**
     * Tes 3: kolom konstan. Tiga destinasi dengan biaya 10000, 20000, 30000 dan rating sama (4,0).
     * kriteria biaya cost bobot 1, rating benefit bobot 1; v = 0,5.
     * Tidak boleh ada NaN. S = 0; 0,25; 0,5 dan Q = 0; 0,5; 1.
     */
    public function test_tes_3_kolom_konstan(): void
    {
        $data = [
            ['nama' => 'D1', 'biaya' => 10000, 'rating' => 4.0],
            ['nama' => 'D2', 'biaya' => 20000, 'rating' => 4.0],
            ['nama' => 'D3', 'biaya' => 30000, 'rating' => 4.0],
        ];

        $kriteria = [
            ['key' => 'biaya', 'tipe' => 'cost', 'bobot' => 1],
            ['key' => 'rating', 'tipe' => 'benefit', 'bobot' => 1],
        ];

        $hasil = Vikor::hitung($data, $kriteria, 0.5);

        $byNama = [];
        foreach ($hasil['urut'] as $item) {
            $byNama[$item['nama']] = $item;
        }

        // Tidak boleh ada NaN
        $this->assertFalse(is_nan($byNama['D1']['S']));
        $this->assertFalse(is_nan($byNama['D1']['Q']));
        $this->assertFalse(is_nan($byNama['D2']['S']));
        $this->assertFalse(is_nan($byNama['D2']['Q']));
        $this->assertFalse(is_nan($byNama['D3']['S']));
        $this->assertFalse(is_nan($byNama['D3']['Q']));

        $this->assertEqualsWithDelta(0.0, $byNama['D1']['S'], 0.0005);
        $this->assertEqualsWithDelta(0.25, $byNama['D2']['S'], 0.0005);
        $this->assertEqualsWithDelta(0.5, $byNama['D3']['S'], 0.0005);

        $this->assertEqualsWithDelta(0.0, $byNama['D1']['Q'], 0.0005);
        $this->assertEqualsWithDelta(0.5, $byNama['D2']['Q'], 0.0005);
        $this->assertEqualsWithDelta(1.0, $byNama['D3']['Q'], 0.0005);
    }

    /**
     * Tes 4: satu kandidat.
     * Kembalikan Q = 0, mode = "tunggal", tanpa error.
     */
    public function test_tes_4_satu_kandidat(): void
    {
        $data = [
            ['nama' => 'Tunggal', 'biaya' => 25000, 'rating' => 4.5],
        ];

        $kriteria = [
            ['key' => 'biaya', 'tipe' => 'cost', 'bobot' => 1],
            ['key' => 'rating', 'tipe' => 'benefit', 'bobot' => 2],
        ];

        $hasil = Vikor::hitung($data, $kriteria, 0.5);

        $this->assertCount(1, $hasil['urut']);
        $this->assertEquals(0.0, $hasil['urut'][0]['Q']);
        $this->assertSame('tunggal', $hasil['mode']);
        $this->assertTrue($hasil['urut'][0]['utama']);
        $this->assertEquals(1, $hasil['urut'][0]['peringkat']);
    }

    /**
     * Tes 5: error.
     * Bobot semua 0 melempar InvalidArgumentException.
     * Data kosong melempar InvalidArgumentException.
     */
    public function test_tes_5_error_pada_bobot_nol_dan_data_kosong(): void
    {
        $data = [
            ['nama' => 'A', 'biaya' => 10000],
        ];

        $kriteriaNol = [
            ['key' => 'biaya', 'tipe' => 'cost', 'bobot' => 0],
        ];

        $this->expectException(InvalidArgumentException::class);
        Vikor::hitung($data, $kriteriaNol, 0.5);
    }

    public function test_tes_5_error_pada_data_kosong(): void
    {
        $kriteria = [
            ['key' => 'biaya', 'tipe' => 'cost', 'bobot' => 1],
        ];

        $this->expectException(InvalidArgumentException::class);
        Vikor::hitung([], $kriteria, 0.5);
    }
}
