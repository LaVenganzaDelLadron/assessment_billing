<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = User::create($validated);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password',
            ], 401);
        }

        if ($this->userHasRole($user, 'student')) {
            return response()->json([
                'message' => 'Students must use the student login endpoint.',
            ], 403);
        }

        return $this->tokenResponse($user, 'Login successfully');
    }

    public function studentLogin(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->connectTimeout(5)
                ->timeout(10)
                ->retry([200, 400], throw: false)
                ->post(config('services.admission.login_url'), [
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                ]);
        } catch (ConnectionException $exception) {
            report($exception);

            return response()->json([
                'message' => 'Student login service is currently unavailable.',
            ], 502);
        }

        if (! $response->successful()) {
            $payload = $response->json();

            return response()->json([
                'message' => $this->admissionErrorMessage(is_array($payload) ? $payload : []),
            ], $response->status());
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            return response()->json([
                'message' => 'Student login service returned an invalid response.',
            ], 502);
        }

        $user = $this->syncAdmissionUser($payload, $validated['email']);

        if (! $this->admissionAccountIsStudent($payload)) {
            return response()->json([
                'message' => 'Only admitted students can access the billing system.',
            ], 403);
        }

        $this->ensureStudentRole($user);

        return $this->tokenResponse($user, 'Student login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    protected function tokenResponse(User $user, string $message): JsonResponse
    {
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => $message,
            'user' => $user,
            'token' => $token,
        ]);
    }

    protected function userHasRole(User $user, string $roleName): bool
    {
        return UserRole::query()
            ->join('role', 'role.id', '=', 'user_role.role_id')
            ->where('user_role.user_id', $user->id)
            ->where('role.name', $roleName)
            ->exists();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function admissionErrorMessage(array $payload): string
    {
        $message = $payload['message'] ?? null;

        if (is_string($message) && $message !== '') {
            return $message;
        }

        return 'Invalid student credentials.';
    }

    protected function syncAdmissionUser(array $payload, string $fallbackEmail): User
    {
        $admissionUser = $this->admissionUserPayload($payload);
        $applicant = $this->admissionApplicantPayload($payload);
        $email = $this->admissionEmail($applicant, $fallbackEmail);
        $name = $this->admissionName($admissionUser, $applicant, $email);

        return DB::transaction(function () use ($admissionUser, $applicant, $email, $name): User {
            $user = User::query()->firstOrNew([
                'email' => $email,
            ]);

            $user->name = $name;
            $user->email = $email;

            if (! $user->exists) {
                $user->password = Str::random(40);
            }

            $user->admission_user_id = isset($admissionUser['id']) ? (string) $admissionUser['id'] : null;
            $user->admission_role = isset($admissionUser['role']) && is_string($admissionUser['role']) ? $admissionUser['role'] : null;
            $user->admission_profile = [
                'user' => $admissionUser,
                'applicant' => $applicant,
            ];
            $user->save();

            return $user;
        });
    }

    protected function ensureStudentRole(User $user): void
    {
        $studentRole = Role::query()->firstOrCreate([
            'name' => 'student',
        ]);

        UserRole::query()->firstOrCreate([
            'user_id' => $user->id,
            'role_id' => $studentRole->id,
        ]);
    }

    protected function admissionAccountIsStudent(array $payload): bool
    {
        $role = data_get($payload, 'user.role');
        $studentProfile = data_get($payload, 'applicant.student');

        return $role === 'student' || is_array($studentProfile);
    }

    /**
     * @return array<string, mixed>
     */
    protected function admissionUserPayload(array $payload): array
    {
        $user = data_get($payload, 'user');

        return is_array($user) ? $user : [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function admissionApplicantPayload(array $payload): array
    {
        $applicant = data_get($payload, 'applicant');

        return is_array($applicant) ? $applicant : [];
    }

    /**
     * @param  array<string, mixed>  $applicant
     */
    protected function admissionEmail(array $applicant, string $fallbackEmail): string
    {
        $email = $applicant['email'] ?? null;

        return is_string($email) && $email !== '' ? $email : $fallbackEmail;
    }

    /**
     * @param  array<string, mixed>  $admissionUser
     * @param  array<string, mixed>  $applicant
     */
    protected function admissionName(array $admissionUser, array $applicant, string $fallbackName): string
    {
        $userName = $admissionUser['name'] ?? null;

        if (is_string($userName) && $userName !== '') {
            return $userName;
        }

        $name = trim(implode(' ', array_filter([
            $applicant['first_name'] ?? null,
            $applicant['middle_name'] ?? null,
            $applicant['last_name'] ?? null,
        ], fn (mixed $value): bool => is_string($value) && $value !== '')));

        return $name !== '' ? $name : $fallbackName;
    }
}
