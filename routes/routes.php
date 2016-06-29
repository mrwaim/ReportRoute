<?php

Route::group(['middleware' => ['auth']], function () {
    Route::group(['middleware' => ['role:manager']], function () {
        Route::group(['prefix' => 'report'], function () {
            Route::post('update-monthly-report/{month}/{year}', '\Klsandbox\ReportRoute\Http\Controllers\ReportController@postUpdateMonthlyReport');
            Route::get('monthly-report/{month}/{year}/{is_hq}/{organization_id}/{filter}', '\Klsandbox\ReportRoute\Http\Controllers\ReportController@getMonthlyReport');
            Route::get('monthly-report-list/{filter}', '\Klsandbox\ReportRoute\Http\Controllers\ReportController@getMonthlyReportList');
        });
    });
});