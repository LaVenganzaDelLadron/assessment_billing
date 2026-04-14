<?php

namespace App\Http\Controllers\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subject\SubjectsRequest;
use App\Models\Programs;
use App\Models\Subjects;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SubjectController extends Controller
{
    public function index(): JsonResponse
    {
        if (! Cache::has('subjects_synced_recently')) {
            $this->syncFromRegistrar();
        }

        $data = Subjects::query()->with('programs')->get();

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

            $response = $request->get(Str::finish($apiUrl, '/').'subjects');

            if (! $response->successful()) {
                return response()->json([
                    'message' => 'Failed to fetch subjects',
                ], 500);
            }

            $subjects = $response->json();

            if (! is_array($subjects)) {
                return response()->json([
                    'message' => 'Invalid API response',
                ], 500);
            }

            $count = 0;

            foreach ($subjects as $subjectPayload) {
                $subjectCode = data_get($subjectPayload, 'subject_code') ?? data_get($subjectPayload, 'code');
                $subjectName = data_get($subjectPayload, 'subject_name') ?? data_get($subjectPayload, 'name');

                if (! is_string($subjectCode) || $subjectCode === '' || ! is_string($subjectName) || $subjectName === '') {
                    continue;
                }

                $subjectId = $this->resolveSubjectId(data_get($subjectPayload, 'id'));

                $subject = Subjects::query()->updateOrCreate(
                    ['id' => $subjectId],
                    [
                        'subject_code' => $subjectCode,
                        'subject_name' => $subjectName,
                        'units' => (int) (data_get($subjectPayload, 'units') ?? 1),
                        'type' => data_get($subjectPayload, 'type'),
                        'status' => (string) (data_get($subjectPayload, 'status') ?? 'active'),
                    ]
                );

                $programsSync = $this->resolveProgramsSyncData(data_get($subjectPayload, 'programs', []));
                if ($programsSync !== []) {
                    $subject->programs()->syncWithoutDetaching($programsSync);
                }

                $count++;
            }

            Cache::put('subjects_synced_recently', true, now()->addHours(1));
            Log::info('Subjects synced: '.$count);

            return response()->json([
                'message' => 'Subjects synced successfully',
                'total' => $count,
            ]);
        } catch (\Exception $e) {
            Log::error('Subjects Sync Failed: '.$e->getMessage());

            return response()->json([
                'message' => 'Sync failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(SubjectsRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $programSync = $this->resolveSingleProgramSyncData($payload, true);

        $subject = Subjects::query()->create($this->extractSubjectAttributes($payload));

        if ($programSync !== null) {
            $subject->programs()->syncWithoutDetaching($programSync);
        }

        return response()->json([
            'message' => 'stored successfully',
            'data' => $subject->load('programs'),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $data = Subjects::query()->with('programs')->find($id);

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

    public function update(SubjectsRequest $request, string $id): JsonResponse
    {
        $payload = $request->validated();
        $data = Subjects::query()->find($id);

        if (! $data) {
            return response()->json([
                'message' => 'data not found',
            ], 404);
        }

        $data->update($this->extractSubjectAttributes($payload));

        $programSync = $this->resolveSingleProgramSyncData($payload, false);
        if ($programSync !== null) {
            $data->programs()->syncWithoutDetaching($programSync);
        }

        return response()->json([
            'message' => 'updated successfully',
            'data' => $data->fresh()->load('programs'),
        ], 201);
    }

    public function destroy(string $id): JsonResponse
    {
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

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function extractSubjectAttributes(array $payload): array
    {
        return collect($payload)->only([
            'subject_code',
            'subject_name',
            'units',
            'type',
            'status',
        ])->toArray();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, array<string, mixed>>|null
     */
    private function resolveSingleProgramSyncData(array $payload, bool $programRequired): ?array
    {
        $containsProgramKey = array_key_exists('program_id', $payload) || array_key_exists('program_code', $payload);

        if (! $programRequired && ! $containsProgramKey) {
            return null;
        }

        $programId = $this->resolveProgramId($payload);
        if (! $programId) {
            abort(response()->json([
                'message' => 'Invalid program provided.',
            ], 422));
        }

        return [
            $programId => $this->extractPivotValues($payload),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function resolveProgramsSyncData(mixed $programsPayload): array
    {
        if (! is_array($programsPayload)) {
            return [];
        }

        $syncData = [];

        foreach ($programsPayload as $programPayload) {
            if (! is_array($programPayload)) {
                continue;
            }

            $programId = $this->resolveProgramId([
                'program_id' => data_get($programPayload, 'id'),
                'program_code' => data_get($programPayload, 'code'),
            ]);

            if (! $programId) {
                continue;
            }

            $pivot = data_get($programPayload, 'pivot', []);
            $syncData[$programId] = $this->extractPivotValues(is_array($pivot) ? $pivot : []);
        }

        return $syncData;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function extractPivotValues(array $payload): array
    {
        return [
            'year_level' => isset($payload['year_level']) ? (int) $payload['year_level'] : null,
            'semester' => isset($payload['semester']) ? (string) $payload['semester'] : null,
            'school_year' => isset($payload['school_year']) ? (string) $payload['school_year'] : null,
            'status' => isset($payload['status']) ? (string) $payload['status'] : 'active',
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
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

    private function resolveSubjectId(mixed $externalId): string
    {
        if (is_string($externalId) && Str::startsWith($externalId, 'SUBJ-')) {
            return $externalId;
        }

        if (is_int($externalId) || (is_string($externalId) && ctype_digit($externalId))) {
            return 'SUBJ-'.str_pad((string) $externalId, 8, '0', STR_PAD_LEFT);
        }

        if (is_scalar($externalId) && (string) $externalId !== '') {
            return 'SUBJ-'.Str::upper(substr(md5((string) $externalId), 0, 8));
        }

        return 'SUBJ-'.Str::upper(Str::random(8));
    }
}
