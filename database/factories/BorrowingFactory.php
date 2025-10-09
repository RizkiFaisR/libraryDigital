<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Borrowing;
use App\Models\User;
use App\Models\Book;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Borrowing>
 */
class BorrowingFactory extends Factory
{
    public function definition(): array
    {
        $user = User::inRandomOrder()->first() ?? User::factory()->create();
        $book = Book::inRandomOrder()->first() ?? Book::factory()->create();

        return [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'borrowed_at' => now()->subDays($this->faker->numberBetween(1, 20)),
            'due_at' => now()->addDays($this->faker->numberBetween(1, 30)),
            'returned_at' => null,
            'status' => 'borrowed',
        ];
    }
}
