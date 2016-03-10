<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMonthlyReportsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monthly_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->integer('year')->unsigned();
            $table->integer('month')->unsigned();

            $table->integer('orders_count')->unsigned();
            $table->integer('approved_orders_count')->unsigned();
            $table->integer('new_users_count')->unsigned();
            $table->integer('total_users_count')->unsigned();
            $table->integer('total_revenue')->unsigned();
            $table->integer('bonus_payout_cash')->unsigned();
            $table->integer('bonus_payout_gold')->unsigned();
            $table->integer('bonus_payout_not_chosen')->unsigned();

            $table->unique(['year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('monthly_reports');
    }

}
