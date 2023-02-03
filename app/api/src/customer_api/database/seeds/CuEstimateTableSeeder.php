<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CuEstimateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create('ja_JP');

        DB::table('cu_estimate')->truncate();
        $limit = 40;
        for ( $i = 1; $i <= $limit; $i++ ) {
            DB::table('cu_estimate')->insert([
                'core_id' => $faker->randomNumber(2),
                'request_id' => $i,
                'project_id' => $i > 20 ? $i -20 : $i,
                'parking_id' => $i,
                'branch_id' => $i,
                'estimate_status' => rand(1,9),
                'survey_parking_name' => "bai do xe",
                'survey_capacity_qty' => $faker->randomNumber(2),
                'create_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'create_user_id' => rand(1, 6),
                'create_system_type' => rand(1, 2),
                'update_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'update_user_id' => rand(1, 6),
                'update_system_type' => rand(1, 2),
                'status' => 1
            ]);
        }
    }
}
