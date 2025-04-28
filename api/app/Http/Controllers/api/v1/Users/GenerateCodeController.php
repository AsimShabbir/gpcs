<?php

namespace App\Http\Controllers\api\v1\Users;

use App\Http\Controllers\api\v1\BaseController as BaseController;
use App\Enums\StatusCode;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\GpcsCode;
use App\Http\Requests\GpscRequest;
use App\Traits\GenerateGPCSCode;
use App\Traits\GetCountryCodeFromDomain;
use App\Traits\GetCountryCodeFromGoogleMap;
use App\Http\Requests\Users\GpscCodeGenerationRequest;

class GenerateCodeController extends BaseController
{
    use GenerateGPCSCode,GetCountryCodeFromDomain,GetCountryCodeFromGoogleMap;
    //
    public function __construct()
    {
    }
    public function index(Request $request)
    {
       $auth_user = Auth::User();
       $user_id = $auth_user->id;
       $latestGpcsCode = GpcsCode::where('user_id', $user_id)
            ->latest() // Orders by created_at DESC
            ->first();

        if ($latestGpcsCode) {
            // Access the latest record's attributes
            return response()->json($latestGpcsCode); // Returns the record as JSON
        } else {
            return response()->json(['message' => 'No GpcsCode found for this user.'], 404);
        }
       dd($auth_user->id);
    }
    public function store(GpscRequest $request)
    {

        $auth_user = Auth::User();
        $user_id = $auth_user->id;

        $first_code="";
        $second_code="";
        // $latestGpcsCode = GpcsCode::where('user_id', $user_id)
        //      ->latest() // Orders by created_at DESC
        //      ->first();

        //  if ($latestGpcsCode) {
        //      // Access the latest record's attributes
        //      //return response()->json($latestGpcsCode); // Returns the record as JSON
        //      $first_code = $latestGpcsCode->first_part;
        //      $second_code = $latestGpcsCode->second_part;
        //  } else {
        //     // return response()->json(['message' => 'No GpcsCode found for this user.'], 404);
        //     $first_code=null;
        //     $second_code=null;
        //  }

         $latitude = $request->latitude;
         $longitude = $request->longitude;
         $domain = $request->domain;
         $label = $request->label;
         $country_code = "";
         //dd($domain);
         if(isset($domain))
         {
          //   dd("hello");
             $country_code = $this->getCountryCode($request->domain);
            // return response()->json(['error' => 'domain is missing'], 400);
         }
         else if(isset($latitude) and isset($longitude))
         {
          //  dd("hello");
             $country_code = $this->getCountry_Code($latitude, $longitude);
           //  return response()->json(['error' => 'Latitude / Longitude must required'], 400);
         }
         else{
             return response()->json(['error' => 'Domain or Latitude / Longitude must required'], 400);
         }
        // dd($country_code);
         $latestGpcsCode = GpcsCode::where('user_id', $user_id)
         ->where('country_code', $country_code)
         ->latest() // Orders by created_at DESC
         ->first();

     if ($latestGpcsCode) {
         // Access the latest record's attributes
         //return response()->json($latestGpcsCode); // Returns the record as JSON
         $first_code = $latestGpcsCode->first_part;
         $second_code = $latestGpcsCode->second_part;
     } else {
        // return response()->json(['message' => 'No GpcsCode found for this user.'], 404);
        $first_code=null;
        $second_code=null;
     }
         $first_part = $this->generateCode($first_code);
        $second_part = $this->generateCode($second_code);
        //dd($stripeSecretKey);
        $gpcs_code = $country_code . "-" . $first_part . "-" . $second_part;
        try {
          //  $request->save();
          $GpscCodeGenerationRequest = new GpcsCode();
          $GpscCodeGenerationRequest->first_part = $first_part;
          $GpscCodeGenerationRequest->second_part = $second_part;
          $GpscCodeGenerationRequest->user_id = $user_id;
          $GpscCodeGenerationRequest->country_code = $country_code;
          $GpscCodeGenerationRequest->gpcscode = $gpcs_code;
          $GpscCodeGenerationRequest->domain = $domain;
          $GpscCodeGenerationRequest->latitude = $latitude;
          $GpscCodeGenerationRequest->longitude = $longitude;
          $GpscCodeGenerationRequest->label = $label;
          $GpscCodeGenerationRequest->is_deleted=0;
          $GpscCodeGenerationRequest->save();
            return $this->renderResponse('Success',['success' => 'Code added successfully','data' =>$GpscCodeGenerationRequest],StatusCode::OK);
        } catch (QueryException $exception) {
            // Check if the exception is related to unique violation
            if ($exception->errorInfo[1] == 7) {
                return $this->renderResponseWithErrors('Error', ['error'=> 'Client already exists'], StatusCode::UNPROCESSABLE_ENTITY);
            }
        }
    }
}
