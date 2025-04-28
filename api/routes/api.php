<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\v1\GpcsController;
use App\Http\Controllers\api\v1\GenerateGpcsController;
use App\Http\Controllers\api\v1\GetCountryCodeFromOpenStreetMapController;
use App\Http\Controllers\api\v1\GetCountryCodeFromGoogleMapController;
use App\Http\Controllers\api\v1\Users\RegistrationsController;
use App\Http\Controllers\api\v1\GetCountryCodeFromDomainController;
use App\Http\Controllers\api\v1\Users\AuthController;
use App\Http\Controllers\api\v1\Users\GenerateCodeController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('generategpcscode', [GpcsController::class, 'generateGPCSCode']);
Route::get('generatecode',[GenerateGpcsController::class, 'generateGPCSCode']);
Route::get('getcountrycodefromopenstreetmap',[GetCountryCodeFromOpenStreetMapController::class,'getCountryCodeFromOpenStreetMap']);
Route::get('getcountrycodefromgooglemap',[GetCountryCodeFromGoogleMapController::class,'getCountryCodeFromGoogleMap']);
Route::get('getcountrycodefromdomain',[GetCountryCodeFromDomainController::class,'getCountryCodeFromDomain']);
Route::post('signup',[RegistrationsController::class,'registration']);
Route::post('login', [AuthController::class, 'signin']);
Route::middleware('auth:sanctum')->group( function () {
    Route::get('codes', [GenerateCodeController::class,'index']);
    Route::post('code', [GenerateCodeController::class,'store']);
});
