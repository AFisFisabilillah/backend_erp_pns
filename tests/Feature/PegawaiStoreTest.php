<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PegawaiStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_store_pegawai_with_alamat_and_jabatan(): void
    {
        $payload = [
            'nip' => '198901012026051001',
            'nama' => 'Budi Santoso',
            'tempat_lahir' => 'Jakarta',
            'tgl_lahir' => '1989-01-01',
            'jenis_kelamin' => 'L',
            'agama' => 'Islam',
            'no_hp' => '081234567890',
            'npwp' => '12.345.678.9-012.345',
            'alamat' => [
                'alamat' => 'Jl. Merdeka No. 1',
                'kota' => 'Jakarta Selatan',
                'provinsi' => 'DKI Jakarta',
            ],
            'jabatan' => [
                'golongan' => 'III/a',
                'eselon' => 'IV/a',
                'jabatan' => 'Staf Administrasi',
                'tempat_tugas' => 'Kantor Pusat',
                'unit_kerja' => 'SDM',
            ],
        ];

        $response = $this->postJson('/api/pegawai', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'pegawai berhasil dibuat')
            ->assertJsonPath('data.nip', $payload['nip'])
            ->assertJsonPath('data.alamat.kota', $payload['alamat']['kota'])
            ->assertJsonPath('data.jabatan.jabatan', $payload['jabatan']['jabatan']);

        $this->assertDatabaseHas('pegawai', [
            'nip' => $payload['nip'],
            'nama' => $payload['nama'],
        ]);

        $this->assertDatabaseHas('alamat_pegawai', [
            'nip' => $payload['nip'],
            'kota' => $payload['alamat']['kota'],
            'provinsi' => $payload['alamat']['provinsi'],
        ]);

        $this->assertDatabaseHas('jabatan_pegawai', [
            'nip' => $payload['nip'],
            'jabatan' => $payload['jabatan']['jabatan'],
            'unit_kerja' => $payload['jabatan']['unit_kerja'],
        ]);
    }
}
