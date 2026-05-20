<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PegawaiPdfExporter
{
    public function download(Collection $pegawai, array $context = [])
    {

        $pdf = Pdf::loadView('exports.pegawai-pdf', [
            'pegawai' => $pegawai,
            'namaInstansi' => $context['nama_instansi'] ?? config('app.name'),
            'unitKerja' => $context['unit_kerja'] ?? null,
        ])->setPaper('a4', 'landscape');

        $unitKerjaSlug = \Str::slug($context['unit_kerja'] ?? 'semua-unit');

        return $pdf->download("daftar-pegawai-$unitKerjaSlug.pdf");
    }

}
