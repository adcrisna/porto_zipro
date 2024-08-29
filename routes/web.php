<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\SpectrumTestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return "its working";
});

Route::get('/test', [TestController::class, 'index']);
Route::get('/testtttt', [TestController::class, 'testttt']);
Route::get('/test1', [TestController::class, 'test1']);
Route::get('/regexComa', [TestController::class, 'regexComa']);
Route::get('/testDate', [TestController::class, 'testDate']);
Route::get('/tesRegexNIK', [TestController::class, 'tesRegexNIK']);
Route::get('/tesOrderJob', [TestController::class, 'tesOrderJob']);

Route::get('/revokeUsers', [TestController::class, 'revokeUsers']);
Route::get('/orderJob', [TestController::class, 'orderJob']);
Route::get('/testGetCart', [TestController::class, 'testGetCart']);
Route::get('/testRollbackOrderRef', [TestController::class, 'testRollbackOrderRef']);
Route::get('/updateInquiry', [TestController::class, 'updateInquiry']);
Route::get('/updateProfile', [TestController::class, 'updateProfile']);
Route::get('/deletePenawaranZap2', [TestController::class, 'deletePenawaranZap2']);
Route::get('/rollbackCart', [TestController::class, 'rollbackCart']);
Route::get('/testNPWP', [TestController::class, 'testNPWP']);
Route::get('/hitspectrum', [SpectrumTestController::class, 'index']);
Route::get('/rehitspectrum', [SpectrumTestController::class, 'rehitSpectrum']);
Route::get('/updateDeduct', [TestController::class, 'updateDeduct']);
Route::get('/updateTotal', [TestController::class, 'updateTotal']);