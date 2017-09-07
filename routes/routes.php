<?php

Route::group(['middleware' => ['auth']], function () {
    Route::group(['middleware' => ['role:manager']], function () {
        Route::group(['prefix' => 'report' , 'namespace' => '\Klsandbox\ReportRoute\Http\Controllers'], function () {
            Route::post('update-monthly-report/{month}/{year}', 'ReportController@postUpdateMonthlyReport');
            Route::get('monthly-report/{month}/{year}/{is_hq}/{organization_id}/{filter}', 'ReportController@getMonthlyReport');
            Route::get('monthly-report-list/{filter}', 'ReportController@getMonthlyReportList');
            Route::get('sales-report/{filter}', 'ReportController@salesReport');
        });
    });
});