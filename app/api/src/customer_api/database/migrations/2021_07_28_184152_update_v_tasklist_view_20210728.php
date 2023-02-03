<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateVTasklistView20210728 extends Migration
{
     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement($this->createView());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement($this->dropView());
    }

    private function createView() 
    {
        return "
        CREATE OR REPLACE VIEW v_tasklist AS
        select 
          cust.customer_id,
          0 as customer_branch_id,
          0 as customer_user_id,
          0 as data_scope,
          ifnull(expire.expire_count,0) as expire_count, 
          ifnull(contract_waiting.contract_waiting_count, 0) as contract_waiting_count, 
          ifnull(renewal_waiting.renewal_waiting_count, 0 ) as renewal_waiting_count, 
          ifnull(renewal_this_month.renewal_this_month_count, 0 ) as renewal_this_month_count,
          ifnull(reminder.reminder_count, 0 ) as reminder_count,
          ifnull(application.application_count, 0 ) as application_count
        from cu_customer cust
         left join (
               select 
                     cp.customer_id,
                     count(ce.estimate_id) as expire_count
                from cu_estimate ce
                     inner join cu_project cp on cp.project_id = ce.project_id
               where ce.estimate_expire_date <= DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY)
                 and ce.estimate_status = 3
                 and ce.status
                 and cp.status
            group by cp.customer_id
         ) expire on expire.customer_id = cust.customer_id
         left join (
               select 
                      cp.customer_id,
                      count(cc.contract_id) as contract_waiting_count
                 from cu_contract cc
                      inner join cu_project cp on cp.project_id = cc.project_id
                where cc.contract_status = 3
                 and cc.status
                 and cp.status
              group by cp.customer_id
         ) contract_waiting on contract_waiting.customer_id = cust.customer_id
         left join (
               select 
                      cp.customer_id,
                      count(cc.contract_id) as renewal_waiting_count
                 from cu_contract cc
                      inner join cu_project cp on cp.project_id = cc.project_id
                where cc.quote_available_end_date BETWEEN DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY) and DATE_ADD(CURRENT_DATE, INTERVAL 60 DAY)
                  and cc.extension_type = 0
                 and cc.status
                 and cp.status
              group by cp.customer_id
        ) renewal_waiting on renewal_waiting.customer_id = cust.customer_id
         left join (
               select 
                      cp.customer_id,
                      count(cc.contract_id) as renewal_this_month_count
                 from cu_contract cc
                      inner join cu_project cp on cp.project_id = cc.project_id
                where cc.quote_available_end_date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY) 
                  and cc.extension_type = 0
                 and cc.status
                 and cp.status
              group by cp.customer_id
        ) renewal_this_month on renewal_this_month.customer_id = cust.customer_id
         left join (
               select 
                      cp.customer_id,
                      count(ci.invoice_id) as reminder_count
                 from cu_invoice ci
                      inner join cu_project cp on cp.project_id = ci.project_id
                where ci.payment_deadline > CURRENT_DATE
                  and ci.payment_status in ( 0, 1 )
                  and ci.reminder
                  and ci.status
                  and cp.status
              group by cp.customer_id
        ) reminder on reminder.customer_id = cust.customer_id
         left join (
               select 
                      cp.customer_id,
                      count(ca.application_id) as application_count
                 from cu_application ca
                      inner join cu_estimate ce on ce.estimate_id = ca.estimate_id 
                      inner join cu_project cp on cp.project_id = ce.project_id
                where ca.application_status = 1
                  and ca.status
                  and cp.status
              group by cp.customer_id
        ) application on application.customer_id = cust.customer_id
        where cust.status
        union
        select 
          cust.customer_id,
          cust.customer_branch_id,
          0 as customer_user_id,
          1 as count_type,
          ifnull(expire.expire_count,5) as expire_count, 
          ifnull(contract_waiting.contract_waiting_count, 5) as contract_waiting_count, 
          ifnull(renewal_waiting.renewal_waiting_count, 5 ) as renewal_waiting_count, 
          ifnull(renewal_this_month.renewal_this_month_count, 5 ) as renewal_this_month_count,
          ifnull(reminder.reminder_count, 5 ) as reminder_count,
          ifnull(application.application_count, 5 ) as application_count
        from cu_customer_branch cust
         left join (
               select 
                     cp.customer_id,
                     cp.customer_branch_id,
                     count(ce.estimate_id) as expire_count
                from cu_estimate ce
                     inner join cu_project cp on cp.project_id = ce.project_id
               where ce.estimate_expire_date <= DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY)
                 and ce.estimate_status = 3
                  and ce.status
                  and cp.status
            group by cp.customer_id,
                     cp.customer_branch_id
         ) expire on expire.customer_id = cust.customer_id and expire.customer_branch_id = cust.customer_branch_id
         left join (
               select 
                     cp.customer_id,
                     cp.customer_branch_id,
                      count(cc.contract_id) as contract_waiting_count
                 from cu_contract cc
                      inner join cu_project cp on cp.project_id = cc.project_id
                where cc.contract_status = 3
                  and cc.status
                  and cp.status
            group by cp.customer_id,
                     cp.customer_branch_id
         ) contract_waiting on contract_waiting.customer_id = cust.customer_id and contract_waiting.customer_branch_id = cust.customer_branch_id
         left join (
               select 
                     cp.customer_id,
                     cp.customer_branch_id,
                      count(cc.contract_id) as renewal_waiting_count
                 from cu_contract cc
                      inner join cu_project cp on cp.project_id = cc.project_id
                where cc.quote_available_end_date BETWEEN DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY) and DATE_ADD(CURRENT_DATE, INTERVAL 60 DAY)
                  and cc.extension_type = 0
                  and cc.status
                  and cp.status
            group by cp.customer_id,
                     cp.customer_branch_id
        ) renewal_waiting on renewal_waiting.customer_id = cust.customer_id and renewal_waiting.customer_branch_id = cust.customer_branch_id
         left join (
               select 
                     cp.customer_id,
                     cp.customer_branch_id,
                      count(cc.contract_id) as renewal_this_month_count
                 from cu_contract cc
                      inner join cu_project cp on cp.project_id = cc.project_id
                where cc.quote_available_end_date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY) 
                  and cc.extension_type = 0
                  and cc.status
                  and cp.status
            group by cp.customer_id,
                     cp.customer_branch_id
        ) renewal_this_month on renewal_this_month.customer_id = cust.customer_id and renewal_this_month.customer_branch_id = cust.customer_branch_id
         left join (
               select 
                     cp.customer_id,
                     cp.customer_branch_id,
                      count(ci.invoice_id) as reminder_count
                 from cu_invoice ci
                      inner join cu_project cp on cp.project_id = ci.project_id
                where ci.payment_deadline > CURRENT_DATE
                  and ci.payment_status in ( 0, 1 )
                  and ci.reminder
                  and ci.status
                  and cp.status
            group by cp.customer_id,
                     cp.customer_branch_id
        ) reminder on reminder.customer_id = cust.customer_id and reminder.customer_branch_id = cust.customer_branch_id
         left join (
               select 
                     cp.customer_id,
                     cp.customer_branch_id,
                      count(ca.application_id) as application_count
                 from cu_application ca
                      inner join cu_estimate ce on ce.estimate_id = ca.estimate_id 
                      inner join cu_project cp on cp.project_id = ce.project_id
                where ca.application_status = 1
                  and ca.status
                  and cp.status
            group by cp.customer_id,
                     cp.customer_branch_id
        ) application on application.customer_id = cust.customer_id and application.customer_branch_id = cust.customer_branch_id
        where cust.status
        union
        select 
          cust.customer_id,
          cust.customer_branch_id,
          cust.customer_user_id as customer_user_id,
          2 as count_type,
          ifnull(expire.expire_count,5) as expire_count, 
          ifnull(contract_waiting.contract_waiting_count, 5) as contract_waiting_count, 
          ifnull(renewal_waiting.renewal_waiting_count, 5 ) as renewal_waiting_count, 
          ifnull(renewal_this_month.renewal_this_month_count, 5 ) as renewal_this_month_count,
          ifnull(reminder.reminder_count, 5 ) as reminder_count,
          ifnull(application.application_count, 5 ) as application_count
        from cu_customer_user cust
         left join (
               select 
                     cp.customer_id,
                     cp.customer_branch_id,
                     cp.customer_user_id,
                     count(ce.estimate_id) as expire_count
                from cu_estimate ce
                     inner join cu_project cp on cp.project_id = ce.project_id
               where ce.estimate_expire_date <= DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY)
                 and ce.estimate_status = 3
                  and ce.status
                  and cp.status
            group by cp.customer_id,
                     cp.customer_branch_id,
                     cp.customer_user_id
         ) expire on expire.customer_id = cust.customer_id and expire.customer_branch_id = cust.customer_branch_id and expire.customer_user_id
         left join (
               select 
                     cp.customer_id,
                     cp.customer_branch_id,
                     cp.customer_user_id,
                      count(cc.contract_id) as contract_waiting_count
                 from cu_contract cc
                      inner join cu_project cp on cp.project_id = cc.project_id
                where cc.contract_status = 3
                  and cc.status
                  and cp.status
            group by cp.customer_id,
                     cp.customer_branch_id,
                     cp.customer_user_id
         ) contract_waiting on contract_waiting.customer_id = cust.customer_id and contract_waiting.customer_branch_id = cust.customer_branch_id and contract_waiting.customer_user_id
         left join (
               select 
                     cp.customer_id,
                     cp.customer_branch_id,
                     cp.customer_user_id,
                      count(cc.contract_id) as renewal_waiting_count
                 from cu_contract cc
                      inner join cu_project cp on cp.project_id = cc.project_id
                where cc.quote_available_end_date BETWEEN DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY) and DATE_ADD(CURRENT_DATE, INTERVAL 60 DAY)
                  and cc.extension_type = 0
                  and cc.status
                  and cp.status
            group by cp.customer_id,
                     cp.customer_branch_id,
                     cp.customer_user_id
        ) renewal_waiting on renewal_waiting.customer_id = cust.customer_id and renewal_waiting.customer_branch_id = cust.customer_branch_id and renewal_waiting.customer_user_id
         left join (
               select 
                     cp.customer_id,
                     cp.customer_branch_id,
                     cp.customer_user_id,
                      count(cc.contract_id) as renewal_this_month_count
                 from cu_contract cc
                      inner join cu_project cp on cp.project_id = cc.project_id
                where cc.quote_available_end_date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY) 
                  and cc.extension_type = 0
                  and cc.status
                  and cp.status
            group by cp.customer_id,
                     cp.customer_branch_id,
                     cp.customer_user_id
        ) renewal_this_month on renewal_this_month.customer_id = cust.customer_id and renewal_this_month.customer_branch_id = cust.customer_branch_id  and renewal_this_month.customer_user_id
         left join (
               select 
                     cp.customer_id,
                     cp.customer_branch_id,
                     cp.customer_user_id,
                      count(ci.invoice_id) as reminder_count
                 from cu_invoice ci
                      inner join cu_project cp on cp.project_id = ci.project_id
                where ci.payment_deadline > CURRENT_DATE
                  and ci.payment_status in ( 0, 1 )
                  and ci.reminder
                  and ci.status
                  and cp.status
            group by cp.customer_id,
                     cp.customer_branch_id,
                     cp.customer_user_id
        ) reminder on reminder.customer_id = cust.customer_id and reminder.customer_branch_id = cust.customer_branch_id and reminder.customer_user_id
         left join (
               select 
                     cp.customer_id,
                     cp.customer_branch_id,
                     cp.customer_user_id,
                      count(ca.application_id) as application_count
                 from cu_application ca
                      inner join cu_estimate ce on ce.estimate_id = ca.estimate_id 
                      inner join cu_project cp on cp.project_id = ce.project_id
                where ca.application_status = 1
                  and ca.status
                  and cp.status
            group by cp.customer_id,
                     cp.customer_branch_id,
                     cp.customer_user_id
        ) application on application.customer_id = cust.customer_id and application.customer_branch_id = cust.customer_branch_id and application.customer_user_id
        where cust.status
        ;
        ";
    }

    private function dropView(): string
    {
        return "DROP VIEW IF EXISTS 'v_project_list'";
    }
}
