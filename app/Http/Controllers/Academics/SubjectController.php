<?php

namespace App\Http\Controllers\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subject\SubjectsRequest;
use App\Models\Subjects;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(): JsonResponse
    {
        //
        $data = Subjects::query()->get();

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

    public function store(SubjectsRequest $request): JsonResponse
    {
        //
        $validate = $request->validated();
        $data = Subjects::create($validate);

        return response()->json([
            'message' => 'stored successfully',
            'data' => $data,
        ], 201);

    }

    public function show(string $id): JsonResponse
    {
        //
        $data = Subjects::query()->find($id);
        if(! $data) {
            return response()->json([
                'message' => 'data not found',
            ], 404);
        }

        return response()->json([
            'message' => 'fetch successfully',
            'data' => $data,
        ], 200);
    }

    public function update(SubjectsRequest $request, string $id): JsonResponse
    {
        //
        $validate = $request->validated();
        $data = Subjects::query()->find($id);

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
        $data = Subjects::query()->find($id);

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
