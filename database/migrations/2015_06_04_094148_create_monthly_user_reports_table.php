<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMonthlyUserReportsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monthly_user_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->integer('monthly_report_id')->unsigned();
            $table->foreign('monthly_report_id')->references('id')->on('monthly_reports');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');

            $table->integer('orders_count')->unsigned();
            $table->integer('introductions_count')->unsigned();
            $table->integer('total_stockist_count')->unsigned();

            $table->integer('bonus_payout_cash')->unsigned();
            $table->integer('bonus_payout_gold')->unsigned();
            $table->integer('bonus_payout_not_chosen')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('monthly_user_reports');
    }

}
