<?php

namespace App\Http\Controllers\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Year\YearRequest;
use App\Models\Year;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class YearController extends Controller
{

    public function index()
    {
        //
        $data = Year::query()->get();

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


    public function store(YearRequest $request)
    {
        //
        $validate = $request->validated();
        $data = Year::create($validate);

        return response()->json([
            'message' => 'stored successfully',
            'data' => $data,
        ], 201);
    }


    public function show(string $id)
    {
        //
        $data = Year::query()->find($id);

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


    public function update(YearRequest $request, string $id)
    {
        //
        $validate = $request->validated();
        $data = Year::query()->find($id);

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
        $data = Year::query()->find($id);

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
