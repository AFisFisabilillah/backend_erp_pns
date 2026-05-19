<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Table('pegawai')]
#[Fillable(["nip",'foto_pegawai', "nama", "tempat_lahir", "tgl_lahir", "jenis_kelamin", "agama", "no_hp", "npwp"])]
class Pegawai extends Model
{
    protected $primaryKey = 'nip';
    protected $keyType = 'string';
    public $incrementing = false;

    public function alamat(): HasOne
    {
        return $this->hasOne(AlamatPegawai::class, 'nip', 'nip');
    }

    public function jabatan(): HasOne
    {
        return $this->hasOne(JabatanPegawai::class, 'nip', 'nip');
    }
}
