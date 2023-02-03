<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Carbon\Carbon;
use App\Services;
use App\Services\Interfaces;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * application services.
     *
     * @return void
     */

    protected $applicationServices = [
        Interfaces\CuSubContractServiceInterface::class => Services\CuSubContractService::class,
        Interfaces\AuthServiceInterface::class => Services\AuthService::class,
        Interfaces\CuCustomerServiceInterface::class => Services\CuCustomerService::class,
        Interfaces\CuCustomerBranchServiceInterface::class => Services\CuCustomerBranchService::class,
        Interfaces\CuMessageServiceInterface::class => Services\CuMessageService::class,
        Interfaces\CuProjectServiceInterface::class => Services\CuProjectService::class,
        Interfaces\CuPrefectureServiceInterface::class => Services\CuPrefectureService::class,
        Interfaces\CuParkingServiceInterface::class => Services\CuParkingService::class,
        Interfaces\CuFileServiceInterface::class => Services\CuFileService::class,
        Interfaces\CuInformationServiceInterface::class => Services\CuInformationService::class,
        Interfaces\CuEstimateServiceInterface::class => Services\CuEstimateService::class,
        Interfaces\CuRequestServiceInterface::class => Services\CuRequestService::class,
        Interfaces\CuContractServiceInterface::class => Services\CuContractService::class,
        Interfaces\CuApplicationServiceInterface::class => Services\CuApplicationService::class,
        Interfaces\CuInvoiceServiceInterface::class => Services\CuInvoiceService::class,
        Interfaces\CuAddressServiceInterface::class => Services\CuAddressService::class,
        Interfaces\CuRoleServiceInterface::class => Services\CuRoleService::class,
        Interfaces\CuCustomerOptionServiceInterface::class => Services\CuCustomerOptionService::class,
        Interfaces\CuUserServiceInterface::class => Services\CuUserService::class,
        Interfaces\GoogleStorageFileServiceInterface::class => Services\FileStorage\GoogleStorageFileService::class,
        Interfaces\VTaskListServiceInterface::class => Services\VTaskListService::class,
        Interfaces\CuRequestParkingServiceInterface::class => Services\CuRequestParkingService::class,
        Interfaces\MailServiceInterface::class => Services\MailService::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        foreach ($this->applicationServices as $interface => $service) {
            $this->app->bind($interface, $service);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('app.debug')) {
            DB::listen(function ($query) {
                Log::info("SQL [query] ". $query->sql ."  [bindings] ". implode(",",$query->bindings) ."  [time] ". $query->time ." milli seconds.");
            });
        }

        Queue::failing(function (JobFailed $event) {
            Log::error("Queue job [connection] ". $event->connectionName ."  [job] ". $event->job ."[exception] ". $event->exception);
        });

        //
        Blueprint::macro('commonFields', function () {
            // $now = Carbon::now();
            $this->dateTime('create_date')->default(DB::raw('CURRENT_TIMESTAMP'))->nullable();
            $this->integer('create_user_id')->nullable();
            $this->smallInteger('create_system_type')->nullable()->comment('システム区分 1: 基幹システム 2: 顧客向けシステム');
            $this->dateTime('update_date')->nullable()->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $this->integer('update_user_id')->nullable();;
            $this->smallInteger('update_system_type')->nullable()->comment('システム区分 1: 基幹システム 2: 顧客向けシステム');
            $this->boolean('status')->default(TRUE)->comment('TRUE: 有効 FALSE：無効')->nullable();
        });

        Validator::extendImplicit('required_parking_data', 'App\Rules\RequiredParkingIdList@validate');

        Validator::extendImplicit('custom_required_without', 'App\Rules\CustomRequiredWithout@validate');

        Validator::extendImplicit('unique_customer', 'App\Rules\UniqueCustomer@validate');

        Validator::extendImplicit('unique_update_customer', 'App\Rules\UniqueUpdateCustomer@validate');

        Validator::extendImplicit('required_array_value', 'App\Rules\RequiredArrayValue@validate');

        Validator::extendImplicit('numeric_item', 'App\Rules\NumericItemInArray@validate');

        Validator::extendImplicit('file_detail_type_valid', 'App\Rules\FileDetailTypeValid@validate');

        Validator::extendImplicit('not_true_with', 'App\Rules\NotTrueWith@validate');

        Validator::extendImplicit('custom_before_or_equal', 'App\Rules\CustomBeforeOrEqual@validate');

        Validator::extendImplicit('is_csv_file', 'App\Rules\IsCsvFile@validate');

        Validator::extendImplicit('regex_integer', 'App\Rules\RegexInteger@validate');

        Validator::extendImplicit('number_in_array', 'App\Rules\IsNumberInArray@validate');

        Validator::extendImplicit('is_valid_payment_status', 'App\Rules\IsValidPaymentStatus@validate');

        Validator::extendImplicit('custom_check_request_other_deadline', 'App\Rules\CustomCheckRequestOtherDeadline@validate');
        
        Validator::extendImplicit('is_encoding_shift_jis', 'App\Rules\IsEncodingShiftJIS@validate');

        Validator::extendImplicit('is_header', 'App\Rules\IsHeaderFile@validate');

        Validator::extendImplicit('max_item', 'App\Rules\MaxItem@validate');
        
        Validator::extendImplicit('is_list_file_path', 'App\Rules\IsListFilePath@validate');

        Validator::extendImplicit('required_without_all_field', 'App\Rules\RequiredWithoutAllField@validate');

        Validator::extendImplicit('is_valid_progress_status', 'App\Rules\IsValidProgressStatus@validate');

        Validator::extendImplicit('is_valid_estimate_status', 'App\Rules\IsValidEstimateStatus@validate');

        Validator::extendImplicit('is_valid_application_status', 'App\Rules\IsValidApplicationStatus@validate');
    }
}
