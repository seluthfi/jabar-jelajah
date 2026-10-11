<?php

namespace Tests\Unit;

use App\Services\Haversine;
use PHPUnit\Framework\TestCase;

class HaversineTest extends TestCase
{
    /**
     * Tes 6: Haversine.
     * Bandung (−6,9175; 107,6191) ke Garut (−7,2279; 107,9087) berjarak kira-kira 43 km (toleransi 3 km).
     */
    public function test_haversine_jarak_bandung_ke_garut(): void
    {
        $latBandung = -6.9175;
        $lonBandung = 107.6191;
        $latGarut = -7.2279;
        $lonGarut = 107.9087;

        $jarak = Haversine::hitung($latBandung, $lonBandung, $latGarut, $lonGarut);

        $this->assertEqualsWithDelta(43.0, $jarak, 3.0);
    }
}
