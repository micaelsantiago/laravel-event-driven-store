<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'service' => 'Payment Service',
        'version' => '1.0.0',
        'status' => 'running'
    ]);
});
