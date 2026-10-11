<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destinasi extends Model
{
    use HasFactory;

    protected $table = 'destinasi';

    public $timestamps = false;

    protected $fillable = [
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

    protected $casts = [
        'lat' => 'float',
        'lon' => 'float',
        'biaya' => 'integer',
        'rating' => 'float',
        'suhu' => 'integer',
        'keramaian' => 'integer',
        'fasilitas' => 'integer',
    ];
}
