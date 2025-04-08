<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin'),
            ]
        );
        $devTeam = Team::where('name', 'Development Team')->first();

        if ($devTeam) {
            $managerUser = User::firstOrCreate(
                ['email' => 'manager@example.com'],
                [
                    'name' => 'Dev Manager',
                    'password' => Hash::make('manager'),
                ]
            );

            $workerUser = User::firstOrCreate(
                ['email' => 'worker@example.com'],
                [
                    'name' => 'Worker',
                    'password' => Hash::make('worker'),
                ]
            );
            if (! $devTeam->members()->where('user_id', $managerUser->id)->exists()) {
                $devTeam->members()->attach($managerUser->id, ['role' => 'manager']);
                $this->command->info('Менеджера додано до Development Team.');
            } else {
                $this->command->info('Менеджер вже є в Development Team.');
            }

            if (! $devTeam->members()->where('user_id', $workerUser->id)->exists()) {
                $devTeam->members()->attach($workerUser->id, ['role' => 'worker']);
                $this->command->info('Працівника додано до Development Team.');
            } else {
                $this->command->info('Працівник вже є в Development Team.');
            }
        } else {
            $this->command->warn('Команду "Development Team" не знайдено, користувачів не додано.');
        }

        $this->command->info('Початкових користувачів та їх зв\'язки з командами створено.');
    }
}
