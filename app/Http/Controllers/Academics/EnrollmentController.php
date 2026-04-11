<?php

namespace App\Http\Controllers\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Enrollment\EnrollmentsRequest;
use App\Models\Enrollments;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{

    public function index(): JsonResponse
    {
        //
        $data = Enrollments::query()->get();

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

    public function store(EnrollmentsRequest $request): JsonResponse
    {
        //
        $validate = $request->validated();
        $data = Enrollments::create($validate);

        return response()->json([
            'message' => 'stored successfully',
            'data' => $data,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        //
        $data = Enrollments::query()->find($id);
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

    public function update(EnrollmentsRequest $request, string $id): JsonResponse
    {
        //
        $validate = $request->validated();
        $data = Enrollments::query()->find($id);

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
        $data = Enrollments::query()->find($id);

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
