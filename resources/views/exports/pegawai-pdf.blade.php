<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pegawai</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 18px 20px 20px 20px;
        }

        body {
            font-family: "Times New Roman", serif;
            color: #111827;
            font-size: 12px;
            margin: 0;
        }

        .title {
            text-align: center;
            margin-bottom: 12px;
        }

        .title h1 {
            font-size: 18px;
            margin: 0 0 6px 0;
            font-weight: 700;
        }

        .title h2 {
            font-size: 14px;
            margin: 0;
            font-weight: 700;
        }

        .title p {
            font-size: 12px;
            margin: 6px 0 0 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #808080;
            padding: 4px 6px;
            vertical-align: top;
            line-height: 1.45;
            word-wrap: break-word;
        }

        th {
            background: #1f3b6d;
            color: #ffffff;
            text-align: center;
            font-weight: 700;
        }

        td.number,
        td.center {
            text-align: center;
        }

        .w-no { width: 3%; }
        .w-nip { width: 9%; }
        .w-nama { width: 8%; }
        .w-tempat { width: 9%; }
        .w-alamat { width: 17%; }
        .w-tgl { width: 8%; }
        .w-lp { width: 4%; }
        .w-gol { width: 4%; }
        .w-eselon { width: 5%; }
        .w-jabatan { width: 15%; }
        .w-tugas { width: 7%; }
        .w-agama { width: 6%; }
        .w-unit { width: 6%; }
        .w-hp { width: 4%; }
        .w-npwp { width: 6%; }
    </style>
</head>
<body>
<div class="title">
    <h1>DAFTAR PEGAWAI</h1>
    <h2>{{ $namaInstansi }}</h2>
    @if ($unitKerja)
        <p>Unit Kerja: {{ $unitKerja }}</p>
    @endif
</div>

<table>
    <thead>
    <tr>
        <th class="w-no">No</th>
        <th class="w-nip">NIP</th>
        <th class="w-nama">Nama</th>
        <th class="w-tempat">Tempat Lahir</th>
        <th class="w-alamat">Alamat</th>
        <th class="w-tgl">Tgl Lahir</th>
        <th class="w-lp">L/P</th>
        <th class="w-gol">Gol</th>
        <th class="w-eselon">Eselon</th>
        <th class="w-jabatan">Jabatan</th>
        <th class="w-tugas">Tempat Tugas</th>
        <th class="w-agama">Agama</th>
        <th class="w-unit">Unit Kerja</th>
        <th class="w-hp">No. HP</th>
        <th class="w-npwp">NPWP</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($pegawai as $item)
        <tr>
            <td class="number">{{ $loop->iteration }}</td>
            <td>{{ $item->nip }}</td>
            <td>{{ $item->nama }}</td>
            <td>{{ $item->tempat_lahir }}</td>
            <td>{{ $item->alamat?->alamat }}</td>
            <td>{{ $item->tgl_lahir ? \Illuminate\Support\Carbon::parse($item->tgl_lahir)->format('d-m-Y') : '' }}</td>
            <td class="center">{{ $item->jenis_kelamin }}</td>
            <td class="center">{{ $item->jabatan?->golongan }}</td>
            <td class="center">{{ $item->jabatan?->eselon }}</td>
            <td>{{ $item->jabatan?->jabatan }}</td>
            <td>{{ $item->jabatan?->tempat_tugas }}</td>
            <td>{{ $item->agama }}</td>
            <td>{{ $item->jabatan?->unit_kerja }}</td>
            <td>{{ $item->no_hp }}</td>
            <td>{{ $item->npwp }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="15" class="center">Data pegawai tidak tersedia.</td>
        </tr>
    @endforelse
    </tbody>
</table>
</body>
</html>
