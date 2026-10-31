<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGracePeriodToSubscriptionTables extends Migration
{
    public function up()
    {
        Schema::table('company_packages', function (Blueprint $table) {
            $table->tinyInteger('grace_period')->default('0')->after('alert_before');
        });
        Schema::table('software_payments', function (Blueprint $table) {
            $table->integer('grace_period')->default('0')->after('alert');
        });
    }

    public function down()
    {
        Schema::table('company_packages', function (Blueprint $table) {
            $table->dropColumn('grace_period');
        });
        Schema::table('software_payments', function (Blueprint $table) {
            $table->dropColumn('grace_period');
        });
    }
}
