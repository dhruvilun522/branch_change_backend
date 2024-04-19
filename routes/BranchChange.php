<?php
//by @bhijeet

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BranchChange;

Route::controller(BranchChange::class)->group(function () {
    Route::post('storepref', 'storepref');
    Route::post('check', 'check');
});