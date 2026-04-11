<?php

namespace App\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RoleRequest;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        //query()->get() is getting all the data
        $data = Role::query()->get();

        if ($data->isEmpty()){
            return response()->json([
                'message' => 'data is empty'
            ], 404);
        }

        return response()->json([
            'message' => 'fetch successfully',
            'data' => $data,
        ], 200);
    }

    public function store(RoleRequest $request): JsonResponse
    {
        $validate = $request->validated();
        $data = Role::create($validate);

        return response()->json([
            'message' => 'stored successfully',
            'data' => $data,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        //query()->find() is getting all the data
        $data = Role::query()->find($id);

        if (! $data) {
            return response()->json([
                'message' => 'data not found',
            ], 404);
        }

        return response()->json([
            'message' => 'fetch successfully',
            'data' => $data,
        ], 200);
    }

    public function update(RoleRequest $request, string $id): JsonResponse
    {
        //query()->find() is getting all the data
        $validate = $request->validated();
        $data = Role::query()->find($id);

        if (! $data) {
            return response()->json([
                'message' => 'data not found',
            ], 404);
        }

        $data->update($validate);

        return response()->json([
            'message' => 'updated successfully',
            'data' => $data->fresh(),
        ], 201);
    }

    public function destroy(string $id): JsonResponse
    {
        //query()->get() is getting all the data
        $data = Role::query()->find($id);

        if (! $data) {
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
