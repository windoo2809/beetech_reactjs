<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CuInformationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cu_information')->truncate();
        DB::table('cu_information_target')->truncate();
        DB::table('cu_user_information_status')->truncate();

        $limit = 20;
        for ( $i = 1; $i <= $limit; $i++ ) {
            DB::table('cu_information')->insert([
                'information_id' => $i,
                'subject' => "information subject ". $i,
                'body' => 'information body'. $i,
                'image_url' => 'https://cdn.pixabay.com/photo/2014/05/02/21/50/laptop-336378_960_720.jpg',
                'thumbnail_url' => 'https://cdn.pixabay.com/photo/2014/05/02/21/50/laptop-336378_960_720.jpg',
                'start_date' => Carbon::now()->subDays(7)->addDays(rand(0, 10))->format('Y-m-d 10:00:00'),
                'end_date' => Carbon::now()->subDays(2)->addDays(rand(0, 10))->format('Y-m-d 10:00:00'),
                'display_header' => rand(0, 1),
                'display_advertisement' => rand(0, 1),
                'create_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'create_user_id' => 1,
                'create_system_type' => rand(1, 2),
                'update_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'update_user_id' => 1,
                'update_system_type' => rand(1, 2),
                'status' => 1
            ]);

            DB::table('cu_information_target')->insert([
                'information_target_id' => $i,
                'information_id' => $i,
                'customer_id' => 1,
                'customer_branch_id' => 1,
                'customer_user_id' => 1,
                'create_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'create_user_id' => 1,
                'create_system_type' => rand(1, 2),
                'update_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'update_user_id' => 1,
                'update_system_type' => rand(1, 2),
                'status' => 1
            ]);

            if (rand(0,1)) {
                DB::table('cu_user_information_status')->insert([
                    'user_id' => 1,
                    'information_id' => $i,
                    'already_read' => 1,
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
}
