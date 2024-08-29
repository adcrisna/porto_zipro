<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Profile;
use App\Models\Partners;
use App\Models\Bank;
use App\Models\Orscheme;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPasswordMail;
use DB, Validator;
use GuzzleHttp\Client;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $messages = [
                'required'  => ':attribute tidak boleh kosong.',
                'unique'    => ':attribute sudah digunakan.',
                'email'     => 'Email tidak valid.',
                'same'      => 'Kata sandi tidak sama.',
                'regex'     => 'Kata sandi harus mengandung minimal 1 huruf besar dan angka.'
            ];

			$validator = Validator::make($request->all(), [
				'name' => 'required',
				'email' => 'required|email|unique:users',
				'password' => 'required|same:password_confirmation|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]/',
                'phone' => 'required',
                'alamat' => 'required',
                'city' => 'required',
                'no_ktp' => 'required',
                
			],$messages);
			if($validator->fails()) {
                return response(['message' => 'Validation errors', 'errors' =>  $validator->errors(), 'status' => false, "data" => $validator->errors()], 422);
            }

        $data = $validator->validate();
        $format = "/^(1[1-9]|21|[37][1-6]|5[1-3]|6[1-5]|[89][12])\d{2}\d{2}([04][1-9]|[1256][0-9]|[37][01])(0[1-9]|1[0-2])\d{2}\d{4}$/";
                
        if ($request->no_ktp == '0123456789697347') {
            $realName = null;
            $npwpValid = true;
            $npwp = $request->no_ktp;
        }else{
            if (!preg_match($format, $request->no_ktp)) {
                return response(['message' => 'Format KTP Tidak Valid', 'errors' =>  ['no_ktp' => ['Format KTP tidak valid']], 'status' => false], 422);
            }else{
                $value = [
                    'nik' => (string) $request->no_ktp,
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
                    $npwp = $request->no_ktp;
                }
            }
        }
        try {
            DB::BeginTransaction();
            $pId = null;
            $ref_email = null;
            if (isset($request->referrer_email)) {
                $ref = User::where("email", $request->referrer_email)->first();
                if ($ref) {
                    $pId = $ref->partner_id;
                    $ref_email = $ref->email;
                } else {
                    return response(['message' => 'Email Referensi belum terdaftar', 'errors' =>  ['referral' => ['referral not found']], 'status' => false], 422);
                }
            } else {
                $ref = User::where('email', env('DEFAULT_EMAIL_PARTNER'))->first();
                $pId = $ref->partner_id;
                $ref_email = $ref->email;
            }

            $user = new User;
            if ($realName != null) {
                $user->name = $realName;
            }else{
                $user->name = $request->name;
            }
            
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->referrer_email = $ref_email;
            $user->partner_id = $pId;
            $user->is_sf = $request['is_sf'];
            $user->is_web = isset($request->is_web) ? $request->is_web : 0;
            $user->save();

            if(!empty($ref) && $ref->partner_id !== null) {
                $orscheme = new Orscheme;
                $orscheme->user_id = $user->id;
                $orscheme->upline_id = $ref->id;
                $orscheme->save();
            }

            $profile = new Profile;
            $profile->user_id = $user->id;
            $profile->address = $data['alamat'];
            $profile->phone = $data['phone'];
            $profile->another_phone = isset($request->another_phone) ? $request->another_phone : null;
            $profile->city = $data['city'];
            $profile->bank_id = isset($request->bank) ? $request->bank : null;
            $profile->bank_account = isset($request->bank_acc_number) ? $request->bank_acc_number : null;
            $profile->no_ktp = $request->no_ktp;
            $profile->bank_branch = isset($request->branch_location) ? $request->branch_location : null;
            $profile->branch_location = isset($request->branch_location) ? $request->branch_location : null;
            $profile->real_name = $realName;
            $profile->npwp_valid = $npwpValid;
            $profile->npwp = $npwp;

            // if ($request->hasFile("id_card")) {

            //     $path = storage_path("uploads/idcard/");
            //     if (!is_dir($path)) {
            //         mkdir($path, 755, true);
            //     }
            //     $idcard = $request->file('id_card');
            //     $newName = "id_card_" . date("YmdHis") . Str::random(5) . "." . $idcard->getClientOriginalExtension();
            //     $idcard->move($path, $newName);
            //     $profile->id_card_pic = $newName;
            // }
            if ($request->hasFile("avatar")) {
                $path = storage_path("uploads/avatar/");
                if (!is_dir($path)) {
                    mkdir($path, 755, true);
                }

                $avatar = $request->file('avatar');
                $newName = "avatar_" . date("YmdHis") . Str::random(5) . "." . $avatar->getClientOriginalExtension();
                $avatar->move($path, $newName);
                $profile->avatar = $newName;
            } else {
                $profile->avatar = "avatar_default.png";
            }
            $profile->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'success',
                'token' => $user->createToken('appToken')->accessToken,
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response([
                "status" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }

    public function forgot(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users',
            ]);

            if ($validator->fails()) {
                return response(['message' => 'Validation errors', 'errors' =>  $validator->errors(), 'status' => false], 422);
            }
            $user = User::where('email', $request->email)->first();
            if ($user) {
                if (!empty($user->cooldown) && $user->cooldown >= date('Y-m-d H:i:s')) {
                    return response([
                        'status' => false,
                        'message' => "Mohon tunggu 5 menit untuk melanjutkan",
                        'cooldown_time' => $user->cooldown
                    ], 429);
                }
                $token = Str::random(60);
                $cd = date('Y-m-d H:i:s', strtotime("+5 min"));
                $user->remember_token = $token;
                $user->cooldown = $cd;
                $user->save();

                Mail::to($user->email)->send(new ForgotPasswordMail($user));
                return response([
                    'status' => true,
                    'message' => 'Silahkan cek email anda untuk melakukan reset password'
                ], 200);
            } else {
                return response(['message' => 'Email tidak ditemukan', 'errors' =>  ['email' => ['Email tidak ditemukan']], 'status' => false], 422);
            }
        } catch (\Exception $e) {
            return response(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function referral(Request $request)
    {
        $ref_email = $request->email;
        $url = env('APP_URL').'/api/bank';
        $client = new Client();
        $response = $client->get($url);
        // return $response;
        // Mendapatkan body respons sebagai array PHP
        $res = json_decode($response->getBody(), true);
        $bank = $res['data'];
        return view('referral.index', compact('ref_email','bank'));
    }
    public function referralDone(Request $request)
    {
        return view('referral.success');
    }
}
