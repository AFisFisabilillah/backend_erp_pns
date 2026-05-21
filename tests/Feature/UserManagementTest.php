<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->create());
        Storage::fake('public');
    }

    public function test_can_store_user(): void
    {
        $payload = [
            'name' => 'operator',
            'fullname' => 'Operator Satu',
            'email' => 'operator1@example.com',
            'password' => 'password123',
            'profile' => UploadedFile::fake()->image('profile.jpg'),
        ];

        $response = $this->postJson('/api/user', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'user berhasil dibuat')
            ->assertJsonPath('data.name', $payload['name'])
            ->assertJsonPath('data.fullname', $payload['fullname'])
            ->assertJsonPath('data.email', $payload['email']);

        $urlProfile = $response->json('data.profile');
        $this->assertStringStartsWith(asset('storage/profile_user/'), $urlProfile);

        $pathProfile = str_replace(asset('storage') . '/', '', $urlProfile);
        Storage::disk('public')->assertExists($pathProfile);

        $this->assertDatabaseHas('users', [
            'name' => $payload['name'],
            'fullname' => $payload['fullname'],
            'email' => $payload['email'],
            'profile' => $pathProfile,
        ]);
    }

    public function test_can_update_user_with_same_email(): void
    {
        $user = User::factory()->create([
            'name' => 'operator',
            'fullname' => 'Operator Satu',
            'email' => 'operator1@example.com',
        ]);

        $payload = [
            'name' => 'operator-baru',
            'fullname' => 'Operator Baru',
            'email' => 'operator1@example.com',
        ];

        $response = $this->postJson('/api/user/' . $user->id, $payload);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'user berhasil diubah')
            ->assertJsonPath('data.name', $payload['name'])
            ->assertJsonPath('data.fullname', $payload['fullname'])
            ->assertJsonPath('data.email', $payload['email']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $payload['name'],
            'fullname' => $payload['fullname'],
            'email' => $payload['email'],
        ]);
    }

    public function test_can_soft_delete_and_restore_user(): void
    {
        $user = User::factory()->create();

        $deleteResponse = $this->deleteJson('/api/user/delete', [
            'id_user' => [$user->id],
        ]);

        $deleteResponse
            ->assertOk()
            ->assertJsonPath('message', '1 user berhasil dihapus');

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);

        $trashResponse = $this->getJson('/api/user/trash');
        $trashResponse
            ->assertOk()
            ->assertJsonFragment([
                'id' => $user->id,
                'email' => $user->email,
            ]);

        $restoreResponse = $this->patchJson('/api/user/restore', [
            'id_user' => [$user->id],
        ]);

        $restoreResponse
            ->assertOk()
            ->assertJsonPath('message', '1 user berhasil direstore');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_can_force_delete_user(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $response = $this->deleteJson('/api/user/forceDelete', [
            'id_user' => [$user->id],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', '1 user berhasil hapus');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
}
