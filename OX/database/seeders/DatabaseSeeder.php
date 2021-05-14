<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 3; $i++) {
            User::factory()->create([
                'name' => 'user'.$i,
                'email' => 'user'.$i.'@amobajatek.hu',
            ]);
        }
    }
}
