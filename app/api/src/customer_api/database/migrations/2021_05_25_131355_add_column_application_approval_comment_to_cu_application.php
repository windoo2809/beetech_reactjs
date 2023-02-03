<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnApplicationApprovalCommentToCuApplication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_application', function (Blueprint $table) {
            $table->text('application_comment')->after('application_status')->nullable();
            $table->text('approval_comment')->after('application_comment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cu_application', function (Blueprint $table) {
            $table->dropColumn(['application_comment', 'approval_comment']);
        });
    }
}
