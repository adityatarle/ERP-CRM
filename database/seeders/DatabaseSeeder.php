<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliveryNote;
use App\Models\Product;
use App\Models\Customer;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test data for delivery notes
        $this->call([
            DeliveryNoteTestSeeder::class,
        ]);
    }
}
