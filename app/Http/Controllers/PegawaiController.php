<?php

namespace App\Http\Controllers;

use App\Http\Requests\PegawaiRequest;
use App\Http\Requests\UpdatePegawaiRequest;
use App\Http\Resources\PegawaiDetailResource;
use App\Http\Resources\PegawaiSimpleResource;
use App\Models\AlamatPegawai;
use App\Models\JabatanPegawai;
use App\Models\Pegawai;
use App\Services\PegawaiPdfExporter;
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
            $pathFoto = $foto->store('foto_profile', 'public');
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

    public function update(UpdatePegawaiRequest $request, Pegawai $pegawai)
    {
        $data = $request->validated();

        $foto = $request->file('foto');
        $pathFotoBaru = $pegawai->foto_pegawai;

        if ($foto) {
            $pathFotoBaru = $foto->store('foto_profile', 'public');
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
            Storage::disk('public')->delete($fotoLama);
            Storage::delete($fotoLama);
        }

        return response()->json([
            'message' => 'pegawai berhasil diubah',
            'data' => (new PegawaiDetailResource($pegawai))->resolve(),
        ]);
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'id_pegawai' => 'required|array',
            'id_pegawai.*' => 'required'
        ]);
        $pegawai = Pegawai::whereIn('nip', $validated['id_pegawai'])->delete();

        return response()->json([
            'message' => $pegawai . ' pegawai berhasil dihapus',
        ]);
    }

    public function restore(Request $request)
    {
        $validated = $request->validate([
            'id_pegawai' => 'required|array',
            'id_pegawai.*' => 'required'
        ]);
        $pegawai = Pegawai::onlyTrashed()->whereIn('nip', $validated['id_pegawai'])->get();
        foreach ($pegawai as $item) {
            $item->restore();
        }

        return response()->json([
            'message' => $pegawai->count() . " pegawai berhasil direstore",
        ]);
    }

    public function forceDelete(Request $request)
    {
        $validated = $request->validate([
            'id_pegawai' => 'required|array',
            'id_pegawai.*' => 'required'
        ]);
        $pegawai = Pegawai::onlyTrashed()->whereIn('nip', $validated['id_pegawai'])->get();
        foreach ($pegawai as $item) {
            $item->forceDelete();
        }
        return response()->json([
            'message' => $pegawai->count() . " pegawai berhasil hapus",
        ]);
    }

    public function trash(){
        return PegawaiSimpleResource::collection(Pegawai::onlyTrashed()->get());
    }

    public function index(Request $request)
    {
        $size = $request->query("size", 10);
        $search = $request->search;
        $unitKerja = $request->query('unit_kerja');

        $query = Pegawai::with('jabatan');
        $query->when($search, function ($q, $search) {
            $q->where("nama", "like", "%$search%");
        });
        $query->when($unitKerja, function ($q, $unitKerja) {
            $q->whereHas('jabatan', function ($jabatanQuery) use ($unitKerja) {
                $jabatanQuery->where('unit_kerja', 'like', "%$unitKerja%");
            });
        });

        $data = $query->paginate($size);

        return PegawaiSimpleResource::collection($data);
    }

    public function show(Pegawai $pegawai)
    {
        return new PegawaiDetailResource($pegawai->load(['alamat', 'jabatan']));
    }

    public function dashboard()
    {
        $golongan = Pegawai::query()
            ->join('jabatan_pegawai', 'pegawai.nip', '=', 'jabatan_pegawai.nip')
            ->whereNotNull('jabatan_pegawai.golongan')
            ->where('jabatan_pegawai.golongan', '!=', '')
            ->select('jabatan_pegawai.golongan', DB::raw('count(*) as total'))
            ->groupBy('jabatan_pegawai.golongan')
            ->orderBy('jabatan_pegawai.golongan')
            ->get()
            ->map(fn ($item) => [
                'label' => $item->golongan,
                'value' => $item->golongan,
                'total' => (int) $item->total,
            ]);

        $jenisKelamin = Pegawai::query()
            ->select('jenis_kelamin', DB::raw('count(*) as total'))
            ->whereIn('jenis_kelamin', ['L', 'P'])
            ->groupBy('jenis_kelamin')
            ->pluck('total', 'jenis_kelamin');

        return response()->json([
            'data' => [
                'total_pegawai' => Pegawai::count(),
                'bar_chart_golongan' => $golongan,
                'pie_chart_jenis_kelamin' => [
                    [
                        'label' => 'Laki-laki',
                        'value' => 'L',
                        'total' => (int) ($jenisKelamin['L'] ?? 0),
                    ],
                    [
                        'label' => 'Perempuan',
                        'value' => 'P',
                        'total' => (int) ($jenisKelamin['P'] ?? 0),
                    ],
                ],
            ],
        ]);
    }

    public function exportPdf(Request $request, PegawaiPdfExporter $exporter)
    {
        $search = $request->query('search');
        $unitKerja = $request->query('unit_kerja');
        $namaInstansi = $request->query('nama_instansi', config('app.name'));

        $pegawai = Pegawai::with(['alamat', 'jabatan'])
            ->when($search, function ($q, $search) {
                $q->where('nama', 'like', "%$search%");
            })
            ->when($unitKerja, function ($q, $unitKerja) {
                $q->whereHas('jabatan', function ($jabatanQuery) use ($unitKerja) {
                    $jabatanQuery->where('unit_kerja', 'like', "%$unitKerja%");
                });
            })
            ->orderBy('nama')
            ->get();

        return $exporter->download($pegawai, [
            'nama_instansi' => $namaInstansi,
            'unit_kerja' => $unitKerja,
        ]);
    }

    public function labelUnitKerja(Request $request){
        $unitKerja = JabatanPegawai::query()
            ->whereNotNull('unit_kerja')
            ->where('unit_kerja', '!=', '')
            ->select('unit_kerja')
            ->distinct()
            ->orderBy('unit_kerja')
            ->pluck('unit_kerja')
            ->map(fn ($unitKerja) => [
                'label' => $unitKerja,
                'value' => $unitKerja,
            ])
            ->values();

        return response()->json($unitKerja);
    }
}
