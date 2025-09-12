<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            //Pemasukan
            ['name' => 'Gaji', 'type' => 'income', 'color' => '#10B981'],
            ['name' => 'PK', 'type' => 'income', 'color' => '#059669'],
            ['name' => 'Pemberian Ortu', 'type' => 'income', 'color' => '#047857'],
            ['name' => 'Lainnya', 'type' => 'income', 'color' => '#064E3B'],

            //Pengeluaran
            ['name' => 'Titip ke Mama', 'type' => 'expense', 'color' => '#EF4444'],
            ['name' => 'Ojol', 'type' => 'expense', 'color' => '#DC2626'],
            ['name' => 'Makan Minum', 'type' => 'expense', 'color' => '#B91C1C'],
            ['name' => 'Belanja', 'type' => 'expense', 'color' => '#B91C1C'],
            ['name' => 'Sewa/Kos', 'type' => 'expense', 'color' => '#7F1D1D'],
            ['name' => 'Paket Internet', 'type' => 'expense', 'color' => '#F59E0B'],
            ['name' => 'Lainnya', 'type' => 'expense', 'color' => '#6B7280'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
