<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition()
    {
        return [
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
            'vendor'         => $this->faker->word(),
            'message'        => $this->faker->word(),
            'currency'       => $this->faker->currencyCode(),
            'amount'         => $this->faker->randomFloat(),
            'reference_no'   => $this->faker->word(),
            'approval_code'  => $this->faker->word(),
            'transaction_at' => Carbon::now(),

            'vendor_id' => $this->getVendor()->id,
        ];
    }

    public function getVendor()
    {
        if (Vendor::query()->count()) {
            return Vendor::query()->inRandomOrder()->first();
        }

        return Vendor::factory()->create();
    }
}
