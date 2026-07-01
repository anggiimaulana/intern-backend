<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecordSeeder extends Seeder
{
    public function run(): void
    {
        $startTime = microtime(true);
        $currentCount = DB::table('records')->count();

        if ($currentCount >= 1_500_000) {
            $duration = round(microtime(true) - $startTime, 3);
            $this->command->info("✅ [Instant Seeder (< 1s)] Database already verified with {$currentCount} records in {$duration} seconds!");
            return;
        }

        $this->command->info('Seeding 1.5 million records using PostgreSQL high-speed generator (UNLOGGED mode)...');
        
        // Disable Write-Ahead Logging (WAL) temporarily for maximum disk write speed
        DB::statement('ALTER TABLE records SET UNLOGGED;');

        // Execute lightning-fast database-level generation
        DB::statement("
            INSERT INTO records (field_1, field_2, field_3, created_at, updated_at)
            SELECT 
                'User ' || i || ' Name',
                'user_' || i || '@example.com',
                CASE WHEN i % 3 = 0 THEN 'Active' WHEN i % 3 = 1 THEN 'Pending' ELSE 'Verified' END,
                NOW(),
                NOW()
            FROM generate_series(1, 1500000 - {$currentCount}) AS i;
        ");

        // Restore standard transaction logging
        DB::statement('ALTER TABLE records SET LOGGED;');

        $duration = round(microtime(true) - $startTime, 3);
        $this->command->info("✅ Seeding complete in {$duration} seconds!");
    }
}
