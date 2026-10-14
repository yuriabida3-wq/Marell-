<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $amount = fake()->randomElement([500, 1000, 2000, 5000, 10000]);

        return [
            'student_id'       => Student::factory(),
            'amount'           => $amount,
            'initiated_amount' => $amount,
            'method'           => fake()->randomElement(['M-Pesa', 'Bank', 'Cash']),
            'transaction_code' => strtoupper(Str::random(10)),
            'status'           => 'completed',
            'term'             => 'Term 1',
            'year'             => (string) date('Y'),
            'notes'            => 'Factory-generated',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status'           => 'pending',
            'transaction_code' => null,
        ]);
    }

    public function mpesa(): static
    {
        return $this->state(fn () => [
            'method' => 'M-Pesa',
        ]);
    }
}
