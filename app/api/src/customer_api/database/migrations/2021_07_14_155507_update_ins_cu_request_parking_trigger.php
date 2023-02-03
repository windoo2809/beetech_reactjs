<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateInsCuRequestParkingTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared($this->updateInsCuRequestParking());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('
            DROP TRIGGER IF EXISTS ins_cu_request_parking;
        ');
    }

    private function updateInsCuRequestParking()
    {
        return "
        DROP TRIGGER IF EXISTS ins_cu_request_parking;
        
        CREATE TRIGGER ins_cu_request_parking  AFTER INSERT ON cu_request_parking FOR EACH ROW
        BEGIN
        
          DECLARE _parking_id, _request_id, _project_id, _supplier_id, _user_branch_id, _extend_estimate_id
                , _customer_id, _customer_branch_id, _customer_user_id INT;
          DECLARE _project_natural_id, _request_natural_id, _supplier_natural_id,  _user_branch_natural_id, _extend_estimate_natural_id
                , _customer_natural_id, _customer_branch_natural_id, _customer_user_natural_id VARCHAR(255);
        
          -- 駐車場情報の取得
          SELECT cp.core_id, p.supplier_id, p.supplier_natural_id
          INTO _parking_id, _supplier_id, _supplier_natural_id
          FROM cu_parking cp
            INNER JOIN parking p ON p.id = cp.core_id
          WHERE parking_id = new.parking_id;
          
          -- 工事情報の取得
          SELECT 
              r.project_id, 
              r.project_natural_id,
              r.id,
              r.request_natural_id, 
              r.customer_id,
              r.customer_natural_id,
              r.customer_branch_id,
              r.customer_branch_natural_id,
              r.customer_user_id,
              r.customer_user_natural_id,
              r.user_branch_id,
              r.user_branch_natural_id,
              cr.extend_estimate_id
          INTO 
            _project_id,
            _project_natural_id,
            _request_id,
            _request_natural_id,
            _customer_id,
            _customer_natural_id,
            _customer_branch_id,
            _customer_branch_natural_id,
            _customer_user_id,
            _customer_user_natural_id,
            _user_branch_id,
            _user_branch_natural_id,
            _extend_estimate_id
          FROM cu_request cr
            INNER JOIN request r ON r.id = cr.core_id
          WHERE cr.request_id = new.request_id;
          
          -- 延長元見積ID の取得
          IF _extend_estimate_id IS NOT NULL THEN
        
            SELECT e.estimate_natural_id
            INTO _extend_estimate_natural_id
            FROM cu_estimate ce
              INNER JOIN estimate ON e.core_id = ce.estimate_id
            WHERE estimate_id = _extend_estimate_id;
        
          END IF;
          
          -- 依頼駐車場管理の登録
          INSERT INTO request_parking( 
              create_timestamp
            , update_timestamp
            , ulid
            , project_id
            , project_natural_id
            , request_id
            , request_natural_id
            , parking_id
            , customer_id
            , customer_natural_id
            , customer_branch_id
            , customer_branch_natural_id
            , customer_user_id
            , customer_user_natural_id
            , supplier_id
            , supplier_natural_id
            , user_branch_id
            , user_branch_natural_id
            , request_parking_status
            , route_map_parking_flag
            , extend_estimate_natural_id
            , lastupdate_user_id
            , want_parking_flag
          ) 
          VALUES (
              CURRENT_TIMESTAMP
            , CURRENT_TIMESTAMP
            , getULID()
            , _project_id
            , _project_natural_id
            , _request_id
            , _request_natural_id
            , _parking_id
            , _customer_id
            , _customer_natural_id
            , _customer_branch_id
            , _customer_branch_natural_id
            , _customer_user_id
            , _customer_user_natural_id
            , _supplier_id
            , _supplier_natural_id
            , _user_branch_id
            , _user_branch_natural_id
            , 0
            , 1
            , _extend_estimate_natural_id
            , 1
            , 1
           );
        
        END;";
    }
}
