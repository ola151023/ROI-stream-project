<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvestmentGroupingController;
Route::get('/', function () {
    return view('welcome');
});