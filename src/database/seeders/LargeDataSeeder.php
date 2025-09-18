<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LargeDataSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        // create employers first (1000)
        $employers = [];
        for ($i = 0; $i < 1000; $i++) {
            $employers[] = [
                'name' => $faker->company,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('employers')->insert($employers);
        $employerIds = DB::table('employers')->pluck('id')->all();

        // bulk insert job_listings in chunks
        $batchSize = 1000;
        $total = 100000; // 100k
        for ($i = 0; $i < $total; $i += $batchSize) {
            $chunk = [];
            for ($j = 0; $j < $batchSize; $j++) {
                $chunk[] = [
                    'employer_id' => $employerIds[array_rand($employerIds)],
                    'title' => $faker->jobTitle,
                    'salary' => $faker->numberBetween(30000, 200000),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('job_listings')->insert($chunk);
            echo 'Inserted '.($i + $batchSize)."/{$total}\n";
        }
    }
}
