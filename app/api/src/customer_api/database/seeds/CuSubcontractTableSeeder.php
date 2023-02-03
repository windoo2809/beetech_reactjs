<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CuSubcontractTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cu_subcontract')->truncate();
        $limit = 15;
        $faker = Faker\Factory::create();

        for ( $i = 1; $i <= $limit; $i++ ) {
            DB::table('cu_subcontract')->insert([
                'customer_id' => 1,
                'subcontract_name' => 'Subcontract '.$i,
                'subcontract_kana' => 'カナ',
                'subcontract_branch_name' => 'Subcontract branch '.$i,
                'subcontract_branch_kana' => 'カナ',
                'subcontract_user_division_name' => 'Division name '.$i,
                'subcontract_user_name' => $faker->name,
                'subcontract_user_kana' => 'カナ',
                'subcontract_user_email' => $faker->companyEmail,
                'subcontract_user_tel' => $faker->phoneNumber,
                'subcontract_user_fax' => $faker->e164PhoneNumber,
                'subcontract_reminder_sms_flag' => true,
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
