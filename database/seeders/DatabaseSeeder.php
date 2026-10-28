<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Creates one demo user (demo@example.com / password),
     * two projects, and a few tasks in each project so the
     * dashboard is not empty on the first login.
     * Safe to run on a fresh database only.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => 'password',
        ]);

        $website = Project::create([
            'user_id' => $user->id,
            'name' => 'Website Redesign',
        ]);

        $mobile = Project::create([
            'user_id' => $user->id,
            'name' => 'Mobile App',
        ]);

        $tasks = [
            [$website->id, 'Design the homepage', 1],
            [$website->id, 'Write the about page', 2],
            [$website->id, 'Fix the contact form', 3],
            [$mobile->id, 'Plan the login screen', 1],
            [$mobile->id, 'Test on Android', 2],
        ];

        foreach ($tasks as [$projectId, $name, $priority]) {
            Task::create([
                'user_id' => $user->id,
                'project_id' => $projectId,
                'name' => $name,
                'priority' => $priority,
            ]);
        }
    }
}
