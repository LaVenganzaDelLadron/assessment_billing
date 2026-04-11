<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fee\FeesRequest;
use App\Models\Fees;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class FeesController extends Controller
{

    public function index()
    {
        //
        $data = Fees::query()->get();

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


    public function store(FeesRequest $request)
    {
        //
        $validate = $request->validated();
        $data = Fees::create($validate);

        return response()->json([
            'message' => 'stored successfully',
            'data' => $data,
        ], 201);
    }


    public function show(string $id)
    {
        //
        $data = Fees::query()->find($id);
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

    public function update(FeesRequest $request, string $id)
    {
        //
        $validate = $request->validated();
        $data = Fees::query()->find($id);

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
        $data = Fees::query()->find($id);

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
