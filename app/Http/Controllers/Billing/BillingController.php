<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\BillingRequest;
use App\Models\Billing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillingController extends Controller
{

    public function index()
    {
        //
        $data = Billing::query()->get();

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


    public function store(BillingRequest $request)
    {
        //
        $validate = $request->validated();
        $data = Billing::create($validate);

        return response()->json([
            'message' => 'stored successfully',
            'data' => $data,
        ], 201);
    }


    public function show(string $id)
    {
        //
        $data = Billing::query()->find($id);
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


    public function update(BillingRequest $request, string $id)
    {
        //
        $validate = $request->validated();
        $data = Billing::query()->find($id);

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
        $data = Billing::query()->find($id);

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
