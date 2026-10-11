<?php

namespace Database\Seeders;

use App\Models\KotaAsal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KotaAsalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kotaList = [
            ['nama' => 'Bandung', 'lat' => -6.9175, 'lon' => 107.6191],
            ['nama' => 'Bogor', 'lat' => -6.5950, 'lon' => 106.8166],
            ['nama' => 'Depok', 'lat' => -6.4025, 'lon' => 106.7942],
            ['nama' => 'Bekasi', 'lat' => -6.2383, 'lon' => 106.9756],
            ['nama' => 'Cirebon', 'lat' => -6.7063, 'lon' => 108.5570],
            ['nama' => 'Sukabumi', 'lat' => -6.9277, 'lon' => 106.9300],
            ['nama' => 'Tasikmalaya', 'lat' => -7.3274, 'lon' => 108.2207],
            ['nama' => 'Garut', 'lat' => -7.2279, 'lon' => 107.9087],
            ['nama' => 'Cimahi', 'lat' => -6.8841, 'lon' => 107.5413],
            ['nama' => 'Karawang', 'lat' => -6.3227, 'lon' => 107.3376],
            ['nama' => 'Cianjur', 'lat' => -6.8205, 'lon' => 107.1428],
            ['nama' => 'Purwakarta', 'lat' => -6.5569, 'lon' => 107.4431],
            ['nama' => 'Subang', 'lat' => -6.5714, 'lon' => 107.7606],
            ['nama' => 'Sumedang', 'lat' => -6.8595, 'lon' => 107.9239],
            ['nama' => 'Jakarta (dari luar Jabar)', 'lat' => -6.2088, 'lon' => 106.8456],
        ];

        DB::table('kota_asal')->delete();

        foreach ($kotaList as $kota) {
            KotaAsal::create($kota);
        }

        $this->command?->info("KotaAsalSeeder berhasil memasukkan " . count($kotaList) . " kota asal.");
    }
}
