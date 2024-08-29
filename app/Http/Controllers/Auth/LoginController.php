<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Partners;
use App\Models\Bank;
use App\Models\Profile;
use Validator;
use DB;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required",
            "password" => "required",
        ]);

        if($validator->fails()) {
            return response(["status" => false, "message" => "Validator Errors", "data" => $validator->errors()], 422);
        }

        try {
           if(Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $user = Auth::user();
                $success = $user->createToken('appToken')->accessToken;
                return response()->json([
                    'status' => true,
                    'token_type' => 'Bearer',
                    'token' => $success,
                    'user' => $user
                ],200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Email atau Kata Sandi yang anda masukan salah!',
                ], 422);
            }

        } catch (\Exception $e) {
           return response(["status" => false, "message" => $e->getMessage()], 500);
        }
    }
}
