<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('destinasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->enum('jenis', [
                'Pantai',
                'Pegunungan',
                'Air & Danau',
                'Taman & Keluarga',
                'Budaya & Sejarah',
            ]);
            $table->string('wilayah');
            $table->double('lat');
            $table->double('lon');
            $table->unsignedInteger('biaya');
            $table->decimal('rating', 2, 1);
            $table->integer('suhu');
            $table->unsignedTinyInteger('keramaian');
            $table->unsignedTinyInteger('fasilitas');
            $table->text('deskripsi')->nullable();
            $table->string('foto')->nullable();
            $table->string('sumber');
            $table->string('diperbarui')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destinasi');
    }
};
