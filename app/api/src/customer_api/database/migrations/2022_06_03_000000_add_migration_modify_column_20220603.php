<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddMigrationModifyColumn20220603 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared($this->getStatment());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    protected function getStatment()
    {
        return "
        -- テーブル定義： ULID情報 更新日：22/01/26
         CREATE TABLE cu_ulid
        (
        ulid CHAR(26)
        , pkid INT PRIMARY KEY  AUTO_INCREMENT
        , used BOOL DEFAULT FALSE
        );
        -- DDL 修正定義
        -- 最終更新日： 2022/05/30

        -- 2021/12/21
        -- 不具合 No.646対応
        -- AUTO INCRIMINATEの定義を削除

        alter table cu_customer_option modify column customer_id int ;

        -- 2021/12/23
        -- 不具合 No.650対応
        -- DEFAULT値の変更
        alter table cu_message modify column edit BOOL DEFAULT FALSE;

        -- 2022/01/03
        drop table cu_user_message_status;

         CREATE TABLE cu_user_message_status
        (
          user_id INT
        , message_id BIGINT
        , already_read BOOL DEFAULT FALSE
        , create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        , create_user_id INT
        , create_system_type SMALLINT
        , update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        , update_user_id INT
        , update_system_type SMALLINT
        , status BOOL DEFAULT TRUE
        );

        -- プライマリーキーの作成
        ALTER TABLE cu_user_message_status ADD PRIMARY KEY ( user_id, message_id );


        -- テーブル定義： 支店担当エリア情報  2022/01/26
        DROP TABLE cu_branch_area;

         CREATE TABLE cu_branch_area
        (
        prefecture VARCHAR(2) PRIMARY KEY
        , prefecture_name VARCHAR(4)
        , branch_id INT
        );

        -- テーブル定義：申請
        alter table cu_application modify column approval_date timestamp null default null;


        -- alter table cu_ulid modify column pkid  timestamp null default null;


        -- □□□□ 本番移行後のALTER TABLE文 □□□□

        -- テーブル定義： 支店担当エリア情報  2022/02/27
        -- 開発環境 2022/02/27実施済
        -- 本番環境 2022/02/27実施済
        alter table cu_branch_area add branch_manager_id INT;


        -- □□□□ 開発再開後 □□□□

        -- テーブル定義： お知らせ管理 2022/05/16
        -- 開発環境 2022/05/16 実施済
        -- 本番環境 2022/06/01 実施済
        ALTER TABLE cu_information ADD  not_login BOOL DEFAULT FALSE  AFTER data_type ;


        -- テーブル定義： 見積 2022/05/16
        -- 開発環境 2022/05/16 実施済
        -- 本番環境 2022/06/01 実施済
        ALTER TABLE cu_estimate ADD survey_parking_rent DECIMAL(12,0) AFTER survey_tax_in_flag;
        ALTER TABLE cu_estimate ADD survey_available_start_date DATETIME AFTER survey_total_amt;
        ALTER TABLE cu_estimate ADD survey_available_end_date DATETIME AFTER survey_available_start_date;
        ALTER TABLE cu_estimate ADD estimate_change_flag BOOL DEFAULT FALSE AFTER survey_available_end_date;
        ALTER TABLE cu_estimate ADD order_start_date DATETIME AFTER estimate_change_flag;
        ALTER TABLE cu_estimate ADD order_end_date DATETIME AFTER order_start_date;
        ALTER TABLE cu_estimate ADD order_amt DECIMAL(12,0) AFTER order_end_date;
        ALTER TABLE cu_estimate ADD order_memo TEXT AFTER order_amt;

        -- テーブル定義： 見積依頼 2022/05/16
        -- 開発環境 2022/05/16 実施済
        -- 本番環境 2022/06/01 実施済
        ALTER TABLE cu_request ADD operation_finished_flag DECIMAL(12,0) AFTER request_other_status;

        -- テーブル定義： 見積 2022/05/23
        -- 開発環境 2022/05/16 実施済
        -- 本番環境 2022/06/01 実施済
        ALTER TABLE cu_estimate ADD order_capacity_qty INT AFTER order_end_date;


        -- テーブル定義： 申請 2022/05/30
        -- 開発環境 2022/05/30 実施済
        -- 本番環境 2022/06/01 実施済
        ALTER TABLE cu_application ADD request_id INT NOT NULL AFTER estimate_id;
        ALTER TABLE cu_application MODIFY COLUMN estimate_id INT ;

        -- テーブル定義： 申請  ※アプリ改修後に実施
        -- 開発環境
        -- 本番環境
        ALTER TABLE cu_application DELETE COLUMN estimate_id  ;


        -- テーブル定義： 見積 2022/05/30
        -- 開発環境 2022/05/30 実施済
        -- 本番環境 2022/06/01 実施済
        ALTER TABLE  cu_estimate ADD  order_date DATETIME AFTER survey_available_end_date;
        ";
    }
}
