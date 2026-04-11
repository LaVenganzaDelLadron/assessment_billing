<?php

namespace Tests\Feature\Billing;

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AssessmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('assessment.tuition_rate_per_unit', 150);
    }

    public function test_authenticated_users_can_store_an_assessment_for_a_student(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $student = User::factory()->create();

        $response = $this->postJson("/api/assessment/{$student->id}", [
            'student_id' => $student->id,
            'total_units' => 18.5,
            'miscellaneous_fee' => 525.75,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.student_id', $student->id)
            ->assertJsonPath('data.tuition_fee', 2775)
            ->assertJsonPath('data.miscellaneous_fee', 525.75)
            ->assertJsonPath('data.discount', 0)
            ->assertJsonPath('data.total_amount', 3300.75);

        $assessment = Assessment::query()
            ->where('student_id', $student->id)
            ->first();

        $this->assertNotNull($assessment);
        $this->assertSame(2775.0, $assessment->tuition_fee);
        $this->assertSame(3300.75, $assessment->total_amount);
    }

    public function test_store_requires_a_matching_student_identifier(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $routeStudent = User::factory()->create();
        $payloadStudent = User::factory()->create();

        $response = $this->postJson("/api/assessment/{$routeStudent->id}", [
            'student_id' => $payloadStudent->id,
            'total_units' => 15,
            'miscellaneous_fee' => 300,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('errors.student_id.0', 'The student_id must match the selected student.');
    }

    public function test_authenticated_users_can_view_a_students_assessment(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $student = User::factory()->create();
        $assessment = Assessment::factory()->create([
            'student_id' => $student->id,
            'total_units' => 20,
            'tuition_fee' => 3000,
            'miscellaneous_fee' => 600,
            'discount' => 0,
            'total_amount' => 3600,
        ]);

        $response = $this->getJson("/api/assessment/{$student->id}");

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $assessment->id)
            ->assertJsonPath('data.student_id', $student->id);
    }

    public function test_authenticated_users_can_apply_a_scholarship_to_an_assessment(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $student = User::factory()->create();
        Assessment::factory()->create([
            'student_id' => $student->id,
            'total_units' => 20,
            'tuition_fee' => 3000,
            'miscellaneous_fee' => 600,
            'discount' => 0,
            'total_amount' => 3600,
        ]);

        $response = $this->postJson("/api/assessment/{$student->id}/apply-scholarship", [
            'discount' => 850.25,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.discount', 850.25)
            ->assertJsonPath('data.total_amount', 2749.75);

        $assessment = Assessment::query()
            ->where('student_id', $student->id)
            ->first();

        $this->assertNotNull($assessment);
        $this->assertSame(850.25, $assessment->discount);
        $this->assertSame(2749.75, $assessment->total_amount);
    }

    public function test_breakdown_returns_the_expected_assessment_fields(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $student = User::factory()->create();
        Assessment::factory()->create([
            'student_id' => $student->id,
            'total_units' => 21,
            'tuition_fee' => 3150,
            'miscellaneous_fee' => 450.5,
            'discount' => 200.25,
            'total_amount' => 3400.25,
        ]);

        $response = $this->getJson("/api/assessment/{$student->id}/breakdown");

        $response
            ->assertOk()
            ->assertExactJson([
                'tuition_fee' => 3150,
                'miscellaneous_fee' => 450.5,
                'discount' => 200.25,
                'total_amount' => 3400.25,
            ]);
    }
}
