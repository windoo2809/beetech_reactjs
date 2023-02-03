<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuBranchAreaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_branch_area', function (Blueprint $table) {
            $table->string('prefecture', 2)->comment('都道府県コード2桁');
            $table->string('prefecture_name', 4)->nullable()->comment('都道府県名');
            $table->integer('branch_id')->comment('ランドマークの支店ID');
            $table->primary('prefecture');
            $table->collation = 'utf8mb4_general_ci';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cu_branch_area');
    }
}
