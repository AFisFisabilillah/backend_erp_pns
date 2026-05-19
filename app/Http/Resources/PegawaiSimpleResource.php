<?php

namespace App\Http\Resources;

use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Pegawai */
class PegawaiSimpleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'nip' => $this->nip,
            'nama' => $this->nama,
            'tempat_lahir' => $this->tempat_lahir,
            'tgl_lahir' => $this->tgl_lahir,
            'jenis_kelamin' => $this->jenis_kelamin,
            'agama' => $this->agama,
            'no_hp' => $this->no_hp,
            'npwp' => $this->npwp,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'foto_pegawai' => $this->foto_pegawai,
        ];
    }
}
