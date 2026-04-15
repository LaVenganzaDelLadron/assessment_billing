<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Role;
use App\Models\Students;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['password'] = '12345678';
        $user = User::create($validated);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (isset($validated['student_id'])) {
            return $this->loginStudent($validated['student_id'], $validated['password']);
        }

        if (! Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            return response()->json([
                'message' => 'Invalid email or password',
            ], 401);
        }

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        if ($user->roles()->doesntExist()) {
            return response()->json([
                'message' => 'User has no assigned role. Access denied.',
            ], 403);
        }

        if ($user->roles()->where('name', 'student')->exists()) {
            return response()->json([
                'message' => 'Students must login using student ID.',
            ], 403);
        }

        return $this->tokenResponse($user, 'Login successfully');
    }

    private function loginStudent(string $studentId, string $password): JsonResponse
    {
        if ($password !== '12345678') {
            return response()->json([
                'message' => 'Invalid student ID or password',
            ], 401);
        }

        $student = Students::query()->find($studentId);

        if (! $student) {
            return response()->json([
                'message' => 'Invalid student ID or password',
            ], 401);
        }

        $user = User::query()->find($student->id);

        if (! $user) {
            $user = new User;
            $user->id = $student->id;
        }

        $user->name = $student->name;
        $user->email = $this->studentEmail($student->id);
        $user->password = '12345678';
        $user->save();

        $studentRole = Role::query()->firstOrCreate([
            'name' => 'student',
        ]);
        UserRole::query()->firstOrCreate([
            'user_id' => $user->id,
            'role_id' => $studentRole->id,
        ]);

        return $this->tokenResponse($user, 'Login successfully');
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

        $role = $user->roles()->first()?->name ?? 'unassigned';

        return response()->json([
            'message' => $message,
            'user' => $user,
            'role' => $role,
            'token' => $token,
        ]);
    }

    private function studentEmail(string $studentId): string
    {
        return strtolower($studentId).'@student.local';
    }
}
