<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonthlyDailyBalanceReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monthly_daily_balance_reports', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('report_name');
            $blueprint->string('file_path');
            $blueprint->date('report_month');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('monthly_daily_balance_reports');
    }
}
