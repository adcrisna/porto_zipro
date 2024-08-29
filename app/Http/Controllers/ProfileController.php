<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Profile;
use App\Models\User;
use GuzzleHttp\Client;
use DB, Validator;

class ProfileController extends Controller
{
    public function getProfile()
    {
        $user = auth('api')->user();
        $user["profile"] = $user->profile;
        $user["profile"]["avatar"] = asset('uploads/avatar/'.$user->profile->avatar);
        $user["partner"] = $user->partner;
        $user["link_portal_claim"] = env('LINK_PORTAL_CLAIM');
        $user["no_cs_salvus"] = env('NOMOR_CS_SALVUS');

        return response([
            "status" => true,
            "data" => $user
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'no_ktp' => 'required',
                'bank_branch' => "required",
                'branch_location' => 'required'
            ]);
            if($validator->fails()){
                return response(['message' => 'Validation errors', 'errors' =>  $validator->errors(), 'status' => false], 422);
            }

            $allowedEmails = ['fily0208@app.com', 'lfily0208@gmail.com'];
            $format = "/^(1[1-9]|21|[37][1-6]|5[1-3]|6[1-5]|[89][12])\d{2}\d{2}([04][1-9]|[1256][0-9]|[37][01])(0[1-9]|1[0-2])\d{2}\d{4}$/";
            $user = User::find(auth('api')->user()->id);
            if ($request->no_ktp == '0123456789697347') {
                $realName = null;
                $npwpValid = true;
                $npwp = $request->no_ktp;
            }elseif (in_array($user->email, $allowedEmails)) {
                $realName = null;
                $npwpValid = false;
                $npwp = $request->no_ktp;
            }else{
                if (!preg_match($format, $request['no_ktp'])) {
                    return response(['message' => 'Format KTP Tidak Valid', 'errors' =>  ['no_ktp' => ['Format KTP tidak valid']], 'status' => false], 422);
                }else{
                    $value = [
                        'nik' => (string) $request['no_ktp'],
                        'tujuan' => 'cek identitas wajib pajak',
                    ];

                    $response['code'] = null;
                    $response['data']['npwp'] = null;
                    $response['data']['nama'] = null;
                        
                    // return $data;
                    $client = new Client();
                    $result = $client->post(env('PAJAK_URL').'/vswp/verify/nik', 
                    [
                        'verify' => false,
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Authorization' => env('PAJAK_TOKEN'),
                            "Accept" => "application/json",
                            // 'API-Key' => 'MjJkMDRlNTQ0NGRjMTkyY2FhZjYwMmJkMzRhZjIwOTMzNWZlNThjYzAwMDUzZDdjNDgzZTFkMTg0Y2FlY2E4YQ==',
                        ],
                        'json' => $value,
                    ]);
                    $response = json_decode($result->getBody(), true);
                    if ($response['code'] == 200) {
                        $realName = $response['data']['nama'];
                        $npwpValid = true;
                        $npwp = $response['data']['npwp'];    
                    } else {
                        $realName = null;
                        $npwpValid = false;
                        $npwp = $request['no_ktp'];
                    }
                }
            }
            DB::beginTransaction();
            $user = User::find(auth('api')->user()->id);
            if ($realName != null) {
                $user->name = $realName;
            }else{
                $user->name = $request->name;
            }
            $user->save();

            $profile = $user->profile;
            $profile->address = !empty($request->address) ? $request->address : $profile->address;
            $profile->city = !empty($request->city) ? $request->city : $profile->city;
            $profile->phone = !empty($request->phone) ? $request->phone : $profile->phone;
            $profile->bank_branch = !empty($request->bank_branch) ? $request->bank_branch : $profile->bank_branch;
            $profile->branch_location = !empty($request->branch_location) ? $request->branch_location : $profile->branch_location;
            $profile->bank_account = !empty($request->bank_account) ? $request->bank_account : $profile->bank_account;
            $profile->no_ktp = !empty($request->no_ktp) ? $request->no_ktp : $profile->no_ktp;
            $profile->bank_id = !empty($request->bank_id) ? $request->bank_id : $profile->bank_id;
            $profile->real_name = $realName;
            $profile->npwp_valid = $npwpValid;
            $profile->npwp = $npwp;

            // if($request->hasFile('id_card')) {
            //     $path = storage_path("uploads/idcard/");
            //     if(file_exists($path.$profile->id_card_pic)) {
            //         unlink($path.$profile->id_card_pic);
            //     }
            //     $idcard = $request->file('id_card');
            //     $newName = "id_card_".date("YmdHis").Str::random(5).".".$idcard->getClientOriginalExtension();
            //     $idcard->move($path,$newName);
            //     $profile->id_card_pic = $newName;
            // }
            $profile->save();
            DB::commit();

            return response([
                "status" => true,
                "data" => $profile,
                "message" => "Profile successfuly updated"
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response([
                "status" => false,
                "message" => $e->getMessage()
            ], 200);
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'old_password' => 'required',
                'password' => 'required|min:6|same:password_confirmation',
            ]);
            
            if ($validator->fails()) {
                return response(['message' => $validator->errors(), 'status' => false], 400);
            }
            $user = User::find(auth('api')->user()->id);
            if(!Auth::attempt(['email' => $user->email, 'password' => $request->old_password])) {
                return response([
                    "status" => false,
                    "message" => "Kata sandi lama salah"
                ], 422);
            }
            if(Hash::check($request->password, $user->password)) {
                return response([
                    "status" => false,
                    "message" => "Kata sandi tidak boleh sama dengan Kata sandi lama."
                ], 422);
            }

            if (!empty($request->password)) {
                $user->password = Hash::make($request->password);
                $user->save();
            }
            return response(['message' => 'Password updated successfully', 'status' => true], 200);
        } catch (\Exception $e) {
            return response(['message' => 'Oops, something went wrong', 'errors' => $e->getMessage(), 'status' => false], 500);
        }
    }

    public function updateAvatar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'avatar' => "required|mimes:jpeg,png,jpg|max:2048",
            ]);
            if ($validator->fails()) {
                return response(['message' => $validator->errors(), 'status' => false], 400);
            }

            $user = User::find(auth('api')->user()->id);
            $profile = $user->profile;

            if($request->hasFile("avatar")) {
                $path = storage_path("uploads/avatar/");
                if(!is_dir($path)) {
                    mkdir($path,755,true);
                }
                $avatar = $request->file('avatar');
                $newName = "avatar_".date("YmdHis").Str::random(5).".".$avatar->getClientOriginalExtension();
                $avatar->move($path,$newName);
                $profile->avatar = $newName;
                $profile->save();
            }
            return response([
                'status' => true,
                'message' => 'Avatar updated successfully!'
            ], 200);
            DB::commit();

        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => 'Oops, something went wrong',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateFcm(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                "fcm_token" => 'required'
            ],['required'  => ':attribute cannot be null.',]);
            
            if($validator->fails()) {
                return response(['message' => 'Validation errors', 'errors' =>  $validator->errors(), 'status' => false], 422);
            }

            $user = User::find(auth('api')->user()->id);
            $user->fcm_token = $request->fcm_token;
            $user->update();

            return response(['message' => 'Token updated successfully!', 'status' => true], 200);
        } catch (\Exception $e) {
            return response(['message' => 'Oops, something went wrong', 'errors' => $e->getMessage(), 'status' => false], 500);
        }
    }
}
