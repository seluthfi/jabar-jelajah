<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RekomendasiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'jenis' => ['nullable', 'array'],
            'jenis.*' => [
                'string',
                Rule::in([
                    'Pantai',
                    'Pegunungan',
                    'Air & Danau',
                    'Taman & Keluarga',
                    'Budaya & Sejarah',
                ]),
            ],
            'asal' => ['required', 'string', 'exists:kota_asal,nama'],
            'maxBiaya' => ['nullable', 'integer', 'min:0'],
            'target' => ['nullable', 'array'],
            'target.jarak' => ['nullable', 'numeric'],
            'target.suhu' => ['nullable', 'numeric'],
            'target.keramaian' => ['nullable', 'numeric'],
            'target.fasilitas' => ['nullable', 'numeric'],
            'penting' => ['required', 'array'],
            'penting.biaya' => ['required', 'integer', 'between:0,3'],
            'penting.rating' => ['required', 'integer', 'between:0,3'],
            'penting.jarak' => ['required', 'integer', 'between:0,3'],
            'penting.suhu' => ['required', 'integer', 'between:0,3'],
            'penting.keramaian' => ['required', 'integer', 'between:0,3'],
            'penting.fasilitas' => ['required', 'integer', 'between:0,3'],
            'v' => ['required', 'numeric', 'between:0,1'],
        ];
    }

    /**
     * Custom validation hook to ensure not all 'penting' weights are 0.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $penting = $this->input('penting');
            if (is_array($penting)) {
                $totalBobot = array_sum(array_map('intval', $penting));
                if ($totalBobot <= 0) {
                    $validator->errors()->add(
                        'penting',
                        'Bobot kriteria kepentingan tidak boleh semuanya bernilai 0.'
                    );
                }
            }
        });
    }

    /**
     * Custom Indonesian messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'jenis.array' => 'Jenis wisata harus berupa array.',
            'jenis.*.in' => 'Jenis wisata yang dipilih tidak valid. Pilihan yang valid: Pantai, Pegunungan, Air & Danau, Taman & Keluarga, Budaya & Sejarah.',
            'asal.required' => 'Kota asal keberangkatan wajib diisi.',
            'asal.exists' => 'Kota asal keberangkatan tidak ditemukan dalam data kota yang tersedia.',
            'maxBiaya.integer' => 'Anggaran biaya maksimal harus berupa bilangan bulat.',
            'maxBiaya.min' => 'Anggaran biaya maksimal tidak boleh bernilai negatif.',
            'target.*.numeric' => 'Nilai target harus berupa angka numerik.',
            'penting.required' => 'Preferensi bobot kepentingan wajib diisi.',
            'penting.array' => 'Preferensi bobot kepentingan harus berupa array.',
            'penting.*.required' => 'Tingkat kepentingan kriteria wajib diisi.',
            'penting.*.integer' => 'Tingkat kepentingan kriteria harus berupa bilangan bulat.',
            'penting.*.between' => 'Tingkat kepentingan kriteria harus bernilai antara 0 sampai 3.',
            'v.required' => 'Nilai bobot strategi v wajib diisi.',
            'v.numeric' => 'Nilai bobot strategi v harus berupa angka numerik.',
            'v.between' => 'Nilai bobot strategi v harus berada di antara 0 dan 1.',
        ];
    }
}
