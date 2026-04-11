<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assessment>
 */
class AssessmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalUnits = fake()->randomFloat(2, 12, 30);
        $miscellaneousFee = fake()->randomFloat(2, 0, 2500);
        $discount = fake()->randomFloat(2, 0, 1000);
        $tuitionFee = round($totalUnits * (float) config('assessment.tuition_rate_per_unit', 150), 2);

        return [
            'student_id' => User::factory(),
            'total_units' => $totalUnits,
            'tuition_fee' => $tuitionFee,
            'miscellaneous_fee' => $miscellaneousFee,
            'discount' => $discount,
            'total_amount' => round($tuitionFee + $miscellaneousFee - $discount, 2),
        ];
    }
}
