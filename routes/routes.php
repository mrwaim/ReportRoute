<?php

Route::group(['middleware' => ['auth']], function () {
    Route::group(['middleware' => ['auth.admin']], function () {

        Route::controllers([
            'report' => '\Klsandbox\ReportRoute\Http\Controllers\ReportController',
        ]);
    });
});
