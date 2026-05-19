<?php

namespace App\Http\Controllers;

use App\Http\Requests\PegawaiRequest;
use App\Models\AlamatPegawai;
use App\Models\JabatanPegawai;
use App\Models\Pegawai;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PegawaiController extends Controller
{
    public function store(PegawaiRequest $request)
    {
        $data = $request->validated();

        $foto = $request->file('foto');
        $pathFoto=null;
        if($foto){
            $pathFoto = $foto->store("public/foto_profile");
        }

        $pegawai = DB::transaction(function () use ($pathFoto, $data) {
            $pegawai = Pegawai::create([
                'nip' => $data['nip'],
                'nama' => $data['nama'],
                'tempat_lahir' => $data['tempat_lahir'],
                'tgl_lahir' => $data['tgl_lahir'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'agama' => $data['agama'],
                'no_hp' => $data['no_hp'],
                'npwp' => $data['npwp'] ?? null,
                'foto_pegawai' => $pathFoto,
            ]);

            AlamatPegawai::create([
                'nip' => $pegawai->nip,
                'alamat' => $data['alamat']['alamat'],
                'provinsi' => $data['alamat']['provinsi'],
                'kota' => $data['alamat']['kota'],
            ]);

            JabatanPegawai::create([
                'nip' => $pegawai->nip,
                'jabatan' => $data['jabatan']['jabatan'],
                'golongan' => $data['jabatan']['golongan'],
                'eselon' => $data['jabatan']['eselon'],
                'tempat_tugas' => $data['jabatan']['tempat_tugas'],
                'unit_kerja' => $data['jabatan']['unit_kerja'],
            ]);

            return $pegawai->load(['alamat', 'jabatan']);
        });

        return response()->json([
            'message' => 'pegawai berhasil dibuat',
            'data' => $pegawai,
        ], 201);
    }
}
