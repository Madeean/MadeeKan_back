<?php

use App\Http\Controllers\API\AnakKontrakanController;
use App\Http\Controllers\API\authController;
use App\Http\Controllers\API\pembayaranController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [authController::class, 'register']);
Route::post('/login', [authController::class, 'login']);
Route::post('/logout', [authController::class, 'logout'])->middleware('auth:api');


Route::get('/get-belum-lunas', [pembayaranController::class, 'GetBelumLunas'])->middleware('auth:api');
Route::get('/get-lunas', [pembayaranController::class, 'GetLunas'])->middleware('auth:api');
Route::get('/get-pembayaran', [pembayaranController::class, 'GetLunasDanGaLunas'])->middleware('auth:api');
Route::post('/bayar', [pembayaranController::class, 'bayar'])->middleware('auth:api');
Route::get('/detail-transaksi',[pembayaranController::class, 'detailTransaksi'])->middleware('auth:api');
Route::post('/delete/pembayaran/{id}',[pembayaranController::class, 'deleteTransaksi'])->middleware('auth:api');



Route::post('/add-anak-kontrakan', [AnakKontrakanController::class, 'addAnakKontrakan'])->middleware('auth:api');
Route::get('/get-anak-kontrakan', [AnakKontrakanController::class, 'getAnakKontrakan'])->middleware('auth:api');
Route::get('/detail-anak-kontrakan/{id}', [AnakKontrakanController::class, 'detailAnakKontrakan'])->middleware('auth:api');
Route::post('/delete/anak-kontrakan/{id}',[AnakKontrakanController::class, 'deleteAnakKontrakan'])->middleware('auth:api');
Route::post('/update/anak-kontrakan/{id}',[AnakKontrakanController::class, 'editAnakKontrakan'])->middleware('auth:api');
Route::get('/belum-bayar-bulan-ini/{bulan}',[AnakKontrakanController::class, 'getBelumBayarBulanIni'])->middleware('auth:api');

