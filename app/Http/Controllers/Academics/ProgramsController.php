<?php

namespace App\Http\Controllers\Academics;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Program\ProgramsRequest;
use App\Models\Programs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramsController extends Controller
{

    public function index(): JsonResponse
    {
        // AUTO SYNC
        if (!Cache::has('programs_synced_recently')) {
            $this->syncFromRegistrar();
        }

        $data = Programs::query()->get();

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

    public function syncFromRegistrar(): bool
    {
        $apiUrl = config('services.registrar.endpoint_url');

        if (!$apiUrl) {
            Log::error("Registrar API URL missing");
            return false;
        }

        try {
            $response = Http::get($apiUrl . 'programs');

            if ($response->successful()) {

                $programs = $response->json();

                foreach ($programs as $p) {

                    if (!isset($p['code'])) continue; // skip invalid

                    Programs::updateOrCreate(
                        ['code' => $p['code']], // UNIQUE KEY
                        [
                            'name' => $p['name'] ?? 'N/A',
                            'department' => $p['department'] ?? 'General',
                            'status' => $p['status'] ?? 'active',
                        ]
                    );
                }

                Cache::put('programs_synced_recently', true, now()->addHours(1));

                Log::info("Programs synced successfully");

                return true;
            }

        } catch (\Exception $e) {
            Log::error("Program Sync Failed: " . $e->getMessage());
        }

        return false;
    }



    public function store(ProgramsRequest $request)
    {
        $validated = $request->validated();

        $program = Programs::create($validated);

        return response()->json($program, 201);
    }


    public function show(string $id): JsonResponse
    {
        //
        $data = Programs::query()->find($id);

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


    public function update(ProgramsRequest $request, string $id): JsonResponse
    {
        //
        $validate = $request->validated();
        $data = Programs::query()->find($id);

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
        $data = Programs::query()->find($id);

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
