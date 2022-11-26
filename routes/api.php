<?php

use App\Http\Controllers\API\AnakKontrakanController;
use App\Http\Controllers\API\authController;
use App\Http\Controllers\API\pembayaranController;
use App\Models\API\Pembayaran;
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

Route::get('/get-user-kontrakan',[authController::class,'getUserPengontrak'])->middleware('auth:api');
Route::get('/get-nama-kontrakan',[authController::class,'getNamaKontrakan']);
Route::get('/detail-pengontrak/{id}',[authController::class,'detailPengontrak'])->middleware('auth:api');
Route::post('/delete-pengontrak/{id}',[authController::class,'deletePengontrak'])->middleware('auth:api');
Route::post('/edit-profile',[authController::class,'editProfile'])->middleware('auth:api');

Route::post('/bayar', [pembayaranController::class, 'bayar'])->middleware('auth:api');
Route::get('/get-request-pembayaran', [pembayaranController::class, 'GetRequestPembayaranPengontrak'])->middleware('auth:api');
Route::get('/get-pembayaran-diterima-pengontrak',[pembayaranController::class,'GetPembayaranPengontrak'])->middleware('auth:api');
Route::get('/get-request-pemilik',[pembayaranController::class,'getRequestPembayaranPemilik'])->middleware('auth:api');
Route::get('/detail-transaksi/{id}',[pembayaranController::class, 'detailTransaksi'])->middleware('auth:api');
Route::post('/delete-pembayaran/{id}',[pembayaranController::class, 'deleteTransaksi'])->middleware('auth:api');
Route::get('/get-belum-bayar-bulanan/{bulan}',[pembayaranController::class,'getBelumBayarBulanan'])->middleware('auth:api');
Route::get('/get-pembayaran-diterima-pemilik',[pembayaranController::class,'getPembayaranDiterimaPemilik'])->middleware('auth:api');
Route::get('/get-pembayaran-belum-lunas-pemilik',[pembayaranController::class,'getPembayaranBelumLunasPemilik'])->middleware('auth:api');

Route::post('/terima-pembayaran/{id}',[pembayaranController::class,'AcceptPembayaran'])->middleware('auth:api');
Route::post('/tolak-pembayaran/{id}',[pembayaranController::class,'RejectPembayaran'])->middleware('auth:api');

Route::post('/edit-profile-pengontrak',[authController::class,'editProfilePengontrak'])->middleware('auth:api');

