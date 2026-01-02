<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PharmacyController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\CageController;
use App\Http\Controllers\Api\HealthRecordController;
use App\Http\Controllers\Api\TreatmentController;
use App\Http\Controllers\Api\BreedingController;
use App\Http\Controllers\Api\BirthController;
use App\Http\Controllers\Api\PregnantController;
use App\Http\Controllers\Api\InvestmentController;
use App\Http\Controllers\Api\InvestorController;
use App\Http\Controllers\Api\GoatsController;
use App\Http\Controllers\Api\TimbanganController;
use App\Http\Controllers\Api\TransaksiController;
use App\Http\Controllers\Api\PenangananController;


Route::prefix('v1')->group(function() {
    Route::prefix('auth')->group(function(){
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
        
        Route::middleware('auth:api')->group(function(){
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    Route::apiResource('mitra', InvestorController::class)
        ->middleware('auth:api','checkuser:admin');

    Route::get('/mitra/{kode_unik}/products', [InvestorController::class, 'products']);

    
    Route::middleware(['auth:api', 'checkuser:admin'])
    ->apiResource('obat', PharmacyController::class);
    Route::middleware(['auth:api', 'checkuser:admin'])
    ->apiResource('transaksi', TransaksiController::class);
    Route::middleware(['auth:api', 'checkuser:admin'])
    ->apiResource('supplier', SupplierController::class);
    Route::apiResource('kandang', CageController::class);
    Route::apiResource('kesehatan', HealthRecordController::class);
    Route::apiResource('pregnant', PregnantController::class);
    Route::apiResource('investment', InvestmentController::class);


    Route::middleware(['auth:api'])->group(function () {
        Route::get('/goats/total', [GoatsController::class, 'getTotalOfProduct']);
        Route::get('/goats', [GoatsController::class, 'index']);
        Route::get('/goats/{kode_product}', [GoatsController::class, 'show']);
    });

    Route::middleware(['auth:api', 'checkuser:admin'])->group(function () {
        Route::post('/goats', [GoatsController::class, 'store']);
        Route::put('/goats/{kode_product}', [GoatsController::class, 'update']);
        Route::delete('/goats/{kode_product}', [GoatsController::class, 'destroy']);
    });

    Route::middleware(['auth:api'])->group(function () {
        Route::get('/penanganan', [PenangananController::class, 'index']);
        Route::get('/penanganan/{kode_product}', [PenangananController::class, 'show']);
    });

    Route::middleware(['auth:api', 'checkuser:admin'])->group(function () {
        Route::post('/penanganan', [PenangananController::class, 'store']);
        Route::put('/penanganan/{kode_product}', [PenangananController::class, 'update']);
        Route::delete('/penanganan/{kode_product}', [PenangananController::class, 'destroy']);
    });

    Route::middleware(['auth:api'])->group(function () {
        Route::get('/breed', [BreedingController::class, 'index']);
        Route::get('/breed/{kode_breeding}', [BreedingController::class, 'show']);
    });

    Route::middleware(['auth:api', 'checkuser:admin'])->group(function () {
        Route::post('/breed', [BreedingController::class, 'store']);
        Route::put('/breed/{kode_breeding}', [BreedingController::class, 'update']);
        Route::delete('/breed/{kode_breeding}', [BreedingController::class, 'destroy']);
    });
    
    Route::middleware(['auth:api'])->group(function() {
        Route::get('/birth', [BirthController::class, 'index']);
        Route::get('/birth/{kode_kelahiran}', [BirthController::class, 'show']);
    });

    Route::middleware(['auth:api', 'checkuser:admin'])->group(function () {
        Route::post('/birth', [BirthController::class, 'store']);
        Route::put('/birth/{kode_kelahiran}', [BirthController::class, 'update']);
        Route::delete('/birth/{kode_kelahiran}', [BirthController::class, 'destroy']);
    });

    Route::middleware(['auth:api'])->group(function() {
        Route::get('/pregnant', [PregnantController::class, 'index']);
        Route::get('/pregnant/{kode_kehamilan}', [PregnantController::class, 'show']);
    });

    Route::middleware(['auth:api', 'checkuser:admin'])->group(function () {
        Route::post('/pregnant', [PregnantController::class, 'store']);
        Route::put('/pregnant/{kode_kehamilan}', [PregnantController::class, 'update']);
        Route::delete('/pregnant/{kode_kehamilan}', [PregnantController::class, 'destroy']);
    });

    // Route::get('/product/mothers', [GoatsController::class, 'getOnlyFatherId']);
    // Route::get('/product/fathers', [GoatsController::class, 'getOnlyMotherId']);
    Route::apiResource('timbangan', TimbanganController::class);
    Route::get('/invest/total', [InvestmentController::class, 'getTotalInvestment']);
    Route::get('/investor/total', [InvestorController::class, 'getTotalMitra']);
    Route::get('/investasi/total/{kode_unik}', [InvestmentController::class, 'getTotalInvestByUsers']);
});