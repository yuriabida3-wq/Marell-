<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 *
 * Note: User::students() joins on parent_phone, not user_id.
 * Tests that need parent-child links must pass
 * ->state(['parent_phone' => $phone]) to match the parent User row.
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $fee  = fake()->randomElement([25000, 35000, 45000, 55000]);
        $paid = fake()->randomElement([0, 5000, 15000, $fee]);

        return [
            'adm_no'       => fake()->unique()->numerify('ADM-######'),
            'name'         => fake()->name(),
            'class'        => (string) fake()->numberBetween(1, 9),
            'stream'       => fake()->randomElement(['A', 'B', 'C']),
            'parent_name'  => fake()->name(),
            'parent_phone' => '07' . fake()->numerify('########'),
            'parent_email' => fake()->safeEmail(),
            'total_fee'    => $fee,
            'paid_amount'  => $paid,
            'balance'      => max(0, $fee - $paid),
            'status'       => 'active',
            'wallet_balance'      => 0,
            'daily_limit'         => 0,
            'low_balance_alert'   => 200,
            'auto_reload_enabled' => false,
        ];
    }

    public function withWallet(float $balance = 500): static
    {
        return $this->state(fn () => ['wallet_balance' => $balance]);
    }

    public function blocked(array $items = [], array $categories = []): static
    {
        return $this->state(fn () => [
            'blocked_items'      => $items,
            'blocked_categories' => $categories,
        ]);
    }
}
