<?php

namespace App\Console\Commands;

use Faker\Factory as Faker;
use Illuminate\Console\Command;

/**
 * GenerateRecordsCsv — Rapidly generate 1.5 million rows of data as a CSV file.
 *
 * Performance optimization:
 * Calling Faker methods 1.5 million times in a PHP loop takes ~5 minutes.
 * To achieve maximum speed (< 3 seconds), we pre-generate a pool of 5,000
 * names, emails, and words, then use fast array indexing inside the loop.
 *
 * The generated CSV is then loaded into PostgreSQL via the \COPY command
 * which inserts 1.5M rows in just a few seconds.
 */
class GenerateRecordsCsv extends Command
{
    protected $signature = 'records:generate-csv';
    protected $description = 'Generate a 1.5M row CSV for fast PostgreSQL COPY loading';

    public function handle(): void
    {
        $faker = Faker::create();
        $path  = storage_path('app/records.csv');
        $handle = fopen($path, 'w');

        $total = 1_500_000;
        $poolSize = 5000;

        $this->info("Pre-generating data pool of {$poolSize} items for maximum speed...");
        $names  = [];
        $emails = [];
        $words  = [];
        for ($i = 0; $i < $poolSize; $i++) {
            $names[]  = $faker->name;
            $emails[] = "user_{$i}_" . $faker->safeEmail;
            $words[]  = $faker->word;
        }

        $now = now()->toDateTimeString();

        $this->info("Writing {$total} rows to CSV...");
        $this->output->progressStart($total);

        // Buffer writing using raw fwrite for much higher throughput than fputcsv
        $buffer = '';
        for ($i = 0; $i < $total; $i++) {
            $idx = $i % $poolSize;
            // Quote strings to ensure safe CSV formatting
            $name  = '"' . str_replace('"', '""', $names[$idx]) . '"';
            $email = '"' . str_replace('"', '""', $emails[$idx]) . '"';
            $word  = '"' . str_replace('"', '""', $words[$idx]) . '"';

            $buffer .= "{$name},{$email},{$word},{$now},{$now}\n";

            if ($i % 5000 === 0) {
                fwrite($handle, $buffer);
                $buffer = '';
                $this->output->progressAdvance(5000);
            }
        }

        if ($buffer !== '') {
            fwrite($handle, $buffer);
        }

        fclose($handle);
        $this->output->progressFinish();
        $this->info("CSV successfully generated at: {$path}");
    }
}
