<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Destinasi;
use App\Models\KotaAsal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DestinasiController extends Controller
{
    /**
     * Menampilkan daftar destinasi, dengan opsi filter jenis.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Destinasi::query();

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->query('jenis'));
        }

        $destinasi = $query->get();

        return response()->json($destinasi);
    }

    /**
     * Menampilkan satu destinasi berdasarkan ID, 404 jika tidak ada.
     */
    public function show(int $id): JsonResponse
    {
        $destinasi = Destinasi::find($id);

        if (!$destinasi) {
            return response()->json([
                'message' => 'Destinasi wisata tidak ditemukan.',
            ], 404);
        }

        return response()->json($destinasi);
    }

    /**
     * Menampilkan daftar seluruh kota asal keberangkatan.
     */
    public function kota(): JsonResponse
    {
        $kota = KotaAsal::all();

        return response()->json($kota);
    }
}
