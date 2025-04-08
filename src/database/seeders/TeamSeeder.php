<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        Team::firstOrCreate(
            ['name' => 'General'],
            [
                'description' => 'General tasks or time entries not assigned to a specific project team.',
            ]
        );
        Team::firstOrCreate(
            ['name' => 'Development Team'],
            [
                'description' => 'Handles software development projects.',
            ]
        );
        $this->command->info('Початкові команди успішно створено.');
    }
}
