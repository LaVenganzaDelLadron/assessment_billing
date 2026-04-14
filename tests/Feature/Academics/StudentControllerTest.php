<?php

namespace Tests\Feature\Academics;

use App\Models\Programs;
use App\Models\Role;
use App\Models\Students;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_student_using_program_code_and_get_program_relation(): void
    {
        $program = Programs::query()->create([
            'code' => 'BSIT',
            'name' => 'Bachelor of Science in Information Technology',
            'department' => 'Institute of Information Technology',
            'status' => 'active',
        ]);

        $adminUser = $this->createAdminUser();
        Sanctum::actingAs($adminUser);

        $response = $this->postJson('/api/student', [
            'name' => 'Jane Doe',
            'program_code' => 'BSIT',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('name', 'Jane Doe')
            ->assertJsonPath('program_id', $program->id)
            ->assertJsonPath('program.id', $program->id)
            ->assertJsonPath('program.code', 'BSIT');

        $createdId = $response->json('id');

        $this->assertIsString($createdId);
        $this->assertStringStartsWith('STU-', $createdId);
        $this->assertDatabaseHas('student', [
            'id' => $createdId,
            'name' => 'Jane Doe',
            'program_id' => $program->id,
        ]);
    }

    public function test_index_auto_syncs_students_and_returns_program_relation_for_admin(): void
    {
        config()->set('services.registrar.endpoint_url', 'https://registrar.example.test/api/');
        config()->set('services.registrar.token', 'sync-token');

        $program = Programs::query()->create([
            'code' => 'BSIT',
            'name' => 'Bachelor of Science in Information Technology',
            'department' => 'Institute of Information Technology',
            'status' => 'active',
        ]);

        Http::fake([
            'https://registrar.example.test/api/students' => Http::response([
                [
                    'id' => 1,
                    'name' => 'Test Student',
                    'program_code' => 'BSIT',
                ],
            ], 200),
        ]);

        $adminUser = $this->createAdminUser();
        Sanctum::actingAs($adminUser);

        $response = $this->getJson('/api/student');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'fetch successfully')
            ->assertJsonPath('data.0.name', 'Test Student')
            ->assertJsonPath('data.0.program.code', 'BSIT');

        $syncedId = $response->json('data.0.id');
        $this->assertIsString($syncedId);
        $this->assertStringStartsWith('STU-', $syncedId);
        $this->assertDatabaseHas('student', [
            'name' => 'Test Student',
            'program_id' => $program->id,
        ]);
        $this->assertSame(1, Students::query()->count());

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://registrar.example.test/api/students'
                && $request->hasHeader('Authorization', 'Bearer sync-token');
        });
    }

    public function test_store_rejects_invalid_program_id(): void
    {
        $adminUser = $this->createAdminUser();
        Sanctum::actingAs($adminUser);

        $response = $this->postJson('/api/student', [
            'name' => 'Invalid Program Student',
            'program_id' => 'PROG-DOES-NOT-EXIST',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('program_id');
    }

    private function createAdminUser(): User
    {
        $adminRole = Role::create([
            'name' => 'admin',
        ]);

        $adminUser = User::factory()->create();
        UserRole::create([
            'user_id' => $adminUser->id,
            'role_id' => $adminRole->id,
        ]);

        return $adminUser;
    }
}
