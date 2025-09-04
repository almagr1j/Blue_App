<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'url'=>'http://picsum.photos/id/'.$this->faker->unique()->numberBetween(1,1000).'/1090/800',
            'imageable_id'=> $this->faker->randomDigitNotNull(),
            'imageable_type'=>$this->faker->randomElement(['App\Models\Article','App\Models\User']),
        
        ];
    }
}