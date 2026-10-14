<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WalletTransaction>
 */
class WalletTransactionFactory extends Factory
{
    protected $model = WalletTransaction::class;

    public function definition(): array
    {
        $amount = fake()->randomElement([50, 100, 200, 500, 1000]);

        return [
            'student_id'    => Student::factory(),
            'type'          => 'load',
            'amount'        => $amount,
            'balance_after' => $amount,
            'reference'     => 'FACT-' . strtoupper(fake()->bothify('??####')),
            'category'      => 'load',
            'description'   => 'Factory-generated',
            'recorded_by'   => 'Factory',
            'status'        => 'completed',
            'ip'            => null,
        ];
    }

    public function spend(): static
    {
        return $this->state(fn () => ['type' => 'spend', 'category' => 'canteen']);
    }

    public function topup(): static
    {
        return $this->state(fn () => [
            'type'          => 'topup',
            'category'      => 'mpesa_topup',
            'phone'         => '2547' . fake()->numerify('########'),
            'mpesa_receipt' => 'M' . strtoupper(fake()->bothify('#########')),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
        ]);
    }
}
