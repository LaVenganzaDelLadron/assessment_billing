<?php

namespace App\Http\Controllers\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Program\CreateProgramRequest;
use App\Http\Requests\Program\ProgramsRequest;
use App\Models\Programs;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProgramsController extends Controller
{
    public function index(): JsonResponse
    {
        // AUTO SYNC
        if (! Cache::has('programs_synced_recently')) {
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

    public function syncFromRegistrar(): JsonResponse
    {
        $apiUrl = config('services.registrar.endpoint_url');

        if (! $apiUrl) {
            return response()->json([
                'message' => 'Registrar API URL missing',
            ], 500);
        }

        try {
            $response = Http::get(Str::finish($apiUrl, '/').'programs');

            if (! $response->successful()) {
                return response()->json([
                    'message' => 'Failed to fetch programs',
                ], 500);
            }

            $programs = $response->json();

            if (! is_array($programs)) {
                return response()->json([
                    'message' => 'Invalid API response',
                ], 500);
            }

            $count = 0;

            foreach ($programs as $p) {
                if (! isset($p['code'])) {
                    continue;
                }

                Programs::updateOrCreate(
                    ['code' => $p['code']], // prevent duplicates using unique code
                    [
                        'name' => $p['name'] ?? 'N/A',
                        'department' => $p['department'] ?? 'General',
                        'status' => $p['status'] ?? 'active',
                    ]
                );

                $count++;
            }

            Cache::put('programs_synced_recently', true, now()->addHours(1));
            Log::info('Programs synced: '.$count);

            return response()->json([
                'message' => 'Programs synced successfully',
                'total' => $count,
            ]);
        } catch (\Exception $e) {
            Log::error('Programs Sync Failed: '.$e->getMessage());

            return response()->json([
                'message' => 'Sync failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(CreateProgramRequest $request)
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
