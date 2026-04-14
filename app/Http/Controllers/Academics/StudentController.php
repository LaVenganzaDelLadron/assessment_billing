<?php

namespace App\Http\Controllers\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Enrollment\StudentsRequest;
use App\Models\Programs;
use App\Models\Students;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index(): JsonResponse
    {
        // Auto Sync
        if (! Cache::has('students_synced_recently')) {
            $this->syncFromRegistrar();
        }

        $data = Students::query()->with('program')->get();

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
        $token = config('services.registrar.token');

        if (! $apiUrl) {
            return response()->json([
                'message' => 'Registrar API URL missing',
            ], 500);
        }

        try {
            $request = Http::acceptJson();

            if (is_string($token) && $token !== '') {
                $request = $request->withToken($token);
            }

            $response = $request->get(Str::finish($apiUrl, '/').'students');

            if (! $response->successful()) {
                return response()->json([
                    'message' => 'Failed to fetch students',
                ], 500);
            }

            $students = $response->json();

            if (! is_array($students)) {
                return response()->json([
                    'message' => 'Invalid API response',
                ], 500);
            }

            $count = 0;

            foreach ($students as $s) {
                $programId = $this->resolveProgramId([
                    'program_id' => data_get($s, 'program_id'),
                    'program_code' => data_get($s, 'program_code') ?? data_get($s, 'program.code'),
                ]);

                if (! $programId) {
                    continue;
                }

                $studentId = $this->resolveStudentId(data_get($s, 'id'));

                Students::updateOrCreate(
                    ['id' => $studentId],
                    [
                        'name' => $s['name'] ?? 'N/A',
                        'program_id' => $programId,
                    ]
                );
                $count++;
            }

            Cache::put('students_synced_recently', true, now()->addHours(1));
            Log::info('Students synced: '.$count);

            return response()->json([
                'message' => 'Students synced successfully',
                'total' => $count,
            ]);

        } catch (\Exception $e) {
            Log::error('Students Sync Failed: '.$e->getMessage());

            return response()->json([
                'message' => 'Sync failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StudentsRequest $request): JsonResponse
    {
        $validate = $this->resolvePayloadProgramId($request->validated(), true);

        $students = Students::create($validate);

        return response()->json($students->load('program'), 201);
    }

    public function show(string $id): JsonResponse
    {
        $data = Students::query()->with('program')->find($id);

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

    public function update(StudentsRequest $request, string $id): JsonResponse
    {
        $validate = $this->resolvePayloadProgramId($request->validated(), false);
        $data = Students::query()->find($id);

        if (! $data) {
            return response()->json([
                'message' => 'data not found',
            ], 404);
        }
        $data->update($validate);

        return response()->json([
            'message' => 'updated successfully',
            'data' => $data->fresh()->load('program'),
        ], 201);

    }

    public function destroy(string $id): JsonResponse
    {
        $data = Students::query()->find($id);

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

    /**
     * @param  array{name: string, program_id?: string, program_code?: string}  $payload
     * @return array{name?: string, program_id?: string}
     */
    private function resolvePayloadProgramId(array $payload, bool $programRequired): array
    {
        $containsProgramKey = array_key_exists('program_id', $payload) || array_key_exists('program_code', $payload);

        if (! $programRequired && ! $containsProgramKey) {
            return $payload;
        }

        $programId = $this->resolveProgramId($payload);

        if (! $programId) {
            abort(response()->json([
                'message' => 'Invalid program provided.',
            ], 422));
        }

        $payload['program_id'] = $programId;
        unset($payload['program_code']);

        return $payload;
    }

    /**
     * @param  array{program_id?: mixed, program_code?: mixed}  $payload
     */
    private function resolveProgramId(array $payload): ?string
    {
        if (isset($payload['program_id']) && is_string($payload['program_id']) && $payload['program_id'] !== '') {
            return Programs::query()->whereKey($payload['program_id'])->value('id');
        }

        if (isset($payload['program_code']) && is_string($payload['program_code']) && $payload['program_code'] !== '') {
            return Programs::query()->where('code', $payload['program_code'])->value('id');
        }

        return null;
    }

    private function resolveStudentId(mixed $externalId): string
    {
        if (is_string($externalId) && Str::startsWith($externalId, 'STU-')) {
            return $externalId;
        }

        if (is_int($externalId) || (is_string($externalId) && ctype_digit($externalId))) {
            return 'STU-'.str_pad((string) $externalId, 8, '0', STR_PAD_LEFT);
        }

        if (is_scalar($externalId) && (string) $externalId !== '') {
            return 'STU-'.Str::upper(substr(md5((string) $externalId), 0, 8));
        }

        return 'STU-'.Str::upper(Str::random(8));
    }
}
