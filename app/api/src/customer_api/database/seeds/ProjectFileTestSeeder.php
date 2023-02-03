<?php

use Illuminate\Database\Seeder;
use App\Models\CuContract;
use App\Models\CuEstimate;
use App\Models\CuFile;
use App\Models\CuProject;
use App\Models\CuInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectFileTestSeeder extends Seeder
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
            // file_type, file_detail_type, file_path
            [3, 301,"633/2589/22/2021/08/26/FILE016.pdf"],
            [3, 303,"633/2589/22/2021/08/26/FILE017.pdf"],
            [4, 401,"633/2589/22/2021/08/26/FILE018.pdf"],
            [5, 501,"633/2589/22/2021/08/26/FILE019.pdf"],
            [6, 601, "633/2589/22/2021/08/26/FILE020.pdf"],
        ];

        $projectRange = [
            "1811" => [422, 442],
            "1815" => [443, 463],
        ];
        
        DB::beginTransaction();

        try {
            
            foreach ($users as $key => $user) {

                $range = $projectRange[$user['user_id']];

                for ($row = $range[0]; $row <= $range[1]; $row ++) {

                    $project = CuProject::where('project_id', $row)->first();
                    
                    $estimate = CuEstimate::where('project_id', $row)->where('status', 1)->first();

                    $contract = CuContract::where('project_id', $row)->where('estimate_id', $estimate->estimate_id)->where('status', 1)->first();

                    $invoiceData= [];
                    $invoiceData['customer_id']             = $user['customer_id'];
                    $invoiceData['customer_branch_id']      = $user['customer_branch_id'];
                    $invoiceData['customer_user_id']        = $user['customer_user_id'];
                    $invoiceData['project_id']              = $row;
                    $invoiceData['contract_id']             = $contract->contract_id;
                    $invoiceData['parking_id']              = $estimate->parking_id;
                    $invoiceData['invoice_amt']             = rand(100,10000);
                    $invoiceData['invoice_closing_date']    = Carbon::now()->addDays(rand(10, 50));
                    $invoiceData['payment_deadline']        = Carbon::now()->addDays(rand(50, 60));
                    $invoiceData['receivable_collect_total_amt']  = rand(100,10000);
                    $invoiceData['receivable_collect_finish_date']  = Carbon::now()->addDays(rand(10, 50));
                    $invoiceData['invoice_status']  = 4;
                    $invoiceData['payment_status']  = 1;
                    $invoiceData['invoice_status']  = 4;
                    $invoiceData['create_user_id']          = $user['user_id'];
                    $invoiceData['create_date'] = Carbon::now()->subDays(rand(0, 5))->format('Y-m-d');
                    $invoiceData['create_system_type']      = 2;
                    $invoiceData['status']                  = 1;
                    
                    $invoiceId = CuInvoice::insertGetId($invoiceData);

                    // insert survey_estimate_file 
                    $surveyEstFileData = [];
                    $surveyEstFileData['file_type'] = 3;
                    $surveyEstFileData['file_detail_type'] = 301;
                    $surveyEstFileData['customer_id'] = $user['customer_id'];
                    $surveyEstFileData['estimate_id'] = $estimate->estimate_id;
                    $surveyEstFileData['file_path'] = "633/2589/22/2021/08/26/FILE016.pdf";
                    $surveyEstFileData['file_name'] = "survey_estimate_file" . $project->construction_number . ".pdf";
                    $surveyEstFileData['create_user_id'] = $user['user_id'];
                    $surveyEstFileData['create_system_type'] = 2;

                    CuFile::insert($surveyEstFileData);
                   
                    // insert estimate_file 
                    $estimateFileData = [];
                    $estimateFileData['file_type'] = 3;
                    $estimateFileData['file_detail_type'] = 303;
                    $estimateFileData['customer_id'] = $user['customer_id'];
                    $estimateFileData['estimate_id'] = $estimate->estimate_id;
                    $estimateFileData['file_path'] = "633/2589/22/2021/08/26/FILE017.pdf";
                    $estimateFileData['file_name'] = "estimate_file". $project->construction_number . ".pdf";
                    $estimateFileData['create_user_id'] = $user['user_id'];
                    $estimateFileData['create_system_type'] = 2;
                    CuFile::insert($estimateFileData);

                    // insert order_file 
                    $orderFileData = [];
                    $orderFileData['file_type'] = 4;
                    $orderFileData['file_detail_type'] = 401;
                    $orderFileData['customer_id'] = $user['customer_id'];
                    $orderFileData['estimate_id'] = $estimate->estimate_id;
                    $orderFileData['file_path'] = "633/2589/22/2021/08/26/FILE018.pdf";
                    $orderFileData['file_name'] = "order_file". $project->construction_number . ".pdf";
                    $orderFileData['create_user_id'] = $user['user_id'];
                    $orderFileData['create_system_type'] = 2;
                    CuFile::insert($orderFileData);

                    // insert contract_file 
                    $contractFileData = [];
                    $contractFileData['file_type'] = 5;
                    $contractFileData['file_detail_type'] = 501;
                    $contractFileData['customer_id'] = $user['customer_id'];
                    $contractFileData['contract_id'] = $contract->contract_id;
                    $contractFileData['file_path'] = "633/2589/22/2021/08/26/FILE019.pdf";
                    $contractFileData['file_name'] = "contract_file". $project->construction_number . ".pdf";
                    $contractFileData['create_user_id'] = $user['user_id'];
                    $contractFileData['create_system_type'] = 2;
                    CuFile::insert($contractFileData);

                    // insert invoice_file 
                    $invoiceFileData = [];
                    $invoiceFileData['file_type'] = 6;
                    $invoiceFileData['file_detail_type'] = 601;
                    $invoiceFileData['customer_id'] = $user['customer_id'];
                    $invoiceFileData['invoice_id'] = $invoiceId;
                    $invoiceFileData['file_path'] = "633/2589/22/2021/08/26/FILE020.pdf";
                    $invoiceFileData['file_name'] = "invoice_file". $project->construction_number . ".pdf";
                    $invoiceFileData['create_user_id'] = $user['user_id'];
                    $invoiceFileData['create_system_type'] = 2 ;
                    CuFile::insert($invoiceFileData);
                }
            }
            DB::commit();
            Log::debug('project_file_seeder:success');
        }catch (\Exception $e) {
            DB::rollBack();
            Log::debug('project_file_seeder:error');
            Log::debug($e);
        }
    }
}
