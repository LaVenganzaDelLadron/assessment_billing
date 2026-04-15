<?php

namespace Tests\Feature\Auth;

use App\Models\Programs;
use App\Models\Role;
use App\Models\Students;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_with_role_can_authenticate_using_the_local_login_endpoint(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        UserRole::query()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Login successfully')
            ->assertJsonPath('user.email', $user->email);

        $this->assertIsString($response->json('token'));
        $this->assertNotSame('', $response->json('token'));
    }

    public function test_users_without_role_cannot_authenticate(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response
            ->assertForbidden()
            ->assertJsonPath('message', 'User has no assigned role. Access denied.');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid email or password');
    }

    public function test_students_can_authenticate_using_student_id_and_fixed_password(): void
    {
        $program = Programs::query()->create([
            'code' => 'BSIT',
            'name' => 'Bachelor of Science in Information Technology',
            'department' => 'Institute of Information Technology',
            'status' => 'active',
        ]);

        $student = Students::query()->create([
            'name' => 'Student User',
            'program_id' => $program->id,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'student_id' => $student->id,
            'password' => '12345678',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Login successfully')
            ->assertJsonPath('user.id', $student->id)
            ->assertJsonPath('role', 'student');

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'name' => 'Student User',
            'email' => strtolower($student->id).'@student.local',
        ]);
        $this->assertDatabaseHas('role', [
            'name' => 'student',
        ]);
    }

    public function test_students_can_not_authenticate_using_email_on_local_login_endpoint(): void
    {
        $studentRole = Role::query()->create([
            'name' => 'student',
        ]);
        $studentUser = User::factory()->create([
            'email' => 'student@example.com',
        ]);
        UserRole::query()->create([
            'user_id' => $studentUser->id,
            'role_id' => $studentRole->id,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'student@example.com',
            'password' => 'password',
        ]);

        $response
            ->assertForbidden()
            ->assertJsonPath('message', 'Students must login using student ID.');
    }

    public function test_users_can_logout_from_the_api(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token');

        $response = $this
            ->withToken($token->plainTextToken)
            ->postJson('/api/auth/logout');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Logged out successfully');

        $this->assertFalse(PersonalAccessToken::query()->whereKey($token->accessToken->getKey())->exists());
    }
}
