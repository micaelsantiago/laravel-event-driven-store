<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'service' => 'Inventory Service',
        'version' => '1.0.0',
        'status' => 'running'
    ]);
});
