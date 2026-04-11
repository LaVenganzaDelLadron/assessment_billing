<?php

namespace App\Http\Controllers\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Submission\SubmissionRequest;
use App\Models\Submissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{

    public function index()
    {
        //
        $data = Submissions::query()->get();

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


    public function store(SubmissionRequest $request)
    {
        //
        $validate = $request->validated();
        $data = Submissions::create($validate);

        return response()->json([
            'message' => 'stored successfully',
            'data' => $data,
        ], 201);
    }


    public function show(string $id)
    {
        //
        $data = Submissions::query()->find($id);
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


    public function update(SubmissionRequest $request, string $id)
    {
        //
        $validate = $request->validated();
        $data = Submissions::query()->find($id);

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


    public function destroy(string $id)
    {
        //
        $data = Submissions::query()->find($id);

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
