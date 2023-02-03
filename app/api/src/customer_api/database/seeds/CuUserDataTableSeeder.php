<?php

use App\Common\CodeDefinition;
use App\Dao\DaoConstants;
use App\Models\CuCustomer;
use App\Models\CuCustomerBranch;
use App\Models\CuCustomerOption;
use App\Models\CuCustomerUser;
use App\Models\CuUserBranch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class CuUserDataTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::table('cu_user')->truncate();
        // DB::table('cu_customer_user')->truncate();
        // DB::table('cu_customer_option')->truncate();
        // DB::table('cu_user_branch')->truncate();
        $userPrefix = [
            'qa', 'qa1', 'qa2', 'dev'
        ];
        
        $dataTest = [
            [
                'login_id' => 'super.user@test.tt',
                'customer_user_name' => 'supper user',
                'role' => CodeDefinition::ROLE_SUPER_USER,
                'access_flg' => 0,
                'user_lock' => DaoConstants::CU_USER_UNLOCKED,
                'many_flag' => false,
            ],
            [
                'login_id' => 'system.admin@test.tt',
                'customer_user_name' => 'system admin',
                'role' => CodeDefinition::ROLE_SYSTEM_ADMINISTRATOR,
                'access_flg' => 0,
                'user_lock' => DaoConstants::CU_USER_UNLOCKED,
                'many_flag' => false,
            ],
            [
                'login_id' => 'approver.user@test.tt',
                'customer_user_name' => 'approver user',
                'role' => CodeDefinition::ROLE_APPROVER,
                'access_flg' => 0,
                'user_lock' => DaoConstants::CU_USER_UNLOCKED,
                'many_flag' => false,
            ],
            [
                'login_id' => 'accountant.user@test.tt',
                'customer_user_name' => 'accountant user',
                'role' => CodeDefinition::ROLE_ACCOUNTANT,
                'access_flg' => 0,
                'user_lock' => DaoConstants::CU_USER_UNLOCKED,
                'many_flag' => false,
            ],
            [
                'login_id' => 'general.user@test.tt',
                'customer_user_name' => 'general user',
                'role' => CodeDefinition::ROLE_PERSON_IN_CHARGE,
                'access_flg' => 0,
                'user_lock' => DaoConstants::CU_USER_UNLOCKED,
                'many_flag' => false,
            ],
            [
                'login_id' => 'unauthorized.user@test.tt',
                'customer_user_name' => 'unauthorized user',
                'role' => CodeDefinition::ROLE_NO_PERMISSION,
                'access_flg' => 0,
                'user_lock' => DaoConstants::CU_USER_UNLOCKED,
                'many_flag' => false,
            ],
            [
                'login_id' => 'manybranch.user@test.tt',
                'customer_user_name' => 'manybranch user',
                'role' => rand(1,4),
                'access_flg' => 0,
                'user_lock' => DaoConstants::CU_USER_UNLOCKED,
                'many_flag' => true,
            ],
            [
                'login_id' => 'firstlogin.user@test.tt',
                'customer_user_name' => 'firstlogin user',
                'role' => rand(1,4),
                'access_flg' => 1,
                'user_lock' => DaoConstants::CU_USER_UNLOCKED,
                'many_flag' => false,
            ]
        ];

        $userScope = [0,1,2];

        $listCustomerId = CuCustomerBranch::where('status', 1)->select('customer_id', 'customer_branch_id')->groupBy('customer_id')->get()->pluck('customer_branch_id', 'customer_id');

        $count = 200;
        
        foreach ($userPrefix as $prefix) {
            foreach ($dataTest as $dataUser) {
                $loginId = $prefix . '.' .$dataUser['login_id'];
                $userName = $prefix . ' ' . $dataUser['customer_user_name'];
                $limit = 1;
                foreach ($userScope as $scope) {
                    $loginId = $prefix . '.scope' . $scope . '.' .$dataUser['login_id'];
                    $userName = $prefix . ' scope ' . $scope . ' ' . $dataUser['customer_user_name'];

                    $count ++;

                    // create user in cu_user
                    $userId = DB::table('cu_user')->insertGetId([
                        'customer_id' => $count,
                        'login_id' => $loginId,
                        'password' => Hash::make('example@Ex123'),
                        'user_lock' => $dataUser['user_lock'],
                        'access_flg' => $dataUser['access_flg'],
                        'role' => $dataUser['role'],
                        'customer_user_name' => $userName,
                        'customer_user_name_kana' => "カタカナ0" . $count,
                        'customer_reminder_sms_flag' => false,
                        'customer_user_tel' => "0123456789". rand(0,99),
                        'change_login_id' => null,
                        'create_date' => Carbon::now()->format('Y-m-d H:i:s'),
                        'create_user_id' => $count,
                        'create_system_type' => rand(1, 2),
                        'update_date' => Carbon::now()->format('Y-m-d H:i:s'),
                        'update_user_id' => $count,
                        'update_system_type' => 2,
                        'status' => 1,
                    ]);
                    
                    // create in cu_customer_option

                    CuCustomerOption::insert([
                        'core_id' => rand(1,9999),
                        'customer_id' => $count,
                        'start_date' => Carbon::now()->subDays(20)->format('Y-m-d 10:00:00'),
                        'end_date' => Carbon::now()->addDays(rand(20,40))->format('Y-m-d 10:00:00'),
                        'plan_type' => rand(0,1),
                        'approval' => 1,
                        'data_scope' => $scope,
                        'create_user_id' => rand(0, $limit),
                        'create_system_type' => rand(1, 2),
                        'update_user_id' => 2,
                        'update_system_type' => rand(1, 2),
                        'status' => 1
                    ]);

                    if ($dataUser['many_flag']) {
                        $limit = 2;
                    }

                    for ( $i = 1; $i <= $limit ; $i++ ) {
                        $customerUserId = CuCustomerUser::insertGetId([
                            'customer_id' => $count,
                            'customer_branch_id' => !empty($listCustomerId[$count]) ? $listCustomerId[$count] : $count,
                            'core_id' => rand(1,9999),
                            'customer_user_name' => $userName.$i,
                            'customer_user_name_kana' => "カタカナ0" . $count ."_" .$i,
                            'customer_user_division_name' => $userName .  "Division Name Test 0" . $i,
                            'customer_user_email' => $loginId,
                            'customer_user_tel' => "0123456789". rand(0,99),
                            'customer_reminder_sms_flag' => true,
                            'create_date' => Carbon::now()->format('Y-m-d H:i:s'),
                            'create_user_id' => rand(0, $limit),
                            'create_system_type' => rand(1, 2),
                            'update_date' => Carbon::now()->format('Y-m-d H:i:s'),
                            'update_user_id' => 2,
                            'update_system_type' => rand(1, 2),
                            'status' => 1
                        ]);

                        CuUserBranch::insert([
                            'user_id' => $userId,
                            'customer_id' => $count,
                            'customer_branch_id' => !empty($listCustomerId[$count]) ? $listCustomerId[$count] : $count,
                            'customer_user_id' => $customerUserId,
                            'belong' => 1,
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

    }
}
