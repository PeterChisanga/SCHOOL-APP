<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\School;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\School>
 */
class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' School',
            'address' => $this->faker->streetAddress(),
            'email' => $this->faker->unique()->safeEmail(),
            'motto' => $this->faker->catchPhrase(),
            'phone' => $this->faker->numerify('07########'),
            // create an owner first and set owner_id on the school insert so NOT NULL constraint is satisfied
            'owner_id' => User::factory(),
        ];
    }
}
