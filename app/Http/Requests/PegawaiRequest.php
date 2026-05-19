<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PegawaiRequest extends FormRequest
{
    public function rules(): array
    {
        $pegawai = $this->route('pegawai');

        $nipLama = is_object($pegawai) ? $pegawai->nip : $pegawai;

        return [
            "foto" => "nullable|image|mimes:jpeg,png,jpg,svg|max:2048",

            "nama" => "required|string",

            "nip" => [
                "required",
                "string",
                Rule::unique('pegawai', 'nip')->ignore($nipLama, 'nip'),
                "regex:/^\d{18}$/"
            ],
            "tempat_lahir" => "required|string",
            "tgl_lahir" => "required|date|before_or_equal:today",
            "jenis_kelamin" => [
                "required",
                Rule::in(['L', 'P']),
            ],
            "agama" => "required|string",
            "no_hp" => "required|string",
            "npwp" => [
                "nullable",
                "string",
                Rule::unique('pegawai', 'npwp')->ignore($nipLama, 'nip'),
            ],

            "alamat.alamat" => "required|string",
            "alamat.kota" => "required|string",
            "alamat.provinsi" => "required|string",

            "jabatan.golongan" => "required|string",
            "jabatan.eselon" => "required|string",
            "jabatan.jabatan" => "required|string",
            "jabatan.tempat_tugas" => "required|string",
            "jabatan.unit_kerja" => "required|string",
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
