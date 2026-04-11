<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\ApplyScholarshipRequest;
use App\Http\Requests\Billing\StoreAssessmentRequest;
use App\Models\Assessment;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class AssessmentController extends Controller
{
    public function __construct(
        protected Assessment $assessment,
        protected ConfigRepository $config,
    ) {
    }

    public function index(): JsonResponse
    {
        $data = $this->assessmentQuery()->get();

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

    public function store(StoreAssessmentRequest $request, string $studentId): JsonResponse
    {
        $validated = $request->validated();

        if ($validated['student_id'] !== $studentId) {
            return response()->json([
                'message' => 'validation failed',
                'errors' => [
                    'student_id' => ['The student_id must match the selected student.'],
                ],
            ], 422);
        }

        $existingAssessment = $this->findAssessmentByStudentId($studentId);
        $discount = (float) ($existingAssessment?->discount ?? 0);
        $tuitionFee = $this->calculateTuitionFee((float) $validated['total_units']);
        $miscellaneousFee = (float) ($validated['miscellaneous_fee'] ?? 0);

        $assessment = $existingAssessment ?? $this->assessment->newInstance();
        $assessment->fill([
            'student_id' => $studentId,
            'total_units' => (float) $validated['total_units'],
            'tuition_fee' => $tuitionFee,
            'miscellaneous_fee' => $miscellaneousFee,
            'discount' => $discount,
            'total_amount' => $this->calculateTotalAmount($tuitionFee, $miscellaneousFee, $discount),
        ]);
        $assessment->save();

        return response()->json([
            'message' => $existingAssessment ? 'updated successfully' : 'stored successfully',
            'data' => $assessment->fresh('student'),
        ], $existingAssessment ? 200 : 201);
    }

    public function show(string $studentId): JsonResponse
    {
        $data = $this->findAssessmentByStudentId($studentId);

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

    public function applyScholarship(ApplyScholarshipRequest $request, string $studentId): JsonResponse
    {
        $assessment = $this->findAssessmentByStudentId($studentId);

        if (! $assessment) {
            return response()->json([
                'message' => 'data not found',
            ], 404);
        }

        $discount = (float) $request->validated()['discount'];
        $assessment->update([
            'discount' => $discount,
            'total_amount' => $this->calculateTotalAmount(
                (float) $assessment->tuition_fee,
                (float) $assessment->miscellaneous_fee,
                $discount,
            ),
        ]);

        return response()->json([
            'message' => 'updated successfully',
            'data' => $assessment->fresh('student'),
        ], 200);
    }

    public function breakdown(string $studentId): JsonResponse
    {
        $assessment = $this->findAssessmentByStudentId($studentId);

        if (! $assessment) {
            return response()->json([
                'message' => 'data not found',
            ], 404);
        }

        return response()->json($this->buildBreakdown($assessment), 200);
    }

    protected function assessmentQuery(): Builder
    {
        return $this->assessment->newQuery()->with('student');
    }

    protected function findAssessmentByStudentId(string $studentId): ?Assessment
    {
        return $this->assessmentQuery()
            ->where('student_id', $studentId)
            ->first();
    }

    protected function calculateTuitionFee(float $totalUnits): float
    {
        $tuitionRatePerUnit = (float) $this->config->get('assessment.tuition_rate_per_unit', 150);

        return round($totalUnits * $tuitionRatePerUnit, 2);
    }

    protected function calculateTotalAmount(
        float $tuitionFee,
        float $miscellaneousFee,
        float $discount,
    ): float {
        return round($tuitionFee + $miscellaneousFee - $discount, 2);
    }

    /**
     * @return array<string, float>
     */
    protected function buildBreakdown(Assessment $assessment): array
    {
        return [
            'tuition_fee' => (float) $assessment->tuition_fee,
            'miscellaneous_fee' => (float) $assessment->miscellaneous_fee,
            'discount' => (float) $assessment->discount,
            'total_amount' => (float) $assessment->total_amount,
        ];
    }
}
