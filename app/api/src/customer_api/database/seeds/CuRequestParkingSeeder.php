<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CuRequestParkingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cu_request_parking')->truncate();

        $limit = 20;
        for ( $i = 1; $i <= $limit; $i++ ) {
            DB::table('cu_request_parking')->insert([
                'request_id' => $i,
                'parking_id' => rand($limit, $limit  * 2),
                'create_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'update_user_id' => rand(1, 6),
                'status' => true
            ]);
        }
    }
}
