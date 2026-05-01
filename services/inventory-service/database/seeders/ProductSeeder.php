<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Product::updateOrCreate(['id' => 101], ['name' => 'Smartphone', 'stock' => 10]);
        \App\Models\Product::updateOrCreate(['id' => 102], ['name' => 'Laptop', 'stock' => 5]);
    }
}
