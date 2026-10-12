# SPEC: Jelajah Jabar (versi Laravel)

Sistem rekomendasi destinasi wisata Jawa Barat. Pengguna mengatur pilihan lewat slider dan tombol, sistem menghitung peringkat destinasi dengan metode VIKOR dari data di database.

Dokumen ini adalah acuan tunggal untuk agen. Baca seluruhnya sebelum mengubah kode. Jika ada hal yang tidak tercantum, tanyakan, jangan mengarang.

## 1. Batasan

- Waktu pengerjaan 5 hari. Jangan menambah fitur di luar dokumen ini.
- Tidak ada login, tidak ada akun pengguna, tidak ada halaman admin, tidak ada fitur unggah foto.
- **Dilarang mengarang data destinasi.** Data hanya berasal dari `data/destinasi.csv` yang diisi manusia.
- **Dilarang mengubah angka ekspektasi di tes** agar tes lulus. Jika tes gagal, perbaiki kode atau laporkan selisihnya.
- **Dilarang mengubah `app/Services/Vikor.php` dan tesnya** saat mengerjakan tampilan atau API, kecuali diminta pengguna secara eksplisit.
- Stack: PHP 8.2 atau lebih baru, Laravel (versi yang dipasang `composer create-project laravel/laravel`), MySQL untuk pengembangan, SQLite memori untuk tes (bawaan `phpunit.xml`), Blade, Tailwind CSS, PHPUnit.
- Dilarang menambah paket Composer di luar bawaan Laravel. JavaScript di halaman memakai JavaScript biasa (`fetch`), tanpa framework JS.
- Semua teks antarmuka berbahasa Indonesia.

## 2. Struktur folder

```
jelajah-jabar/
├── SPEC.md                              # dokumen ini
├── data/destinasi.csv                   # diisi Anggota 2 dan 3, bukan agen
├── prototipe/
│   ├── index.html                       # prototipe lama, JANGAN diedit
│   └── stitch/                          # beranda, katalog, detail, rekomendasi (desain Stitch)
├── app/
│   ├── Models/Destinasi.php
│   ├── Models/KotaAsal.php
│   ├── Services/
│   │   ├── Vikor.php                    # fungsi murni, TANPA akses database atau request
│   │   ├── Haversine.php                # jarak dua koordinat (km)
│   │   └── AlasanBuilder.php            # menyusun kalimat alasan per destinasi
│   └── Http/
│       ├── Controllers/HalamanController.php
│       ├── Controllers/Api/DestinasiController.php
│       ├── Controllers/Api/RekomendasiController.php
│       └── Requests/RekomendasiRequest.php
├── database/
│   ├── migrations/                      # destinasi, kota_asal
│   └── seeders/                         # DestinasiSeeder, KotaAsalSeeder
├── resources/views/
│   ├── layouts/app.blade.php            # navbar dan footer jadi komponen
│   ├── beranda.blade.php
│   ├── katalog.blade.php
│   ├── destinasi/show.blade.php
│   └── rekomendasi.blade.php
├── routes/web.php, routes/api.php
├── public/images/destinasi/             # satu foto per destinasi, nama file = kolom foto
├── public/images/placeholder.jpg        # gambar cadangan
└── tests/
    ├── Unit/VikorTest.php               # tes 1-5 (bagian 8)
    ├── Unit/HaversineTest.php           # tes 6
    └── Feature/RekomendasiApiTest.php, HalamanTest.php
```

Aturan: logika perhitungan hanya ada di `Vikor.php`. Controller dan tampilan hanya memanggilnya.

## 3. Database

Migration (nama tabel dan kolom persis seperti di bawah). Nilai `jenis` memakai `enum` agar ada batasan di MySQL dan SQLite.

| Tabel `destinasi` | Tipe | Catatan |
|---|---|---|
| id | bigint, PK | otomatis |
| nama | string, unik | wajib |
| jenis | enum | hanya: `Pantai`, `Pegunungan`, `Air & Danau`, `Taman & Keluarga`, `Budaya & Sejarah` |
| wilayah | string | wajib, contoh "Ciwidey, Kabupaten Bandung" |
| lat | double | wajib, derajat desimal (negatif = Lintang Selatan) |
| lon | double | wajib, derajat desimal |
| biaya | unsigned integer | rupiah per orang, 0 = gratis |
| rating | decimal(2,1) | 1,0 sampai 5,0 |
| suhu | integer | derajat Celsius, perkiraan rata-rata |
| keramaian | unsigned tinyint | 1 sampai 5 |
| fasilitas | unsigned tinyint | 1 sampai 5 |
| deskripsi | text, boleh kosong | |
| foto | string, boleh kosong | hanya nama file, contoh `kawah-putih-ciwidey.jpg` |
| sumber | string | wajib, asal angka dan data |
| diperbarui | string atau date, boleh kosong | |

| Tabel `kota_asal` | Tipe |
|---|---|
| id | bigint, PK |
| nama | string, unik |
| lat, lon | double |

Aturan:

- `jarak` **tidak disimpan**. Nilainya dihitung per permintaan dari `lat`, `lon`, dan kota asal pengguna.
- Skala `keramaian` dan `fasilitas`: 1 sampai 5 (1 = sangat sepi atau sangat sederhana, 5 = sangat ramai atau sangat lengkap).
- Batas rentang (rating 1-5, keramaian 1-5, fasilitas 1-5, biaya ≥ 0) diperiksa di seeder karena `CHECK` tidak tersedia di semua database.
- `DestinasiSeeder` membaca `data/destinasi.csv`:
  - Pemisah kolom bisa `,` atau `;` (CSV dari Excel Indonesia sering memakai `;`). Deteksi dari baris header. Buang BOM UTF-8 bila ada.
  - Kolom CSV: `nama,jenis,wilayah,lat,lon,biaya,rating,suhu,keramaian,fasilitas,deskripsi,foto,sumber,diperbarui`.
  - Validasi tiap baris. Baris tidak valid dilaporkan dengan nomor barisnya dan **seluruh proses dibatalkan**, tanpa mengubah tabel.
  - Baris tanpa `sumber` ditolak. Baris yang `jenis`-nya di luar 5 nilai di atas ditolak.
  - Kosongkan tabel `destinasi`, lalu masukkan ulang.
  - Periksa apakah file di kolom `foto` ada di `public/images/destinasi/`. Jika tidak ada, jangan gagalkan seeder, tetapi cetak daftar destinasi yang fotonya hilang.
- `KotaAsalSeeder` mengisi 15 kota dari konstanta `KOTA` di `prototipe/index.html` (Bandung, Bogor, Depok, Bekasi, Cirebon, Sukabumi, Tasikmalaya, Garut, Cimahi, Karawang, Cianjur, Purwakarta, Subang, Sumedang, Jakarta (dari luar Jabar)).

## 4. Kelas VIKOR (`app/Services/Vikor.php`)

Tanda tangan:

```php
Vikor::hitung(array $data, array $kriteria, float $v): array
```

- `$data`: array destinasi (array asosiatif, sudah difilter, sudah memuat `jarak`).
- `$kriteria`: array `['key' => ..., 'tipe' => ..., 'bobot' => ..., 'target' => ...]`. `tipe`:
  - `cost`: makin kecil makin baik (contoh: `biaya`)
  - `benefit`: makin besar makin baik (contoh: `rating`)
  - `target`: makin dekat ke `target` makin baik. Nilai yang dipakai adalah `|nilai − target|`, diperlakukan sebagai cost.
- `$v`: bilangan 0 sampai 1.
- Kesalahan (bobot total 0, data kosong) melempar `InvalidArgumentException` dengan pesan jelas.

Langkah:

1. `w[j] = bobot[j] / jumlah bobot`. Jika jumlah bobot 0, lempar error.
2. Bentuk matriks `X[i][j]` (untuk `target`: selisih mutlak).
3. Untuk tiap kriteria j: `f*` = nilai terbaik (benefit: maksimum, selain itu minimum), `f-` = nilai terburuk.
4. `d[i][j] = (f*[j] − X[i][j]) / (f*[j] − f-[j])`. **Jika `f*[j] == f-[j]`, `d = 0`** (jangan membagi dengan nol).
5. `S[i] = Σ w[j]·d[i][j]`, `R[i] = max(w[j]·d[i][j])`.
6. `Q[i] = v·(S[i] − S*)/(S- − S*) + (1 − v)·(R[i] − R*)/(R- − R*)`. Jika penyebut 0, suku itu 0.
7. Urutkan naik berdasarkan Q. Jika Q sama: S naik, lalu `rating` turun, lalu `nama` alfabetis.

Makna `v`: **v tinggi** menekankan kepuasan rata-rata di semua kriteria (S, "mayoritas"), **v rendah** menekankan kekurangan terburuk (R, "hindari kekurangan").

Uji dua syarat (hanya jika jumlah kandidat m ≥ 2). `A1` = peringkat 1, `A2` = peringkat 2, toleransi perbandingan 1e-9:

- **Keunggulan**: `Q(A2) − Q(A1) ≥ 1/(m−1)`.
- **Kestabilan**: `A1` juga terbaik menurut S atau R (`S(A1) = min S` atau `R(A1) = min R`).

Penentuan `mode` dan `utama` (destinasi yang direkomendasikan):

| Kondisi | mode | utama |
|---|---|---|
| m = 1, atau kedua syarat terpenuhi | `tunggal` | A1 |
| Keunggulan gagal | `setara` | semua destinasi dengan `Q − Q(A1) < 1/(m−1)` |
| Keunggulan lolos, kestabilan gagal | `dua_teratas` | A1 dan A2 |

Nilai kembalian:

```php
[
  'urut' => [ [...destinasi, 'S'=>, 'R'=>, 'Q'=>, 'd'=>['biaya'=>.., ...], 'peringkat'=>1, 'utama'=>true], ... ],
  'Smin'=>, 'Smax'=>, 'Rmin'=>, 'Rmax'=>,
  'bobotTernormalisasi'=>[..], 'DQ'=>, 'keunggulan'=>bool, 'kestabilan'=>bool, 'mode'=>'tunggal|setara|dua_teratas'
]
```

`d` per destinasi (nilai d tiap kriteria, kunci = `key` kriteria) dipakai untuk menyusun alasan.

Kasus tepi yang wajib ditangani: satu kandidat (Q = 0), kolom dengan nilai sama di semua baris (tanpa NaN), semua Q sama, bobot total 0 (error jelas), data kosong (error jelas).

## 5. API

Semua di `routes/api.php`, tanpa autentikasi.

`GET /api/destinasi?jenis=Pantai` → daftar destinasi (untuk katalog). `jenis` opsional.
`GET /api/destinasi/{id}` → satu destinasi, 404 jika tidak ada.
`GET /api/kota` → daftar kota asal.

`POST /api/rekomendasi`, isi permintaan:

```json
{
  "jenis": ["Pantai","Pegunungan"],
  "asal": "Bandung",
  "maxBiaya": 25000,
  "target": { "jarak": 60, "suhu": 20, "keramaian": 2, "fasilitas": 3 },
  "penting": { "biaya": 1, "rating": 1, "jarak": 2, "suhu": 3, "keramaian": 2, "fasilitas": 1 },
  "v": 0.5
}
```

`RekomendasiRequest` memvalidasi: `jenis` kosong berarti semua jenis; nilai `jenis` harus salah satu dari 5 nilai; `asal` harus ada di `kota_asal`; `maxBiaya` null berarti tanpa batas, selain itu ≥ 0; `target.*` angka; `penting.*` bilangan bulat 0 sampai 3 (0 = abaikan) dan **tidak boleh semuanya 0**; `v` 0 sampai 1. Kesalahan → HTTP 422 (format bawaan Laravel) dengan pesan berbahasa Indonesia.

Controller mengerjakan, berurutan:

1. Ambil destinasi dengan Eloquent/query builder (filter `jenis` dan `biaya <= maxBiaya`, **dilarang** menyambung string SQL).
2. Hitung `jarak` tiap baris dengan Haversine (R = 6371 km) dari koordinat kota asal. Jarak adalah **garis lurus**, bukan jarak jalan.
3. Susun `kriteria`: `biaya` cost, `rating` benefit, keempat lainnya `target`. Bobot = nilai `penting`.
4. Panggil `Vikor::hitung()`.
5. Tambahkan ke tiap item: `persen` dan `alasan` (bagian 6).
6. Kembalikan JSON:

```json
{
  "mode": "tunggal | setara | dua_teratas | kosong",
  "jumlahKandidat": 12,
  "totalAktif": 48,
  "asal": "Bandung",
  "v": 0.5,
  "bobotTernormalisasi": { "biaya": 0.1, "rating": 0.1, "jarak": 0.2, "suhu": 0.3, "keramaian": 0.2, "fasilitas": 0.1 },
  "DQ": 0.0909, "keunggulan": true, "kestabilan": true,
  "urut": [ { "id":1, "nama":"...", "jenis":"...", "wilayah":"...", "foto":"...", "biaya":0, "rating":4.5,
              "suhu":17, "keramaian":3, "fasilitas":4, "jarak":48.2,
              "S":0.1, "R":0.1, "Q":0.0, "persen":100.0, "peringkat":1, "utama":true,
              "alasan":["...", "..."] } ]
}
```

`jumlahKandidat` = banyak destinasi setelah filter. `totalAktif` = jumlah seluruh baris di tabel `destinasi`. Keduanya dari database, **bukan angka tetap**.

Jika tidak ada destinasi yang lolos filter, kembalikan HTTP 200 dengan `urut: []` dan `mode: "kosong"`.

## 6. Keputusan tampilan hasil (tetap, jangan diubah)

### 6.1 Label gaya keputusan (nilai v)

| Label di antarmuka | v | Keterangan di antarmuka |
|---|---|---|
| Seimbang | 0.5 | Menyeimbangkan kepuasan rata-rata dan kekurangan terburuk |
| Utamakan mayoritas | 0.8 | Mengutamakan kecocokan rata-rata di sebagian besar kriteria |
| Hindari kekurangan | 0.2 | Menghindari destinasi yang sangat buruk di satu kriteria |

Nilai selain 0.2 / 0.5 / 0.8 tidak dipakai di antarmuka. Desain Stitch yang memakai 0.7 dan 0.3 **salah**, abaikan.

### 6.2 Skor yang ditampilkan

- Di VIKOR, **Q lebih kecil berarti lebih baik**. Tampilkan Q dengan keterangan "makin kecil makin baik".
- Persen kecocokan: `persen = round((1 − Q) × 100, 1)`, dihitung di controller, bukan di `Vikor.php`. Beri label "Kecocokan relatif" dan tulis rumusnya di bagian "Lihat cara menghitung".
- Karena Q dinormalisasi antar kandidat, persen bersifat **relatif terhadap kandidat saat ini**: peringkat 1 selalu 100% dan yang terburuk 0%. Jangan menyebutnya "akurasi" atau "peluang".
- Jangan menampilkan persen rekaan atau angka yang tidak berasal dari respons API.

### 6.3 Lima kondisi panel hasil (kanan halaman rekomendasi)

| Kondisi | Isi panel kanan |
|---|---|
| 1. Awal (belum mencari) | Sesuai desain `rekomendasi.html`: header "Rekomendasi Wisata untukmu" dengan badge "{N} destinasi dianalisis" (N = total baris tabel destinasi), "Ringkasan Preferensi Aktif" (dengan label "Jarak Diinginkan"), "Prioritas Penilaian" (bar bobot kriteria yang reaktif saat slider/bobot diubah, persen = nilai penting / total penting), "Destinasi Populer Siap Dikalkulasi" (grid 2x2 dari 4 destinasi rating tertinggi di database dengan badge "{N} Titik Aktif"), "Bagaimana Rekomendasi Dihitung?" (3 langkah VIKOR), "Insight Perjalanan", dan "Jaminan Rekomendasi Objektif & Bebas Bias". Tanpa angka karangan atau data rekaan. |
| 2. Memuat | Kerangka kartu abu-abu berkedip (skeleton) dan teks "Menghitung...". Tombol dinonaktifkan. |
| 3. Hasil | Daftar peringkat (lihat 6.4) |
| 4. Tidak ada hasil | "Tidak ada destinasi yang cocok", saran: naikkan anggaran maksimal atau tambah jenis wisata. Dipakai juga untuk `mode: "kosong"`. |
| 5. Error | Pesan kesalahan jelas: kesalahan validasi (422) menampilkan pesan per kolom; server tidak terjangkau menampilkan "Tidak dapat menghubungi server, coba lagi". |

Saat halaman dibuka, tampilkan kondisi 1. Data destinasi populer diambil dari database (rating tertinggi), sedangkan perhitungan peringkat dan skor kompromi VIKOR baru dihitung setelah tombol ditekan.

### 6.4 Kondisi hasil

- Judul: "Menampilkan {n} teratas dari {jumlahKandidat} destinasi", dengan n = min(5, jumlah hasil). Subjudul: "Berdasarkan keberangkatan {asal}". Di bawah form: "Menghitung {totalAktif} titik wisata aktif".
- Badge mode (menggantikan badge "MCDM VIKOR Valid"; tidak ada proses "validasi"):
  - `tunggal` → "Satu pilihan terbaik"
  - `setara` → "Beberapa pilihan setara" (tandai semua `utama`)
  - `dua_teratas` → "Dua pilihan teratas" (tandai A1 dan A2)
- Tiap kartu: peringkat, foto, nama, jenis, wilayah, `persen` dan Q, jarak "{jarak} km (garis lurus)", suhu, biaya, `alasan`. Tandai "Rekomendasi Utama" bila `utama = true`.
- Jangan menampilkan estimasi waktu tempuh (jam). Sistem tidak menghitungnya.
- Bagian "Lihat cara menghitung" (dapat dibuka): bobot ternormalisasi, nilai ideal dan terburuk tiap kriteria, tabel S, R, Q semua kandidat, rumus persen, dan hasil uji keunggulan dan kestabilan.

### 6.5 Alasan (`AlasanBuilder`)

- Dibuat di server dari data, **bukan teks tetap**.
- Hanya dari kriteria dengan bobot `penting > 0`. Urutkan menurut `d` naik (tie: bobot lebih besar dulu, lalu urutan `biaya, rating, jarak, suhu, keramaian, fasilitas`). Ambil paling banyak 3.
- Hanya kriteria dengan `d ≤ 0.4` (konstanta `BATAS_ALASAN`). Jika tidak ada, `alasan` = `[]` dan antarmuka menyembunyikan blok alasan.
- Templat kalimat:
  - `biaya`: biaya 0 → "Tiket masuk gratis"; selain itu → "Tiket Rp{biaya}" ditambah " (anggaran maksimal Rp{maxBiaya})" jika `maxBiaya` ada
  - `rating`: "Rating {rating} dari 5"
  - `jarak`: "Sekitar {jarak} km dari {asal} (garis lurus), dekat dengan jarak pilihanmu {target} km"
  - `suhu`: "Suhu sekitar {suhu}°C, mendekati pilihanmu {target}°C"
  - `keramaian`: "Tingkat keramaian {label}, sesuai pilihanmu" (1 sangat sepi, 2 sepi, 3 sedang, 4 ramai, 5 sangat ramai)
  - `fasilitas`: "Fasilitas {label}, sesuai pilihanmu" (1 sangat sederhana, 2 sederhana, 3 cukup, 4 lengkap, 5 sangat lengkap)

### 6.6 Kontrol form

- Daftar jenis wisata di form **persis sama** dengan 5 nilai kolom `jenis` (bagian 3). Label seperti "Gunung & Kawah", "Kebun Teh", "Air Terjun", "Agrowisata" dari desain Stitch diganti dengan 5 nilai itu.
- Slider (mengikuti `prototipe/index.html`): anggaran maksimal 10.000 sampai 250.000 (langkah 5.000), jarak 0 sampai 300 km (langkah 10), suhu 16 sampai 30 °C, keramaian 1 sampai 5, fasilitas 1 sampai 5. Tiap kriteria punya pilihan "Seberapa penting?" 0 sampai 3.
- Anggaran maksimal adalah **filter**, dan "penting biaya" adalah bobot kriteria biaya. Keduanya terpisah.

## 7. Antarmuka (halaman lain)

Ikuti `prototipe/stitch/` untuk tampilan dan gaya. **Data dan teks contoh di dalam desain Stitch diabaikan**; semua isi dari database.

- Beranda: 4 kartu destinasi populer = 4 destinasi dengan `rating` tertinggi dari database.
- Katalog: hanya tampilan. Data dari database dengan pagination dan filter jenis lewat query string. Kartu mengarah ke `/destinasi/{id}`. Tidak ada VIKOR di halaman ini.
- Detail (`/destinasi/{id}`): semua teks dan angka dari kolom database. Kartu "Pesona Keindahan Alam" dan "Informasi & Tarif" sejajar: grid 3:2, `items-stretch`, padding dan gap sama, blok "Daya Tarik Utama" didorong ke dasar kartu kiri (`mt-auto`). Kolom yang tidak ada di database (ulasan, jam buka, tips) **tidak ditampilkan**.
- Foto: `asset('images/destinasi/' . $destinasi->foto)`. Jika `foto` kosong atau file tidak ada, pakai `images/placeholder.jpg`.
- Font memakai Poppins (bukan serif).
- Rapi di lebar 375 px. Jika kartu bertumpuk di layar kecil, lebar penuh.

## 8. Tes penerimaan

PHPUnit. Bandingkan desimal dengan `assertEqualsWithDelta(..., 0.0005)`. Angka di bawah dihitung dengan tangan dan skrip terpisah. **Jangan diubah.**

**Tes 1: tanpa target, 5 destinasi.** `v = 0,5`.

Data:

| | harga | jarak | rating | fasilitas | keramaian |
|---|---|---|---|---|---|
| A | 5000 | 8 | 4,3 | 4 | 4 |
| B | 10000 | 90 | 4,6 | 3 | 2 |
| C | 0 | 3 | 4,0 | 5 | 5 |
| D | 50000 | 40 | 4,8 | 4 | 3 |
| E | 15000 | 15 | 4,4 | 2 | 1 |

Kriteria (tipe, bobot): harga cost 0,30; jarak cost 0,20; rating benefit 0,25; fasilitas benefit 0,10; keramaian cost 0,15.

Hasil yang benar:

| | S | R | Q |
|---|---|---|---|
| A | 0,3436 | 0,1563 | 0,0926 |
| B | 0,4267 | 0,2000 | 0,4931 |
| C | 0,4000 | 0,2500 | 0,5475 |
| D | 0,4934 | 0,3000 | 1,0000 |
| E | 0,3426 | 0,1250 | 0,0000 |

Urutan: E, A, B, C, D. Batas keunggulan 1/(m−1) = 0,25 dan selisih Q = 0,0926, jadi keunggulan **gagal**. Kestabilan terpenuhi (E terbaik di S dan R). `mode = "setara"`, `utama` = E dan A.

**Tes 2: dengan kriteria target, 4 destinasi.** `v = 0,5`.

| | biaya | rating | jarak | suhu |
|---|---|---|---|---|
| Alfa | 10000 | 4,0 | 50 | 18 |
| Beta | 20000 | 4,5 | 100 | 22 |
| Gama | 30000 | 4,8 | 20 | 28 |
| Delta | 15000 | 4,2 | 80 | 20 |

Kriteria: biaya cost bobot 1; rating benefit bobot 1; jarak target 40 bobot 2; suhu target 18 bobot 2. Bobot ternormalisasi: 0,1667; 0,1667; 0,3333; 0,3333.

| | S | R | Q |
|---|---|---|---|
| Alfa | 0,1667 | 0,1667 | 0,0000 |
| Beta | 0,6125 | 0,3333 | 1,0000 |
| Gama | 0,5667 | 0,3333 | 0,9486 |
| Delta | 0,4333 | 0,2000 | 0,3991 |

Urutan: Alfa, Delta, Gama, Beta. Selisih Q (0,3991) ≥ batas 1/3 (0,3333), jadi keunggulan terpenuhi. Kestabilan terpenuhi. `mode = "tunggal"`, `utama` = Alfa.

Dengan `v = 1`: urutan tetap Alfa, Delta, Gama, Beta dengan Q = 0,0000; 0,5981; 0,8972; 1,0000.

**Tes 3: kolom konstan.** Tiga destinasi dengan biaya 10000, 20000, 30000 dan rating sama (4,0); kriteria biaya cost bobot 1, rating benefit bobot 1; `v = 0,5`. Tidak boleh ada NaN. S = 0; 0,25; 0,5 dan Q = 0; 0,5; 1.

**Tes 4: satu kandidat.** Kembalikan Q = 0, `mode = "tunggal"`, tanpa error.

**Tes 5: error.** Bobot semua 0 melempar `InvalidArgumentException`. Data kosong melempar `InvalidArgumentException`.

**Tes 6: Haversine.** Bandung (−6,9175; 107,6191) ke Garut (−7,2279; 107,9087) berjarak kira-kira 47,04 km (toleransi 0,1 km).

**`tests/Feature/RekomendasiApiTest.php`** (`RefreshDatabase`, database berisi 3 destinasi buatan di dalam tes):

- `POST /api/rekomendasi` mengembalikan 200 dengan `urut` terurut naik menurut Q.
- Peringkat 1 memiliki `persen = 100.0`, dan tiap item memiliki `persen = round((1 − Q) × 100, 1)`.
- `jumlahKandidat` sama dengan banyak baris yang lolos filter dan `totalAktif` sama dengan jumlah baris tabel.
- `alasan` berisi paling banyak 3 kalimat, tidak ada kalimat untuk kriteria dengan `penting = 0`, dan kosong bila semua `d > 0.4`.
- Masukan tidak valid (semua `penting` 0, `v` = 2, `asal` tidak dikenal, `jenis` di luar 5 nilai) mengembalikan 422.
- Filter yang tidak meloloskan siapa pun mengembalikan 200 dengan `mode: "kosong"` dan `urut: []`.

**`tests/Feature/HalamanTest.php`:** `/`, `/katalog`, `/destinasi/{id}` (id ada), `/rekomendasi` mengembalikan 200; `/destinasi/9999` mengembalikan 404; halaman `/rekomendasi` tidak memuat kartu destinasi sebelum pencarian.

## 9. Urutan kerja agen

Kerjakan satu langkah per sesi, jalankan tes, lalu commit. Tunggu persetujuan pengguna sebelum langkah berikutnya.

| Langkah | Hari | Pekerjaan | Selesai jika |
|---|---|---|---|
| 1 | 1 | Migration, model, `DestinasiSeeder`, `KotaAsalSeeder`, pemeriksaan foto di seeder | `php artisan migrate:fresh --seed` berhasil dan jumlah baris sama dengan CSV |
| 2 | 1-2 | `Vikor.php`, `Haversine.php`, tes 1-6 | `php artisan test` hijau |
| 3 | 3 | `RekomendasiRequest`, controller API, `AlasanBuilder`, `RekomendasiApiTest` | tes API hijau, permintaan contoh mengembalikan JSON sesuai bagian 5 |
| 4 | 3-4 | Layout, beranda, katalog, detail dari desain Stitch | semua halaman terbuka di browser, data dari database |
| 5 | 4 | Halaman rekomendasi dengan lima kondisi panel (bagian 6) dan `fetch` ke API | slider mengubah hasil; hasil di layar sama dengan hasil API |
| 6 | 4-5 | Uji skenario: semua bobot 0, satu kandidat, slider tengah, anggaran terlalu kecil, lebar 375 px; catat temuan | daftar temuan dan perbaikannya |
| 7 | 5 | Data final, `README.md` (cara pasang dan menjalankan), bekukan fitur | proyek berjalan dari nol dengan `composer install`, `php artisan migrate:fresh --seed`, `php artisan serve` |

## 10. Aturan untuk agen

- Minta rencana dulu, tunggu persetujuan, baru ubah kode.
- Tampilkan hasil tes yang benar-benar dijalankan. Jangan menyatakan "sudah beres" tanpa hasil tes.
- Jangan mengubah `prototipe/`, angka ekspektasi tes, `data/destinasi.csv`, atau `Vikor.php` (di luar langkah 2).
- Jangan menambah paket, fitur, atau halaman di luar dokumen ini.
- Jangan menampilkan data atau angka rekaan di antarmuka. Semua dari database atau respons API.
- Jelaskan setiap perubahan pada `Vikor.php` dengan bahasa sederhana, karena pengguna harus bisa menjelaskannya saat presentasi.
- Jika gagal memperbaiki masalah yang sama setelah 3 percobaan, berhenti dan laporkan apa yang sudah dicoba.

## 11. Definisi selesai

- `php artisan test` hijau.
- Proyek berjalan dari folder bersih dengan `composer install`, `php artisan migrate:fresh --seed`, `php artisan serve`.
- Hasil 3 skenario di aplikasi sama dengan hitungan manual pengguna.
- Seluruh baris data punya `sumber`, dan semua foto di CSV ada di `public/images/destinasi/`.
- Panel hasil kosong sebelum pencarian; label v, persen, dan badge mode sesuai bagian 6.
- Tampilan rapi di HP dan laptop.
