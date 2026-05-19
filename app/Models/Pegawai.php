<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Table(name: 'pegawai')]
#[Fillable(["nip", "nama", "tempat_lahir", "tgl_lahir", "jenis_kelamin", "agama", "no_hp", "npwp"])]
class Pegawai extends Model
{
    public $incrementing = false;
    public function alamat(): HasOne
    {
        return $this->hasOne(AlamatPegawai::class);
    }
}
