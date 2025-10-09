<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use App\Models\Publisher;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    public function definition(): array
    {
        // ensure categories and publishers exist
        $category = Category::inRandomOrder()->first() ?? Category::factory()->create();
        $publisher = Publisher::inRandomOrder()->first() ?? Publisher::factory()->create();

        return [
            'title' => $this->faker->sentence(3),
            'isbn' => $this->faker->optional()->isbn13(),
            'author' => $this->faker->name(),
            'year' => $this->faker->year(),
            'category_id' => $category->id,
            'publisher_id' => $publisher->id,
            'copies' => $this->faker->numberBetween(0, 5),
            'description' => $this->faker->optional()->paragraph(),
        ];
    }
}
