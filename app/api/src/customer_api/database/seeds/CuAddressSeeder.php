<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CuAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create('ja_JP');
        DB::table('cu_address')->truncate();
        $limit = 20;
        for ( $i = 1; $i <= $limit; $i++ ) {
            DB::table('cu_address')->insert([
                'address_cd' => $i,
                'prefecture_cd' => str_pad(rand(1, 99), 2, 0, STR_PAD_LEFT),
                'city_cd' => rand(1, $limit),
                'town_cd' => 'town_' . rand(1, 20),
                'zip_cd' => $faker->postcode,
                'company_flg' => rand(0, 1),
                'delete_flg' => 0,
                'prefecture_name' => $faker->prefecture,
                'prefecture_kana' => 'プリーフェクトユア',
                'city_name' => $faker->city,
                'city_kana' => 'シティー',
                'town_name' => $faker->address,
                'town_kana' => 'アドレス',
                'town_info' => 'town_info_'. $i,
                'kyoto_street_name' => 'kyoto_street_name'. $i,
                'street_name' => 'street_name_'. $i,
                'street_kana' => 'street_kana_'. $i,
                'information' => 'information_'. $i,
                'company_name' => 'company_name_'. $i,
                'company_kana' => 'company_kana_'. $i,
                'company_address' => 'cp_address'. $i,
                'new_address_cd' => $i,
            ]);
        }
    }
}
