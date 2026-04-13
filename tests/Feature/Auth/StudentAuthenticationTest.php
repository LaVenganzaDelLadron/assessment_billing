<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StudentAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_can_authenticate_through_the_admission_api_and_receive_a_local_token(): void
    {
        config([
            'services.admission.login_url' => 'https://admission-api.example.test/api/auth/login',
        ]);

        Http::fake([
            config('services.admission.login_url') => Http::response([
                'message' => 'Login successful',
                'user' => [
                    'id' => 8,
                    'name' => 'Dark Glitch',
                    'role' => 'student',
                ],
                'applicant' => [
                    'id' => 6,
                    'user_id' => 8,
                    'first_name' => 'Dark',
                    'last_name' => 'Glitch',
                    'middle_name' => 'Dev',
                    'email' => 'darkglitch5417@gmail.com',
                    'phone_number' => '09972977654',
                    'date_of_birth' => '2000-05-14T16:00:00.000000Z',
                    'address' => '123 Main Street, Manila, Philippines',
                    'status' => 'enrolled',
                    'course_id' => 1,
                    'student' => [
                        'id' => 99,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/auth/student-login', [
            'email' => 'darkglitch5417@gmail.com',
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Student login successful')
            ->assertJsonPath('user.email', 'darkglitch5417@gmail.com')
            ->assertJsonPath('user.admission_role', 'student');

        $this->assertIsString($response->json('token'));
        $this->assertNotSame('', $response->json('token'));

        $this->assertDatabaseHas('users', [
            'email' => 'darkglitch5417@gmail.com',
            'name' => 'Dark Glitch',
            'admission_user_id' => '8',
            'admission_role' => 'student',
        ]);
        $this->assertDatabaseHas('role', [
            'name' => 'student',
        ]);

        $user = User::query()->where('email', 'darkglitch5417@gmail.com')->firstOrFail();
        $studentRole = Role::query()->where('name', 'student')->firstOrFail();

        $this->assertDatabaseHas('user_role', [
            'user_id' => $user->id,
            'role_id' => $studentRole->id,
        ]);
        $this->assertSame('09972977654', data_get($user->admission_profile, 'applicant.phone_number'));
    }

    public function test_successful_applicant_login_still_syncs_the_profile_before_billing_access_is_denied(): void
    {
        config([
            'services.admission.login_url' => 'https://admission-api.example.test/api/auth/login',
        ]);

        Http::fake([
            config('services.admission.login_url') => Http::response([
                'message' => 'Login successful',
                'user' => [
                    'id' => 6,
                    'name' => 'Juan Dela Cruz',
                    'role' => 'applicant',
                ],
                'applicant' => [
                    'id' => 4,
                    'user_id' => 6,
                    'first_name' => 'Juan',
                    'last_name' => 'Dela Cruz',
                    'middle_name' => 'Santos',
                    'email' => 'juan.delacruz@example.com',
                    'phone_number' => '09123456789',
                    'date_of_birth' => '2000-05-14T16:00:00.000000Z',
                    'address' => '123 Main Street, Manila, Philippines',
                    'status' => 'pending',
                    'course_id' => 1,
                    'student' => null,
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/auth/student-login', [
            'email' => 'juan.delacruz@example.com',
            'password' => 'password',
        ]);

        $response
            ->assertForbidden()
            ->assertJsonPath('message', 'Only admitted students can access the billing system.');

        $this->assertDatabaseHas('users', [
            'email' => 'juan.delacruz@example.com',
            'name' => 'Juan Dela Cruz',
            'admission_user_id' => '6',
            'admission_role' => 'applicant',
        ]);
    }

    public function test_student_login_returns_the_admission_api_error_for_invalid_credentials(): void
    {
        config([
            'services.admission.login_url' => 'https://admission-api.example.test/api/auth/login',
        ]);

        Http::fake([
            config('services.admission.login_url') => Http::response([
                'message' => 'Invalid student credentials',
            ], 401),
        ]);

        $response = $this->postJson('/api/auth/student-login', [
            'email' => 'student@example.com',
            'password' => 'wrong-password',
        ]);

        $response
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid student credentials');
    }

    public function test_students_can_not_use_the_local_login_endpoint(): void
    {
        $user = User::factory()->create([
            'email' => 'student@example.com',
        ]);
        $studentRole = Role::create([
            'name' => 'student',
        ]);
        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $studentRole->id,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response
            ->assertForbidden()
            ->assertJsonPath('message', 'Students must use the student login endpoint.');
    }
}
