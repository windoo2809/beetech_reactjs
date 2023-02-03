<?php

use App\Dao\DaoConstants;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class CuUserDataTestTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cu_user')->truncate();
        DB::table('cu_customer_user')->truncate();
        $dataTest = [
            [
                'login_id' => 'testuser@test.tt',
                'customer_user_name' => 'Example',
                'role' => DaoConstants::CU_USER_ROLE_USER,
                'access_flg' => 0,
                'user_lock' => DaoConstants::CU_USER_UNLOCKED,
                'many_flag' => false,
            ],
            [
                'login_id' => 'firstlogin@test.tt',
                'customer_user_name' => 'First login',
                'role' => DaoConstants::CU_USER_ROLE_USER,
                'access_flg' => 0,
                'user_lock' => DaoConstants::CU_USER_UNLOCKED,
                'many_flag' => False,
            ],
            [
                'login_id' => 'superuser@test.tt',
                'customer_user_name' => 'Super User',
                'role' => DaoConstants::CU_USER_ROLE_SUPER_USER,
                'access_flg' => 0,
                'user_lock' => DaoConstants::CU_USER_UNLOCKED,
                'many_flag' => False,
            ],
            [
                'login_id' => 'userlock@test.tt',
                'customer_user_name' => 'Lock User',
                'role' => DaoConstants::CU_USER_ROLE_USER,
                'access_flg' => 0,
                'user_lock' => DaoConstants::CU_USER_LOCKED,
                'many_flag' => False,
            ],
            [
                'login_id' => 'defaultuser@test.tt',
                'customer_user_name' => 'Default User',
                'role' => DaoConstants::CU_USER_ROLE_USER,
                'access_flg' => 0,
                'user_lock' => DaoConstants::CU_USER_UNLOCKED,
                'many_flag' => False,
            ],
            [
                'login_id' => 'defaultapplication@test.tt',
                'customer_user_name' => 'Default Application',
                'role' => DaoConstants::CU_USER_ROLE_APPROVER,
                'access_flg' => 0,
                'user_lock' => DaoConstants::CU_USER_UNLOCKED,
                'many_flag' => False,
            ],
            [
                'login_id' => 'manybranch@test.tt',
                'customer_user_name' => 'Many Branch',
                'role' => DaoConstants::CU_USER_ROLE_USER,
                'access_flg' => 0,
                'user_lock' => DaoConstants::CU_USER_UNLOCKED,
                'many_flag' => True,
            ],
        ];

        for ($i = 0; $i < 10; $i ++) {
            $email = 'example.' . $i . '.email@test.tt';
            $role = [0,1,2,3,4,9];
            $user = [
                'login_id' => $email,
                'customer_user_name' => 'example' . $i,
                'role' => $role[rand(0, 5)],
                'access_flg' => 0,
                'user_lock' => rand(0,1),
                'many_flag' => false,
            ];
            array_push($dataTest, $user);
        };

        $limit = 1;
        $count = 0;
        $cus_id = 0;
        foreach ($dataTest as $key => $item)  {
            $count ++ ;
            if ($item['many_flag']) {
                $limit = 3;
            }
            DB::table('cu_user')->insert([
                'customer_id' => $count,
                'login_id' => $item['login_id'],
                'password' => Hash::make('example@Ex123'),
                'user_lock' => $item['user_lock'],
                'access_flg' => false,
                'role' => $item['role'],
                'customer_user_name' => $item['customer_user_name'],
                'customer_user_name_kana' => "カタカナ0" . $count,
                'customer_reminder_sms_flag' => false,
                'customer_user_tel' => "0123456789012",
                'change_login_id' => null,
                'create_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'create_user_id' => rand(0, 50),
                'create_system_type' => rand(1, 2),
                'update_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'update_user_id' => rand(0, 50),
                'update_system_type' => 2,
                'status' => 1,
            ]);
            for ( $i = 0; $i < $limit ; $i++ ) {
                DB::table('cu_customer_user')->insert([
                    'customer_id' => $count + $i,
                    'customer_branch_id' => $count + $i,
                    'core_id' => $count + $i,
                    'customer_user_name' => $item['customer_user_name'] . $i,
                    'customer_user_name_kana' => "カタカナ0" . $i,
                    'customer_user_division_name' => $item['customer_user_name'] .  "Division Name Test 0" . $i,
                    'customer_user_email' => $item['login_id'],
                    'customer_user_tel' => "0123456789012",
                    'customer_reminder_sms_flag' => True,
                    'create_date' => Carbon::now()->format('Y-m-d H:i:s'),
                    'create_user_id' => rand(0, $limit),
                    'create_system_type' => rand(1, 2),
                    'update_date' => Carbon::now()->format('Y-m-d H:i:s'),
                    'update_user_id' => 2,
                    'update_system_type' => rand(1, 2),
                    'status' => 1
                ]);
            }
        }
    }
}
