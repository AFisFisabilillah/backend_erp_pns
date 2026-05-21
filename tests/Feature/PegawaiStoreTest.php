<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PegawaiStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->create());
        Storage::fake('public');
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
            'foto' => UploadedFile::fake()->image('pegawai.jpg'),
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

        $urlFoto = $response->json('data.foto_pegawai');
        $this->assertStringStartsWith(asset('storage/foto_profile/'), $urlFoto);

        $pathFoto = str_replace(asset('storage') . '/', '', $urlFoto);
        Storage::disk('public')->assertExists($pathFoto);

        $this->assertDatabaseHas('pegawai', [
            'nip' => $payload['nip'],
            'nama' => $payload['nama'],
            'foto_pegawai' => $pathFoto,
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

    public function test_can_show_pegawai_detail(): void
    {
        $payload = $this->pegawaiPayload();

        $this->postJson('/api/pegawai', $payload)->assertCreated();

        $response = $this->getJson('/api/pegawai/'.$payload['nip']);

        $response
            ->assertOk()
            ->assertJsonPath('data.nip', $payload['nip'])
            ->assertJsonPath('data.nama', $payload['nama'])
            ->assertJsonPath('data.alamat.alamat', $payload['alamat']['alamat'])
            ->assertJsonPath('data.alamat.kota', $payload['alamat']['kota'])
            ->assertJsonPath('data.alamat.provinsi', $payload['alamat']['provinsi'])
            ->assertJsonPath('data.jabatan.golongan', $payload['jabatan']['golongan'])
            ->assertJsonPath('data.jabatan.eselon', $payload['jabatan']['eselon'])
            ->assertJsonPath('data.jabatan.jabatan', $payload['jabatan']['jabatan'])
            ->assertJsonPath('data.jabatan.tempat_tugas', $payload['jabatan']['tempat_tugas'])
            ->assertJsonPath('data.jabatan.unit_kerja', $payload['jabatan']['unit_kerja']);
    }

    public function test_can_soft_delete_pegawai(): void
    {
        $payload = $this->pegawaiPayload();

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

    public function test_can_filter_pegawai_by_unit_kerja(): void
    {
        $sdmPegawai1 = $this->pegawaiPayload([
            'nip' => '198901012026051011',
            'nama' => 'Sinta Putri',
            'npwp' => '12.345.678.9-012.346',
        ]);
        $sdmPegawai2 = $this->pegawaiPayload([
            'nip' => '198901012026051012',
            'nama' => 'Raka Pratama',
            'npwp' => '12.345.678.9-012.347',
        ]);
        $keuanganPegawai = $this->pegawaiPayload([
            'nip' => '198901012026051013',
            'nama' => 'Dina Lestari',
            'npwp' => '12.345.678.9-012.348',
            'jabatan' => [
                'golongan' => 'III/a',
                'eselon' => 'IV/a',
                'jabatan' => 'Analis Anggaran',
                'tempat_tugas' => 'Kantor Pusat',
                'unit_kerja' => 'Keuangan',
            ],
        ]);

        $this->postJson('/api/pegawai', $sdmPegawai1)->assertCreated();
        $this->postJson('/api/pegawai', $sdmPegawai2)->assertCreated();
        $this->postJson('/api/pegawai', $keuanganPegawai)->assertCreated();

        $response = $this->getJson('/api/pegawai?unit_kerja=SDM&size=10');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'nip' => $sdmPegawai1['nip'],
                'nama' => $sdmPegawai1['nama'],
                'unit_kerja' => 'SDM',
            ])
            ->assertJsonFragment([
                'nip' => $sdmPegawai2['nip'],
                'nama' => $sdmPegawai2['nama'],
                'unit_kerja' => 'SDM',
            ])
            ->assertJsonMissing([
                'nip' => $keuanganPegawai['nip'],
                'nama' => $keuanganPegawai['nama'],
                'unit_kerja' => 'Keuangan',
            ]);
    }

    public function test_can_get_distinct_unit_kerja_labels(): void
    {
        $sdmPegawai1 = $this->pegawaiPayload([
            'nip' => '198901012026051021',
            'npwp' => '12.345.678.9-012.356',
        ]);
        $sdmPegawai2 = $this->pegawaiPayload([
            'nip' => '198901012026051022',
            'npwp' => '12.345.678.9-012.357',
        ]);
        $keuanganPegawai = $this->pegawaiPayload([
            'nip' => '198901012026051023',
            'npwp' => '12.345.678.9-012.358',
            'jabatan' => [
                'unit_kerja' => 'Keuangan',
            ],
        ]);

        $this->postJson('/api/pegawai', $sdmPegawai1)->assertCreated();
        $this->postJson('/api/pegawai', $sdmPegawai2)->assertCreated();
        $this->postJson('/api/pegawai', $keuanganPegawai)->assertCreated();

        $response = $this->getJson('/api/unit-kerja');

        $response
            ->assertOk()
            ->assertExactJson([
                [
                    'label' => 'Keuangan',
                    'value' => 'Keuangan',
                ],
                [
                    'label' => 'SDM',
                    'value' => 'SDM',
                ],
            ]);
    }

    public function test_can_get_dashboard_summary(): void
    {
        $pegawaiLakiLakiGolonganTigaA = $this->pegawaiPayload([
            'nip' => '198901012026051031',
            'npwp' => '12.345.678.9-012.366',
            'jenis_kelamin' => 'L',
            'jabatan' => [
                'golongan' => 'III/a',
            ],
        ]);
        $pegawaiLakiLakiGolonganTigaB = $this->pegawaiPayload([
            'nip' => '198901012026051032',
            'npwp' => '12.345.678.9-012.367',
            'jenis_kelamin' => 'L',
            'jabatan' => [
                'golongan' => 'III/b',
            ],
        ]);
        $pegawaiPerempuanGolonganTigaA = $this->pegawaiPayload([
            'nip' => '198901012026051033',
            'npwp' => '12.345.678.9-012.368',
            'jenis_kelamin' => 'P',
            'jabatan' => [
                'golongan' => 'III/a',
            ],
        ]);

        $this->postJson('/api/pegawai', $pegawaiLakiLakiGolonganTigaA)->assertCreated();
        $this->postJson('/api/pegawai', $pegawaiLakiLakiGolonganTigaB)->assertCreated();
        $this->postJson('/api/pegawai', $pegawaiPerempuanGolonganTigaA)->assertCreated();

        $response = $this->getJson('/api/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath('data.total_pegawai', 3)
            ->assertJsonPath('data.bar_chart_golongan.0.label', 'III/a')
            ->assertJsonPath('data.bar_chart_golongan.0.value', 'III/a')
            ->assertJsonPath('data.bar_chart_golongan.0.total', 2)
            ->assertJsonPath('data.bar_chart_golongan.1.label', 'III/b')
            ->assertJsonPath('data.bar_chart_golongan.1.value', 'III/b')
            ->assertJsonPath('data.bar_chart_golongan.1.total', 1)
            ->assertJsonPath('data.pie_chart_jenis_kelamin.0.label', 'Laki-laki')
            ->assertJsonPath('data.pie_chart_jenis_kelamin.0.value', 'L')
            ->assertJsonPath('data.pie_chart_jenis_kelamin.0.total', 2)
            ->assertJsonPath('data.pie_chart_jenis_kelamin.1.label', 'Perempuan')
            ->assertJsonPath('data.pie_chart_jenis_kelamin.1.value', 'P')
            ->assertJsonPath('data.pie_chart_jenis_kelamin.1.total', 1);
    }

    private function pegawaiPayload(array $overrides = []): array
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

        return array_replace_recursive($payload, $overrides);
    }
}
