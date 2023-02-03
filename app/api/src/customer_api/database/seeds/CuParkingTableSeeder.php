<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CuParkingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create('ja_JP');
        $limit = 20;

        DB::table('cu_parking')->truncate();

        DB::table('cu_parking')->insert([
            'core_id' => "1",
            'parking_name' => 'Imperial Palace Main Gate Iron Bridge',
            'parking_name_kana' => '二重橋',
            'latitude' => '35.68043284454966',
            'longitude' => '139.75359899364472',
            'create_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'create_user_id' => 1,
            'create_system_type' => rand(1, 2),
            'update_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'update_user_id' => 1,
            'update_system_type' => rand(1, 2),
            'status' => 1
        ]);
        DB::table('cu_parking')->insert([
            'core_id' => "1",
            'parking_name' => 'Nishinomaru gate',
            'parking_name_kana' => '西の丸玄関門',
            'latitude' => '35.68089473018095',
            'longitude' => '139.75369555316894',
            'create_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'create_user_id' => 1,
            'create_system_type' => rand(1, 2),
            'update_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'update_user_id' => 1,
            'update_system_type' => rand(1, 2),
            'status' => 1
        ]);
        DB::table('cu_parking')->insert([
            'core_id' => "1",
            'parking_name' => 'Giang Hộ Thành - Phục Kiến Lỗ',
            'parking_name_kana' => '伏見櫓',
            'latitude' => '35.68070440001587',
            'longitude' => '139.7528445273405',
            'create_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'create_user_id' => 1,
            'create_system_type' => rand(1, 2),
            'update_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'update_user_id' => 1,
            'update_system_type' => rand(1, 2),
            'status' => 1
        ]);

        for ( $i = 4; $i <= $limit; $i++ ) {
            DB::table('cu_parking')->insert([
                'core_id' => rand(1, $limit),
                'parking_name' => $faker->address,
                'parking_name_kana' => 'パーキング',
                'latitude' => $faker->latitude,
                'longitude' => $faker->longitude,
                'create_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'create_user_id' => rand(1, 6),
                'create_system_type' => rand(1, 2),
                'update_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'update_user_id' => 1,
                'update_system_type' => rand(1, 2),
                'status' => 1
            ]);
        }
    }
}
