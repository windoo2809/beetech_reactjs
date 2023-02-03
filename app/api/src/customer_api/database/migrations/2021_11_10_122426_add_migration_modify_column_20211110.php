<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddMigrationModifyColumn20211110 extends Migration
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

    protected function getStatment() {
        return "
            -- DEFAULT VALUEを持つDATETIME型のTIMESTAMP型への一括変更
            -- 更新日： 2021/11/05 UM 山口

            -- 工事一覧
            ALTER TABLE cu_project modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_project modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- 見積依頼
            ALTER TABLE cu_request modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_request modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- 見積依頼対象駐車場
            ALTER TABLE cu_request_parking modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_request_parking modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- 見積
            ALTER TABLE cu_estimate modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_estimate modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- 申請
            ALTER TABLE cu_application modify column  application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_application modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_application modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- 契約
            ALTER TABLE cu_contract modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_contract modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- 請求
            ALTER TABLE cu_invoice modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_invoice modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- 駐車場情報
            ALTER TABLE cu_parking modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_parking modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- お知らせ
            ALTER TABLE cu_information modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_information modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- お知らせ対象
            ALTER TABLE cu_information_target modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_information_target modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- ユーザーお知らせ既読状況
            ALTER TABLE cu_user_information_status modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_user_information_status modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- メッセージ
            ALTER TABLE cu_message modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_message modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- メッセージ履歴
            ALTER TABLE cu_message_history modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

            -- ユーザーメッセージ既読状況
            ALTER TABLE cu_user_message_status modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_user_message_status modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- ファイル情報
            ALTER TABLE cu_file modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_file modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- 支店情報
            ALTER TABLE cu_branch modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_branch modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- 支店担当エリア情報

            -- 顧客会社情報
            ALTER TABLE cu_customer modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_customer modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- 顧客システム利用情報
            ALTER TABLE cu_customer_option modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_customer_option modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- 顧客支店情報
            ALTER TABLE cu_customer_branch modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_customer_branch modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- 顧客担当者情報
            ALTER TABLE cu_customer_user modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_customer_user modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- ユーザー情報
            ALTER TABLE cu_user modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_user modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- ユーザー顧客支店所属情報
            ALTER TABLE cu_user_branch modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_user_branch modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- 下請情報
            ALTER TABLE cu_subcontract modify column  create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
            ALTER TABLE cu_subcontract modify column  update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

            -- 住所マスタ
            -- 役割
            -- ワンタイムトークb
            -- カレンダー
            -- ULID情報
        ";
    }
}
