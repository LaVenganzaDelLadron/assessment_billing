<?php

namespace App\Http\Controllers\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Class\ClassesRequest;
use App\Models\Classes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassesController extends Controller
{

    public function index(): JsonResponse
    {
        //
        $data = Classes::query()->get();

        if ($data->isEmpty()){
            return response()->json([
                'message' => 'data is empty',
            ], 404);
        }

        return response()->json([
            'message' => 'fetch successfully',
            'data' => $data,
        ], 200);
    }


    public function store(ClassesRequest $request): JsonResponse
    {
        //
        $validate = $request->validated();
        $data = Classes::create($validate);

        return response()->json([
            'message' => 'stored successfully',
            'data' => $data,
        ], 201);
    }


    public function show(string $id): JsonResponse
    {
        //
        $data = Classes::query()->find($id);

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


    public function update(ClassesRequest $request, string $id): JsonResponse
    {
        //
        $validate = $request->validated();
        $data = Classes::query()->find($id);

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
        $data = Classes::query()->find($id);

        if (! $data) {
            return response()->json([
                'message' => 'data not found',
            ], 404);
        }

        $data->delete();

        return response()->json([
            'message' => 'deleted successfully',
        ], 200);
    }
}
