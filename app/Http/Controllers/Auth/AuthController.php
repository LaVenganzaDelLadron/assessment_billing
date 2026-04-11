<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{

    public function register(RegisterRequest $request): JsonResponse
    {
        $validate = $request->validated();
        $user = User::create($validate);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }


    public function login(LoginRequest $request):JsonResponse
    {
        $validate = $request->validated();
        $user = User::where('email', $validate['email'])->first();


        if (! $user || ! Hash::check($validate['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successfully',
            'user' => $user,
            'token' => $token,
        ]);
    }


}
