<?php

namespace App\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\UserRoleRequest;
use App\Models\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function index(): JsonResponse
    {
        $data = UserRole::query()->get();

        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'data is empty',
            ], 404);
        }

        return response()->json([
            'message' => 'fetch successfully',
            'data' => $data,
        ], 200);
    }

    public function store(UserRoleRequest $request): JsonResponse
    {
        $validate = $request->validated();
        $data = UserRole::create($validate);

        return response()->json([
            'message' => 'stored successfully',
            'data' => $data,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $data = UserRole::query()->find($id);

        if (!$data) {
            return response()->json([
                'message' => 'data not found',
            ], 404);
        }

        return response()->json([
            'message' => 'fetch successfully',
            'data' => $data,
        ], 200);
    }

    public function update(UserRoleRequest $request, string $id): JsonResponse
    {
        $validate = $request->validated();
        $data = UserRole::query()->find($id);

        if (!$data) {
            return response()->json([
                'message' => 'data not found',
            ], 404);
        }

        $data->update($validate);

        return response()->json([
            'message' => 'updated successfully',
            'data' => $data->fresh(),
        ], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $data = UserRole::query()->find($id);

        if (!$data) {
            return response()->json([
                'message' => 'data not found',
            ], 404);
        }

        $data->delete();

        return response()->json([
            'message' => 'delete successfully',
        ], 200);
    }
}
