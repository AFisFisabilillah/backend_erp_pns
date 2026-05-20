<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PegawaiStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->create());
    }

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

    public function test_can_update_pegawai_with_same_npwp_and_new_nip(): void
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

        $this->postJson('/api/pegawai', $payload)->assertCreated();

        $updatePayload = [
            'nip' => '198901012026051009',
            'nama' => 'Budi Santoso Update',
            'tempat_lahir' => 'Bandung',
            'tgl_lahir' => '1989-01-01',
            'jenis_kelamin' => 'L',
            'agama' => 'Islam',
            'no_hp' => '081298765432',
            'npwp' => '12.345.678.9-012.345',
            'alamat' => [
                'alamat' => 'Jl. Asia Afrika No. 10',
                'kota' => 'Bandung',
                'provinsi' => 'Jawa Barat',
            ],
            'jabatan' => [
                'golongan' => 'III/b',
                'eselon' => 'III/a',
                'jabatan' => 'Analis Kepegawaian',
                'tempat_tugas' => 'BKD',
                'unit_kerja' => 'Kepegawaian',
            ],
        ];

        $response = $this->postJson('/api/pegawai/'.$payload['nip'], $updatePayload);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'pegawai berhasil diubah')
            ->assertJsonPath('data.nip', $updatePayload['nip'])
            ->assertJsonPath('data.nama', $updatePayload['nama'])
            ->assertJsonPath('data.alamat.kota', $updatePayload['alamat']['kota'])
            ->assertJsonPath('data.jabatan.jabatan', $updatePayload['jabatan']['jabatan']);

        $this->assertDatabaseMissing('pegawai', [
            'nip' => $payload['nip'],
        ]);

        $this->assertDatabaseHas('pegawai', [
            'nip' => $updatePayload['nip'],
            'nama' => $updatePayload['nama'],
            'npwp' => $updatePayload['npwp'],
        ]);

        $this->assertDatabaseHas('alamat_pegawai', [
            'nip' => $updatePayload['nip'],
            'kota' => $updatePayload['alamat']['kota'],
            'provinsi' => $updatePayload['alamat']['provinsi'],
        ]);

        $this->assertDatabaseHas('jabatan_pegawai', [
            'nip' => $updatePayload['nip'],
            'jabatan' => $updatePayload['jabatan']['jabatan'],
            'unit_kerja' => $updatePayload['jabatan']['unit_kerja'],
        ]);
    }

    public function test_can_soft_delete_pegawai(): void
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

        $this->postJson('/api/pegawai', $payload)->assertCreated();

        $response = $this->deleteJson('/api/pegawai/delete', [
            'id_pegawai' => [$payload['nip']],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', '1 pegawai berhasil dihapus');

        $this->assertSoftDeleted('pegawai', [
            'nip' => $payload['nip'],
        ]);
    }
}
