<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Make sure categories are already seeded
        $categories = Category::all();

        if ($categories->count() == 0) {
            $this->command->info('No categories found, seeding categories first...');
            $this->call(CategorySeeder::class);
            $categories = Category::all();
        }

        $faker = Faker::create();

        $slNumber = 0;
        foreach ($categories as $category) {
            for ($i = 1; $i <= 5; $i++) {
                $slNumber = $slNumber + 1;
                Product::create([
                    'name' => $category->name . ' Product ' . $i,
                    'description' => 'Description for ' . $category->name . ' product ' . $i,
                    'price' => rand(100, 1000),
                    'stock' => rand(10, 100),
                    //'image' => 'https://via.placeholder.com/300x300.png?text=Product+' . $i,
                    //'image' => 'https://loremflickr.com/640/480/product?random=' . $faker->unique()->numberBetween(1, 1000),
                    //'image' => 'https://loremflickr.com/640/480/product?lock=' . $faker->unique()->numberBetween(1, 1000),

                    'image' => 'https://picsum.photos/id/' . $slNumber . '/200/300',
                    'category_id' => $category->id,
                ]);
            }
        }
    }
}
