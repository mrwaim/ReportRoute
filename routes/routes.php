<?php

Route::group(['middleware' => ['auth']], function () {
    Route::group(['middleware' => ['auth.admin']], function () {
        Route::group(['prefix' => 'report'], function () {
            Route::post('update-monthly-report/{month}/{year}', '\Klsandbox\ReportRoute\Http\Controllers\ReportController@postUpdateMonthlyReport');
            Route::get('monthly-report/{month}/{year}/{filter}', '\Klsandbox\ReportRoute\Http\Controllers\ReportController@getMonthlyReport');
            Route::get('monthly-report-list', '\Klsandbox\ReportRoute\Http\Controllers\ReportController@getMonthlyReportList');
        });
    });
});