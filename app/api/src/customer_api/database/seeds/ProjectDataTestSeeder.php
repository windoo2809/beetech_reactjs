<?php

use Illuminate\Database\Seeder;
use App\Models\CuApplication;
use App\Models\CuContract;
use App\Models\CuEstimate;
use App\Models\CuProject;
use App\Models\CuRequest;
use App\Models\CuRequestParking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectDataTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                "user_id" => 1811,
                "customer_id" => 2,
                "customer_branch_id" => 67,
                "customer_user_id" => 1132,
                "approval_user_id" => 1812,
            ],
            [
                "user_id" => 1815,
                "customer_id" => 3,
                "customer_branch_id" => 74,
                "customer_user_id" => 1136,
                "approval_user_id" => 1816,
            ]
        ];
        
        $dataStruct = [
            [0,NULL,0,0,1],
            [0,NULL,3,1,1],
            [0,0,3,1,1],
            [0,1,3,1,1],
            [0,2,3,1,1],
            [0,3,3,1,1],
            [0,4,3,1,1],
            [0,NULL,3,2,1],
            [0,NULL,3,5,2],
            [0,NULL,3,5,3],
            [0,NULL,3,5,4],
            [0,NULL,3,5,4],
            [],
            [0,NULL,3,5,5],
            [0,NULL,7,7,5],
            [],
            [4,NULL,3,5,5],
            [4,NULL,7,8,5],
            [1,NULL,1,0,1],
            [2,NULL,3,1,1],
            [3,NULL,3,3,1],
            [5,NULL,3,5,5],
            [6,NULL,7,9,5],
        ];
        DB::beginTransaction();

        try {
            
            foreach ($users as $key => $user) {

                foreach ($dataStruct as $row => $struct) {
                    if (count($struct) > 0) {   
                        $projectData = [];       
                        // insert project;
                        $projectPrefix = str_pad($key + 1, 2, "0", STR_PAD_LEFT) . str_pad($row + 1, 2, "0", STR_PAD_LEFT);
                        $projectData['customer_id']             = $user['customer_id'];
                        $projectData['customer_branch_id']      = $user['customer_branch_id'];
                        $projectData['customer_user_id']        = $user['customer_user_id'];
                        $projectData['branch_id']               = 1;
                        $projectData['construction_number']     = "T00000". $projectPrefix;
                        $projectData['site_name']               = "Project TOP ". $projectPrefix;
                        $projectData['site_name_kana']          = "";
                        $projectData['zip_code']                = "150-0013";
                        $projectData['site_prefecture']         = "13";
                        $projectData['address_cd']              = "150001300";
                        $projectData['city_cd']                 = "13113";
                        $projectData['site_city']               = "渋谷区";
                        $projectData['site_address']            = "恵比寿";
                        $projectData['latitude']                = 35.6461371;
                        $projectData['longitude']               = 139.7153917;
                        $projectStartDate = $key == 0 ? Carbon::now()->subDays(rand(26, 30))->format('Y-m-d') : Carbon::now()->subDays(rand(5, 10))->format('Y-m-d');
                        $projectEndDate = Carbon::now()->addDays(rand(40, 60))->format('Y-m-d');
                        $projectData['project_start_date']      = $projectStartDate;
                        $projectData['project_finish_date']     = $projectEndDate;
                        $projectData['create_user_id']          = $user['user_id'];
                        $projectData['create_date'] = $key == 0 ? Carbon::now()->subDays(rand(28, 30))->format('Y-m-d') : Carbon::now()->subDays(rand(0, 5))->format('Y-m-d');
                        $projectData['create_system_type']      = 2;
                        $projectData['status']                  = 1;

                        $projectId = CuProject::insertGetId($projectData);
                        Log::debug('project store success:'. $projectId);

                        // insert request
                        $requestData = [];
                        
                        $requestData['project_id'] = $projectId;
                        $requestData['request_date'] = $key == 0 ? Carbon::now()->subDays(rand(26, 30))->format('Y-m-d') : Carbon::now()->subDays(rand(0, 5))->format('Y-m-d');
                        $requestData['estimate_deadline'] = $key == 0 ? Carbon::now()->subDays(rand(20, 25))->format('Y-m-d') : Carbon::now()->addDays(rand(5, 10))->format('Y-m-d');
                        $requestData['request_type'] = $struct[0];
                        $requestData['want_start_date'] = $key == 0 ? Carbon::now()->subDays(rand(15, 20)) : Carbon::now()->addDays(rand(10, 15));
                        $requestData['want_end_date'] = $projectEndDate;
                        $carQty = rand(1, 10);
                        $lTruckQty = rand(1, 10);
                        $truckQty = rand(1, 10);
                        $requestData['car_qty'] = $carQty;
                        $requestData['light_truck_qty'] = $lTruckQty;
                        $requestData['truck_qty'] = $truckQty;
                        $requestData['other_car_qty'] = 0;
                        $requestData['other_car_detail'] = "NO";
                        $requestData['want_guide_type'] = 1;
                        $requestData['cc_email'] = "test.mail". $projectPrefix ."@beetech.test";
                        $requestData['response_request_date'] = $key == 0 ? Carbon::now()->subDays(rand(15, 20))->format('Y-m-d') : Carbon::now()->addDays(rand(5,15))->format('Y-m-d');
                        $requestData['customer_other_request'] = "";
                        $requestData['request_other_qty'] = $carQty + $lTruckQty + $truckQty;
                        $requestData['request_status'] = $struct[2];
                        $requestData['create_date'] = $requestData['request_date'];
                        $requestData['create_system_type'] = 2;
                        $requestData['create_user_id'] = $user['user_id'];  
                        $requestData['status'] = 1;
                        
                        $requestId = CuRequest::insertGetId($requestData);

                        // insert parking request;

                        $requestParking = [];
                        $requestParking['request_id']           = $requestId;
                        $requestParking['parking_id']           = 75;
                        $requestParking['create_user_id']       = $user['user_id'];
                        $requestParking['create_system_type']   = 2;
                        $requestParking['status']               = 1;

                        CuRequestParking::insert($requestParking);

                        // insert estimate
                        $estimateParam = [];

                        $estimateParam['request_id'] = $requestId;
                        $estimateParam['project_id'] = $projectId;
                        $estimateParam['parking_id'] = 75;
                        $estimateParam['branch_id'] = 1;
                        $estimateParam['estimate_status'] = $struct[3];
                        $estimateParam['estimate_expire_date'] = $key == 0 ? Carbon::now()->subDays(rand(10, 12 ))->format('Y-m-d') : Carbon::now()->addDays(rand(15, 20))->format('Y-m-d');
                        $estimateParam['estimate_cancel_check_flag'] = 0;
                        $estimateParam['estimate_cancel_check_date'] = NULL;
                        $estimateParam['survey_parking_name'] = "surrvey parking " . $projectPrefix;
                        $estimateParam['survey_capacity_qty'] = rand(1,20);
                        $estimateParam['survey_site_distance_minute'] = rand(1,20);
                        $estimateParam['survey_site_distance_meter'] = rand(1,1000);
                        $estimateParam['survey_tax_in_flag'] = 0;
                        $estimateParam['survey_total_amt'] = 0;
                        $estimateParam['create_date'] =  $key == 0 ? Carbon::now()->subDays(rand(12, 15 ))->format('Y-m-d') : Carbon::now()->addDays(rand(12, 15))->format('Y-m-d');
                        $estimateParam['create_user_id']       = $user['user_id'];
                        $estimateParam['create_system_type']   = 2;
                        $estimateParam['status']               = 1;
                        
                        $estimateId = CuEstimate::insertGetId($estimateParam);

                        // insert contract
                        $contractParam = [];
                        $contractParam['project_id'] = $projectId;
                        $contractParam['estimate_id'] = $estimateId;
                        $contractParam['parking_id'] = 75;
                        $contractParam['branch_id'] = 1;
                        $contractParam['contract_status'] = $struct[4];
                        $contractParam['parking_name'] = "Parking name " . $projectPrefix;
                        $contractParam['parking_name_kana'] = "";
                        $contractParam['quote_capacity_qty'] = rand(1,20);
                        $contractParam['quote_subtotal_amt'] = rand(20, 100);
                        $contractParam['quote_tax_amt'] = rand(20, 100);
                        $contractParam['quote_total_amt'] = $contractParam['quote_subtotal_amt'] + $contractParam['quote_tax_amt'];
                        $contractParam['purchase_order_upload_date'] = $key == 0 ? Carbon::now()->subDays(rand(10, 12))->format('Y-m-d') : Carbon::now()->addDays(rand(10, 15))->format('Y-m-d');
                        $contractParam['purchase_order_register_type'] = rand(0, 1);
                        $contractParam['purchase_order_check_flag'] = 1;
                        $contractParam['purchase_order_check_date'] = $key == 0 ? Carbon::now()->subDays(rand(10, 12))->format('Y-m-d') : Carbon::now()->addDays(rand(12, 20))->format('Y-m-d');
                        $contractParam['order_schedule_date'] = $key == 0 ? Carbon::now()->subDays(rand(10, 12))->format('Y-m-d') : Carbon::now()->addDays(rand(12, 20))->format('Y-m-d');
                        $contractParam['order_process_date'] = $key == 0 ? Carbon::now()->subDays(rand(7, 12))->format('Y-m-d') : Carbon::now()->addDays(rand(12, 20))->format('Y-m-d');
                        $contractParam['quote_available_start_date'] = $key == 0 ? Carbon::now()->subDays(rand(5, 10))->format('Y-m-d') : Carbon::now()->addDays(rand(15, 40))->format('Y-m-d');
                        $contractParam['quote_available_end_date'] = $key == 0 ? Carbon::now()->subDays(rand(0, 5))->format('Y-m-d') : Carbon::now()->addDays(rand(35, 40))->format('Y-m-d');
                        $contractParam['extension_type'] = rand(0, 2);
                        $contractParam['create_date'] = $contractParam['purchase_order_upload_date'];
                        $contractParam['create_user_id']       = $user['user_id'];
                        $contractParam['create_system_type']   = 2;
                        $contractParam['status']               = 1;

                        CuContract::insert($contractParam);

                        // insert application;
                        if (isset($struct[1])) {
                            $applicationParam = [];
                            $applicationParam['estimate_id'] = $estimateId;
                            $applicationParam['application_user_id'] = $user['approval_user_id'];
                            $applicationParam['application_date'] =$key == 0 ? Carbon::now()->subDays(rand(7, 12))->format('Y-m-d') : Carbon::now()->addDays(rand(12, 15))->format('Y-m-d');
                            $applicationParam['approval_user_id'] = $user['approval_user_id'];
                            $applicationParam['approval_date'] = $key == 0 ? Carbon::now()->subDays(rand(7, 12))->format('Y-m-d') : Carbon::now()->addDays(rand(15, 20))->format('Y-m-d');
                            $applicationParam['application_status'] = $struct[1];
                            $applicationParam['application_comment'] = "application comment " . $projectPrefix;
                            $applicationParam['approval_comment'] = "approval comment " . $projectPrefix;
                            $applicationParam['create_date'] = $applicationParam['application_date'];
                            $applicationParam['create_user_id']       = $user['user_id'];
                            $applicationParam['create_system_type']   = 2;
                            $applicationParam['status']               = 1;

                            CuApplication::insert($applicationParam);
                        }
                    }
                }
            }
            DB::commit();
            Log::debug('project_data_seeder:success');
        }catch (\Exception $e) {
            DB::rollBack();
            Log::debug('project_data_seeder:error');
            Log::debug($e);
        }
    }
}
