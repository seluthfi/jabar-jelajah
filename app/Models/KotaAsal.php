<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KotaAsal extends Model
{
    use HasFactory;

    protected $table = 'kota_asal';

    public $timestamps = false;

    protected $fillable = [
        'nama',
        'lat',
        'lon',
    ];

    protected $casts = [
        'lat' => 'float',
        'lon' => 'float',
    ];
}
