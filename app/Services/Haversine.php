<?php

namespace App\Services;

class Haversine
{
    /**
     * Jari-jari rata-rata bumi dalam kilometer.
     */
    public const RADIUS_BUMI_KM = 6371.0;

    /**
     * Menghitung jarak garis lurus antara dua titik koordinat (derajat desimal)
     * menggunakan rumus Haversine.
     *
     * @param float $lat1 Lintang titik pertama (derajat desimal)
     * @param float $lon1 Bujur titik pertama (derajat desimal)
     * @param float $lat2 Lintang titik kedua (derajat desimal)
     * @param float $lon2 Bujur titik kedua (derajat desimal)
     * @return float Jarak dalam kilometer (km)
     */
    public static function hitung(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        // Konversi selisih koordinat dari derajat ke radian
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        // Rumus haversine untuk separuh kuadrat jarak sudut
        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) ** 2;

        // Jarak sudut lingkaran besar dalam radian
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Jarak permukaan bumi = jari-jari dikalikan jarak sudut
        return self::RADIUS_BUMI_KM * $c;
    }
}
