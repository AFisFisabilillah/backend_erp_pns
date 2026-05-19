<?php

namespace App\Http\Controllers;

use App\Http\Requests\PegawaiRequest;
use App\Http\Requests\UpdatePegawaiRequest;
use App\Http\Resources\PegawaiDetailResource;
use App\Http\Resources\PegawaiSimpleResource;
use App\Models\AlamatPegawai;
use App\Models\JabatanPegawai;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PegawaiController extends Controller
{
    public function store(PegawaiRequest $request)
    {
        $data = $request->validated();

        $foto = $request->file('foto');
        $pathFoto = null;

        if ($foto) {
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
            'data' => (new PegawaiDetailResource($pegawai))->resolve(),
        ], 201);
    }

    public function update(PegawaiRequest $request, Pegawai $pegawai)
    {
        $data = $request->validated();

        $foto = $request->file('foto');
        $pathFotoBaru = $pegawai->foto_pegawai;

        if ($foto) {
            $pathFotoBaru = $foto->store("public/foto_profile");
        }

        $fotoLama = $pegawai->foto_pegawai;

        $pegawai = DB::transaction(function () use ($pegawai, $data, $pathFotoBaru) {
            $pegawai->update([
                'nip' => $data['nip'],
                'nama' => $data['nama'],
                'tempat_lahir' => $data['tempat_lahir'],
                'tgl_lahir' => $data['tgl_lahir'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'agama' => $data['agama'],
                'no_hp' => $data['no_hp'],
                'npwp' => $data['npwp'] ?? null,
                'foto_pegawai' => $pathFotoBaru,
            ]);

            $pegawai->alamat()->updateOrCreate(
                ['nip' => $pegawai->nip],
                [
                    'alamat' => $data['alamat']['alamat'],
                    'provinsi' => $data['alamat']['provinsi'],
                    'kota' => $data['alamat']['kota'],
                ]
            );

            $pegawai->jabatan()->updateOrCreate(
                ['nip' => $pegawai->nip],
                [
                    'jabatan' => $data['jabatan']['jabatan'],
                    'golongan' => $data['jabatan']['golongan'],
                    'eselon' => $data['jabatan']['eselon'],
                    'tempat_tugas' => $data['jabatan']['tempat_tugas'],
                    'unit_kerja' => $data['jabatan']['unit_kerja'],
                ]
            );

            return $pegawai->fresh()->load(['alamat', 'jabatan']);
        });

        if ($foto && $fotoLama && $fotoLama !== $pathFotoBaru) {
            Storage::delete($fotoLama);
        }

        return response()->json([
            'message' => 'pegawai berhasil diubah',
            'data' => (new PegawaiDetailResource($pegawai))->resolve(),
        ]);
    }

    public function index(Request $request) {
        $size = $request->query("size", 10);

        $search = $request->search;

        $query = Pegawai::query();
       $query->when($search, function ($q, $search) {
           $q->where("nama", "like", "%$search%");
       });

       $data = $query->paginate($size);

       return PegawaiSimpleResource::collection($data);
    }
}
