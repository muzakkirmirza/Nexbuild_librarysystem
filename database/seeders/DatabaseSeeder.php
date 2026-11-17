<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'nexbuild@gmail.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'student@gmail.com'],
            [
                'name' => 'Student User',
                'password' => Hash::make('password123'),
                'role' => 'student',
            ]
        );

        $cat1 = Category::firstOrCreate(['name' => 'Laptops']);
        $cat2 = Category::firstOrCreate(['name' => 'Hardware Kits']);

        InventoryItem::firstOrCreate(
            ['name' => 'Dell Latitude Laptop'],
            [
                'category_id' => $cat1->id,
                'condition' => 'Good',
                'is_available' => true,
            ]
        );

        InventoryItem::firstOrCreate(
            ['name' => 'Arduino Starter Kit'],
            [
                'category_id' => $cat2->id,
                'condition' => 'Good',
                'is_available' => true,
            ]
        );
    }
}
