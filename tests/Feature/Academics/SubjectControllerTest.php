<?php

namespace Tests\Feature\Academics;

use App\Models\Programs;
use App\Models\Role;
use App\Models\Subjects;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubjectControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_subject_using_program_code_and_get_program_relation(): void
    {
        $program = Programs::query()->create([
            'code' => 'BSIT',
            'name' => 'Bachelor of Science in Information Technology',
            'department' => 'Institute of Information Technology',
            'status' => 'active',
        ]);

        $adminUser = $this->createAdminUser();
        Sanctum::actingAs($adminUser);

        $response = $this->postJson('/api/subject', [
            'subject_code' => 'CS105',
            'subject_name' => 'Database Management Systems',
            'units' => 3,
            'type' => 'lab',
            'status' => 'active',
            'program_code' => 'BSIT',
            'year_level' => 1,
            'semester' => '2',
            'school_year' => '2025-2026',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.subject_code', 'CS105')
            ->assertJsonPath('data.subject_name', 'Database Management Systems')
            ->assertJsonPath('data.programs.0.id', $program->id)
            ->assertJsonPath('data.programs.0.code', 'BSIT')
            ->assertJsonPath('data.programs.0.pivot.year_level', 1);

        $createdId = $response->json('data.id');

        $this->assertIsString($createdId);
        $this->assertStringStartsWith('SUBJ-', $createdId);
        $this->assertDatabaseHas('subjects', [
            'id' => $createdId,
            'subject_code' => 'CS105',
            'subject_name' => 'Database Management Systems',
        ]);
        $this->assertDatabaseHas('program_subject', [
            'subject_id' => $createdId,
            'program_id' => $program->id,
            'year_level' => 1,
            'semester' => '2',
            'school_year' => '2025-2026',
        ]);
    }

    public function test_index_auto_syncs_subjects_and_returns_programs_for_admin(): void
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
            'https://registrar.example.test/api/subjects' => Http::response([
                [
                    'id' => 5,
                    'subject_code' => 'CS105',
                    'subject_name' => 'Database Management Systems',
                    'units' => 3,
                    'type' => 'lab',
                    'status' => 'active',
                    'programs' => [
                        [
                            'code' => 'BSIT',
                            'pivot' => [
                                'year_level' => 1,
                                'semester' => '2',
                                'school_year' => '2025-2026',
                                'status' => 'active',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $adminUser = $this->createAdminUser();
        Sanctum::actingAs($adminUser);

        $response = $this->getJson('/api/subject');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'fetch successfully')
            ->assertJsonPath('data.0.subject_code', 'CS105')
            ->assertJsonPath('data.0.programs.0.code', 'BSIT')
            ->assertJsonPath('data.0.programs.0.pivot.school_year', '2025-2026');

        $syncedId = $response->json('data.0.id');
        $this->assertIsString($syncedId);
        $this->assertStringStartsWith('SUBJ-', $syncedId);
        $this->assertDatabaseHas('subjects', [
            'subject_code' => 'CS105',
            'subject_name' => 'Database Management Systems',
            'units' => 3,
        ]);
        $this->assertDatabaseHas('program_subject', [
            'subject_id' => $syncedId,
            'program_id' => $program->id,
            'year_level' => 1,
            'semester' => '2',
            'school_year' => '2025-2026',
        ]);
        $this->assertSame(1, Subjects::query()->count());

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://registrar.example.test/api/subjects'
                && $request->hasHeader('Authorization', 'Bearer sync-token');
        });
    }

    public function test_store_rejects_invalid_program_id(): void
    {
        $adminUser = $this->createAdminUser();
        Sanctum::actingAs($adminUser);

        $response = $this->postJson('/api/subject', [
            'subject_code' => 'CS103',
            'subject_name' => 'Computer Programming 2',
            'units' => 3,
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
