<?php

use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServiceCategoryController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/users', function (Request $request) {
    return User::get();
})->middleware(['auth:api', 'role:admin']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/vendor/register', [AuthController::class, 'vendorRegister']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:api', 'role:admin'])->group(function() {
    Route::apiResource('service-categories', ServiceCategoryController::class);
    Route::apiResource('vendors', VendorController::class)->only(['index','show','destroy']);
    Route::patch('vendors/{id}/approve', [VendorController::class, 'approve']);
    Route::patch('vendors/{id}/reject', [VendorController::class, 'reject']);
});
