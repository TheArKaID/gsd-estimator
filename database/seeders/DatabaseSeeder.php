<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->seedFromSqlFile(database_path('seeders/seeder.sql'));
    }

    /**
     * Seed database from an SQL file.
     *
     * @param string $file Path to the SQL file
     * @return void
     */
    protected function seedFromSqlFile(string $file): void
    {
        // Check if the Database already seeded
        if (DB::table('global_factors')->exists()) {
            $this->command->info('Database already seeded');
            return;
        }
        if (File::exists($file)) {
            $sql = File::get($file);
            DB::unprepared($sql);
            $this->command->info('SQL file imported successfully: ' . $file);
        } else {
            $this->command->error('SQL file not found: ' . $file);
        }
    }
}
