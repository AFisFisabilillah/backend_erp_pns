<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('jabatan_pegawai')]
#[Fillable(["nip", "golongan", "eselon", "jabatan", "tempat_tugas", "unit_kerja"])]
class JabatanPegawai extends Model
{
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, "nip", "nip");
    }
}
