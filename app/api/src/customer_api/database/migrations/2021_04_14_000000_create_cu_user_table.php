<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_user', function (Blueprint $table) {
            $table->increments('user_id');
            $table->integer('customer_id');
            $table->string('login_id', 2048);
            $table->string('password', 255);
            $table->boolean('user_lock')->comment('TRUE: ロック, FALSE：なし')->default(FALSE);
            $table->boolean('access_flg')->comment('TRUE: ログイン済, FALSE：未ログイン')->nullable()->default(FALSE);
            $table->smallInteger('role')->comment('0: スーパーユーザー, 1: システム管理者, 2: 承認者, 3: 経理担当者, 4: 一般ユーザー, 9: 退職済／離任済')->default(4);
            $table->string('customer_user_name', 255)->nullable();
            $table->string('customer_user_name_kana', 255)->nullable();
            $table->boolean('customer_reminder_sms_flag')->nullable();
            $table->char('customer_user_tel', 13)->nullable();
            $table->commonFields();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cu_user');
    }
}
