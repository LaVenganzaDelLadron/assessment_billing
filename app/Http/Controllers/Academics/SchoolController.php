<?php

namespace App\Http\Controllers\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\School\SchoolRequest;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index(): JsonResponse
    {
        //
        $data = School::query()->get();

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

    public function store(SchoolRequest $request): JsonResponse
    {
        //
        $validate = $request->validated();
        $data = School::create($validate);

        return response()->json([
            'message' => 'stored successfully',
            'data' => $data,
        ], 201);

    }

    public function show(string $id): JsonResponse
    {
        $data = School::query()->find($id);

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

    public function update(SchoolRequest $request, string $id): JsonResponse
    {
        //
        $validate = $request->validated();
        $data = School::query()->find($id);

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
        //
        $data = School::query()->find($id);

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
