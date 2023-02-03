<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CuProjectTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create('ja_JP');

        DB::table('cu_project')->truncate();

        $limit = 20;
        for ( $i = 1; $i <= $limit; $i++ ) {
            DB::table('cu_project')->insert([
                'customer_id' => rand(1,4),
                'core_id' => rand(1, $limit),
                'customer_branch_id' => rand(1,4),
                'customer_user_id' => rand(1,4),
                'branch_id' => rand(1,4),
                'construction_number' => $faker->randomNumber(6),
                'site_name' => $faker->company,
                'site_name_kana' => 'カンパニー',
                'zip_code' => substr_replace($faker->postcode, '-', 3, 0),
                'site_prefecture' =>  str_pad(rand(1, 99), 2, 0, STR_PAD_LEFT),
                'address_cd' => rand(1, $limit),
                'city_cd' => rand(1, $limit),
                'site_city' => $faker->city,
                'site_address' => $faker->address,
                'project_start_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'project_finish_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'create_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'create_user_id' => 1,
                'create_system_type' => rand(1, 2),
                'update_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'update_user_id' => 1,
                'update_system_type' => rand(1, 2),
                'status' => 1
            ]);
        }
    }
}
