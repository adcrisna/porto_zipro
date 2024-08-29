<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\AdiraTransaction;
use App\Models\Product;
use App\Models\Coverages;
use App\Models\Cart;
use App\Jobs\OfferingTravelJob;
use App\Http\Controllers\CartController;
use Illuminate\Support\Str;
use Exception, DB, Validator;

class TravelController extends Controller
{
    public function error_page($message = null)
    {
        return view('travel.errorpage',compact('message'));
    }
    
    public function index(Request $request)
    {
        $token = $request->bearerToken();
        $product_id = $request->get('product_id');
        // $product_id = 370;
        // $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiN2U3OWExZWZhYWQ3ZTU0MjgzZjM4MGZlNGIwYjM0ZjhhM2MxYTAxMjcxNTA1ZjI2NjM4ZWI4ZWYxZDYzZWE5ZmZlYzg2MTk0MDRiOTRhYjkiLCJpYXQiOjE3MTM3Njg0NTcuMjMxNzMzLCJuYmYiOjE3MTM3Njg0NTcuMjMxNzM3LCJleHAiOjE3NDUzMDQ0NTcuMjIwNDgsInN1YiI6IjcxMzciLCJzY29wZXMiOltdfQ.T1w6nDbXsL3Eh6NzXKvEtL0rtumvB1vG4ZVD5ALpjQfYQXAUXmk6lxG-G2dnuK0swobaJg1ToHMicfKVkjVD7Ko_JuaE1ShcxbEao4GsTRK3ukF3teOST6045oF8-1kraSd5sZGsCwEiMkh1CHSJG0gdoJfYWZax0oX4a8K_XZq5wZBRLzM7y0zhANg2Bymkx8QszDVBm_NXRQjHqhNLw_EnFOmM3ynyewLmNkJ0JID9WdQ7VagAFPyzR85-qYrXlLfSvQlr1wq7YoYxZH51CHuQGP8PZN9hJMlNiMQfYJUZnM7IAMUVdVsbQ-4waAHeM9ZAeODBlpqMexPPD4yNi0Rv983kw-RQEQgmqTwuSdGGJqmmgRCyi4jhmFr0nAm20IQqyEZiSPoBCLLK60DTjFOtTYY4grtWznipFP6ggNkFCzbT8AyPnHpusYBulykUbXhpnstyeUoQIGIl4u6lcKYkcxQ3_VnCBYB2nuGkQ8xqQC2hg1RtGYqZkDsDH6us7h86ejg8i-liIgyR-WIgOpQxwRPJDmA76hYjyoPiJYWA6Jzo2u_x_No4Jwyhiz4sxZlFLqs99sA413gTlXPoe4oF_7LAaXKhqJ7XRk3umBsCL_vxaxRGg8KlAA8dKqmzxyRoUqgN33Sf1g-tdbb2WmaeWynhlWEVEHJ7SfwRTYk';
        $product = Product::find($product_id);
        if(empty($product)) {
            return response([
                "status" => false,
                "message" => "Invalid request"
            ], 400);
        }

        $carts = null;
        if (!empty($request->get('cart_id'))) {
            $cartID = $request->get('cart_id');
            $checkCart = Cart::find($cartID);
            if (!empty($checkCart)) {
                $carts = $checkCart;
            }else{
                $carts = null;
            }
        }
        // $carts = Cart::where('user_id', auth('api')->user()->id)->where('is_checkout', 0)->get();

        $client = new Client;
        $result = $client->get(env("TRAVEL_URL").'/regions?APIKey='.env('TRAVEL_TOKEN'));

        $client_type = new Client;
        $res_type = $client_type->get(env("TRAVEL_URL").'/package-types?APIKey='.env('TRAVEL_TOKEN'));

        $client = new Client;
        $res_travel = $client->get(env("TRAVEL_URL").'/travel-needs?APIKey='.env('TRAVEL_TOKEN'));

        $client = new Client;
        $res_origin = $client->get(env("TRAVEL_URL").'/origins?APIKey='.env('TRAVEL_TOKEN'));

        $client = new Client;
        $res_cities = $client->get(env("TRAVEL_URL").'/cities?APIKey='.env('TRAVEL_TOKEN'));

        $regions = json_decode($result->getBody(), true);
        $types = json_decode($res_type->getBody(), true)['PackageTypes'];
        $travelneeds = json_decode($res_travel->getBody(), true)['TravelNeeds'];
        $origins = json_decode($res_origin->getBody(), true)['Origins'];
        $cities = json_decode($res_cities->getBody(), true)['Cities'];
        // return count($type);
        return view('travel.maintrav', compact(
            'regions', 'types', 'travelneeds', 'origins', 'cities',
            'product_id', 'token', 'product','carts'
        ));
    }

    public function getCountry(Request $request, $data = null)
    {
        $client = new Client;
        $result = $client->get(env("TRAVEL_URL").'/regions?APIKey='.env('TRAVEL_TOKEN'));
        $regions = json_decode($result->getBody(), true);

        if(empty($data)) {
            $area_id = $request->area_id;
        }else {
            $area_id = $data['destinationArea'];
        }
        $destination = array_search($area_id, array_column($regions['Regions'], 'ID'));
        $country = $regions['Regions'][$destination]['Destinations'];

        if(empty($data)) {
            return response([
                "country" => $country
            ], 200);
        }else {
            return $country;
        }
    }


    public function prodtest()
    {
        return view('travel.formdata_back');
    }

    public function getProduct(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "*.destinationArea" => "required",
                "*.destinationCountry" => "required",
                "*.package_type" => "required",
                "*.start_date" => "required",
                "*.end_date" => "required",
                "*.birth" => "required",
                "*.days" => "required",
                "*.product_type" => "required",
                "*.travel_need" => "required",
                "*.origins" => "required"
            ], [
                "*.destinationArea.required" => "Area Tujuan wajib diisi",
                "*.destinationCountry.required" => "Negara Tujuan wajib diisi",
                "*.package_type.required" => "Tipe Paket wajib diisi",
                "*.start_date.required" => "Tanggal Pergi wajib diisi",
                "*.end_date.required" => "Tanggal Pulang wajib diisi",
                "*.birth.required" => "Tanggal lahir wajib diisi",
                "*.days.required" => "Hari wajib diisi",
                "*.product_type.required" => "Tipe Produk wajib diisi",
                "*.travel_need.required" => "Keperluan Perjalanan wajib diisi",
                "*.origins.required" => "Kota Asal wajib diisi"
            ]);
            if($validator->fails()) {
                return response([
                    "status" => false,
                    "message" => "Validation errors",
                    "data" => $validator->errors(),
                ], 422);
            }

            $data = $request->data;
            $client = new Client;
            $start_date = date_format(date_create_from_format('d/m/Y',$data['start_date']), 'Y-m-d');
            $end_date = date_format(date_create_from_format('d/m/Y',$data['end_date']), 'Y-m-d');
            $birth = date_format(date_create_from_format('d/m/Y',$data['birth']), 'Y-m-d');
            $result = $client->get(env("TRAVEL_URL").'/products',[
                'query' => [
                    'APIKey' => env('TRAVEL_TOKEN'),
                    'RegionID' => $data['destinationArea'],
                    'DestinationID' => $data['destinationCountry'],
                    'PackageTypeID' => $data['package_type'],
                    'TravelStartDate' => $start_date,
                    'TravelEndDate' => $end_date,
                    'DateOfBirth' => $birth
                ]
            ]);
            $products = json_decode($result->getBody(), true);
            if(count($products) == 0) {
                throw new Exception("Produk tidak ditemukan");
            }
            if(!empty($products['Status']) && $products['Status'] == 'failed') {
                throw new Exception($products['Message']);
            }
            $client_type = new Client;
            $res_type = $client_type->get(env("TRAVEL_URL").'/package-types?APIKey='.env('TRAVEL_TOKEN'));
            $types = json_decode($res_type->getBody(), true)['PackageTypes'];
            $key_types = array_search($data['package_type'], array_column($types, 'ID'));
            $filter = [];
            foreach($products as $key => $product) {
                if($product["TravellerTypeName"] == $data['product_type']) {
                    $coverage = Coverages::where('plan_id', $product['PlanID'])
                    ->where('product_type', $product['TravellerTypeName'])
                    ->where('package_type', $types[$key_types]['Name'])
                    ->get();
                    $filter[$key] = $product;
                    $filter[$key]['coverages'] = $coverage;
                }
            }
            $new_product = array_values($filter);
            if(count($new_product) == 0) {
                throw new Exception("Produk tidak ditemukan");
            }
            if(Str::contains(strtoupper($request->header('User-Agent')), 'POSTMANRUNTIME')) {
                return $new_product;
            }
            return view('travel.product', [
                'products' => $new_product
            ]);

        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage(),
                "line" => $e->getLine()
            ], 500);
        }

    }

    public function product_penawaran($data = []) {
        if(empty($data)) {
            return [];
        }
        
        if($data['destinationArea'] == null || $data['destinationCountry'] == null ||
            $data['package_type'] == null || $data['start_date'] == null ||
            $data['end_date'] == null || $data['birth'] == null ||
            $data['days'] == null || $data['product_type'] == null ||
            $data['travel_need'] == null || $data['origins'] == null
        ) { 
            return [];
        }
        $client = new Client;
        $start_date = date_format(date_create_from_format('d M Y',$data['start_date']), 'Y-m-d');
        $end_date = date_format(date_create_from_format('d M Y',$data['end_date']), 'Y-m-d');
        $birth = date_format(date_create_from_format('d M Y',$data['birth']), 'Y-m-d');
        $result = $client->get(env("TRAVEL_URL").'/products',[
            'query' => [
                'APIKey' => env('TRAVEL_TOKEN'),
                'RegionID' => $data['destinationArea'],
                'DestinationID' => $data['destinationCountry'],
                'PackageTypeID' => $data['package_type'],
                'TravelStartDate' => $start_date,
                'TravelEndDate' => $end_date,
                'DateOfBirth' => $birth
            ]
        ]);
        $products = json_decode($result->getBody(), true);
        if(count($products) == 0) {
            return [];
        }
        if(!empty($products['Status']) && $products['Status'] == 'failed') {
            return [];
        }
        $client_type = new Client;
        $res_type = $client_type->get(env("TRAVEL_URL").'/package-types?APIKey='.env('TRAVEL_TOKEN'));
        $types = json_decode($res_type->getBody(), true)['PackageTypes'];
        $key_types = array_search($data['package_type'], array_column($types, 'ID'));
        $filter = [];

        foreach($products as $key => $product) {
            if($product["TravellerTypeName"] == $data['product_type']) {
                $coverage = Coverages::where('plan_id', $product['PlanID'])
                ->where('product_type', $product['TravellerTypeName'])
                ->where('package_type', $types[$key_types]['Name'])
                ->get();
                $filter[$key] = $product;
                $filter[$key]['coverages'] = $coverage;
            }
        }
        $new_product = array_values($filter);
        // return $new_product;
        if(count($new_product) == 0) {
            return [];
        }
        $newData = null;
        foreach ($new_product as $np) {
            if($np['ID'] == $data['zurich_product_id']) {
                $newData = $np;
            }
        }
        return $newData;
    }

    public function travellerinfo(Request $request)
    {
        try {
            $start_date = date_format(date_create_from_format('d/m/Y',$request['start_date']), 'Y-m-d');
            $end_date = date_format(date_create_from_format('d/m/Y',$request['end_date']), 'Y-m-d');
            $birth_date = date_format(date_create_from_format('d M Y',$request['birth']), 'Y-m-d');
            $depature = null;
            
            $alreadyTrav = $request['alreadyTrav'] == 'yes' ? 1 : 0;
            if($alreadyTrav == 1) {
                $depature = date_format(date_create_from_format('d/m/Y',$request['depature_date']), 'Y-m-d');
            }

            $person = 0;
            $dateBirth = null;
            foreach ($request->penutupan as $key => $value) {
                $isAllNull = array_reduce($request->penutupan[$key],function($result, $elem) {
                    return $result && ($elem === null);
                }, true);
                if($key == 0 && $isAllNull) {
                    return response([
                        "status" => false,
                        "message" => "Terdapat input kosong."
                    ],500);
                }
                if(!$isAllNull) {
                    $validator = Validator::make($request->all(),[
                        'penutupan.*.insured_title' => 'required',
                        'penutupan.*.insured_firstname' => 'required',
                        'penutupan.*.insured_lastname' => 'required',
                        'penutupan.*.insured_birthplace' => 'required',
                        'penutupan.*.insured_dob' => 'required',
                        'penutupan.*.insured_identity' => 'required',
                        'penutupan.*.insured_noindentity' => 'required',
                        'penutupan.*.insured_alamat' => 'required',
                    ], [
                        'penutupan.*.insured_title.required'  => 'Title tidak boleh kosong.',
                        'penutupan.*.insured_firstname.required'  => 'Nama Depan tidak boleh kosong.',
                        'penutupan.*.insured_lastname.required' => 'Nama Belakang tidak boleh kosong.',
                        'penutupan.*.insured_birthplace.required' => 'Tempat Lahir tidak boleh kosong.',
                        'penutupan.*.insured_dob.required' => 'Tanggal tidak boleh kosong.',
                        'penutupan.*.insured_identity.required' => 'Identitas tidak boleh kosong.',
                        'penutupan.*.insured_noindentity.required' => 'No Indentitas tidak boleh kosong.',
                        'penutupan.*.insured_alamat.required' => 'Alamat tidak boleh kosong.',
                    ]);
                    if($validator->fails()) {
                        return response([
                            'message' => 'Terjadi Kesalahan',
                            'data' =>  $validator->errors(),
                            'status' => false
                        ], 422);
                    }
                    $person = $person + 1;
                    $dob = date_format(date_create_from_format('d M Y',$value['insured_dob']), 'Y-m-d');
                    $datenow = new \DateTime("today");
                    $valid_date = new \DateTime($birth_date);
                    $find_umur = new \DateTime($dob);
                    if(!empty($value['insured_relationship']) && $value['insured_relationship'] == "Child") {
                        if($datenow->diff($find_umur)->y > 18) {
                            return response([
                                "status" => false,
                                "message" => "Umur anak tidak boleh lebih dari 18 tahun" 
                            ],500);
                        }
                    }
                    if($datenow->diff($valid_date)->y < 70) {
                        if($datenow->diff($find_umur)->y > 70) {
                            return response([
                                "status" => false,
                                "message" => "Umur tidak boleh lebih dari 70 tahun!"
                            ],500);
                        }
                    }

                    $dateBirth = $dateBirth == null ?  $dob : $dateBirth.",".$dob;
                    if($value['insured_identity'] == "KTP") {
                        if(strlen($value['insured_noindentity']) < 16) {
                            return response([
                                "status" => false,
                                "message" => "Nomor Identitas KTP minimal 16 angka"
                            ],500);
                        }elseif(strlen($value['insured_noindentity']) > 20) {
                            return response([
                                "status" => false,
                                "message" => "Nomor Identitas KTP maksimal 20 angka"
                            ],500);
                        }
                    }elseif($value['insured_identity'] == "Passport") {
                        if(strlen($value['insured_noindentity']) > 20) {
                            return response([
                                "status" => false,
                                "message" => "Nomor Identitas PASPOR maksimal 20 angka"
                            ],500);
                        }
                    }
                }
            }

            return response([
                "status" => true,
                "type" => "validate",
                "message" => "Validation OK"
            ], 200);

        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage(). " ". $e->getLine(),
                "line" => $e->getLine()
            ], 500);
        }
    }

    public function submitTraveller(Request $request)
    {
        try {
            $cart_id = $request->cart_id;
            if($cart_id == "new_cart") {
                $cart_arr['id'] = null;
                $cart_arr['name'] = $request->cartUser;
            }else {
                $cart = Cart::find($cart_id);
                $cart_arr['id'] = $cart->id;
                $cart_arr['name'] = $cart->name;
            }

            $start_date = date_format(date_create_from_format('d/m/Y',$request['start_date']), 'Y-m-d');
            $end_date = date_format(date_create_from_format('d/m/Y',$request['end_date']), 'Y-m-d');
            $birth_date = date_format(date_create_from_format('d M Y',$request['birth']), 'Y-m-d');
            $depature = null;
            
            $alreadyTrav = $request['alreadyTrav'] == 'yes' ? 1 : 0;
            if($alreadyTrav == 1) {
                $depature = date_format(date_create_from_format('d/m/Y',$request['depature_date']), 'Y-m-d');
            }

            $person = 0;
            $dateBirth = null;
            foreach ($request->penutupan as $key => $value) {
                $isAllNull = array_reduce($request->penutupan[$key],function($result, $elem) {
                    return $result && ($elem === null);
                }, true);
                if(!$isAllNull) {
                    $person = $person + 1;
                    $dob = date_format(date_create_from_format('d M Y',$value['insured_dob']), 'Y-m-d');
                    $dateBirth = $dateBirth == null ?  $dob : $dateBirth.",".$dob;
                }
            }

            $coverages = null;
            if(!empty($request['coverages'])) {
                foreach ($request['coverages'] as $keyCover => $coverage) {
                    if(!empty($coverage)) {
                        $coverages = $coverages == null ? $coverage : $coverages.",".$coverage;
                    }
                }
            }else {
                $request['coverages'] = [];
            }

            $client = new Client;
            $resPrice = $client->get(env("TRAVEL_URL").'/price-overview',[
                'query' => [
                    'APIKey' => env('TRAVEL_TOKEN'),
                    'OriginID' => $request['origins'],
                    'RegionID' => $request['destinationArea'],
                    'DestinationID' => $request['destinationCountry'] ?? 277,
                    'ProductID' => $request['zurich_product_id'],
                    'TravelStartDate' => $start_date,
                    'TravelEndDate' => $end_date,
                    'NumOfPersons' => $person,
                    'DateOfBirths' => $dateBirth,
                    'CoverageIDs' => $coverages
                ]
            ]);
            
            $getPrice = json_decode($resPrice->getBody(), true);
            if(!empty($getPrice['Status']) && $getPrice['Status'] == "failed") {
                return response([
                    'status' => false,
                    'message' => $getPrice['Message']
                ], 500);
            }
            $insurer = $this->getFormattedForm($request->penutupan[0]);

            DB::beginTransaction();

            $product = Product::find($request['salvus_product_id']);

            $data['product_id'] = $product?->id ?? 7;
            $data['total'] = $getPrice['TotalPremium'];
            $data['inquiry_id'] = null;
        
            $cart = CartController::insert(json_decode(json_encode($data)), $cart_arr);

            $getResponseCart = json_decode(json_encode($cart, true), true)['original'];
            if($getResponseCart['status'] == false) {
                return response([
                    "status" => false,
                    "message" => $getResponseCart['message']
                ], 500);
            }

            $orders = new Order;
            $orders->user_id = auth('api')->user()->id ?? 34;
            $orders->product_id = $request['salvus_product_id'] ?? 334;
            $orders->data = $insurer;
            $orders->base_price = $getPrice['TotalPremium'];
            $orders->deduct = 0;
            $orders->total = $getPrice['TotalPremium'];
            $orders->additional_data = [$request->all()];
            $orders->start_date = $start_date;
            $orders->end_date = $end_date;
            $orders->cart_id = $getResponseCart['cart']['id'];
            $orders->status = 2;
            $orders->save();

            DB::commit();

            return response([
                "status" => true,
                "type" => "submit_data",
                "message" => "Order done!"
            ]);
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage(). " ". $e->getLine(),
                "line" => $e->getLine()
            ], 500);
        }

    }

    public function summary(Request $request)
    {
        try {
            // return $request;
            $start_date = date_format(date_create_from_format('d/m/Y',$request['start_date']), 'Y-m-d');
            $end_date = date_format(date_create_from_format('d/m/Y',$request['end_date']), 'Y-m-d');
            $dob = date_format(date_create_from_format('d M Y',$request['birth']), 'Y-m-d');

            $person = 1;
            $date = null;
            if($request['product_type'] == "Duo Plus") {
                $person = 2;
                $date = $dob.",".$dob;
            }else {
                $date = $dob;
            }

            $coverages = null;
            if(!empty($request['coverages'])) {
                foreach ($request['coverages'] as $keyCover => $coverage) {
                    if(!empty($coverage)) {
                        $coverages = $coverages == null ? $coverage : $coverages.",".$coverage;
                    }
                }
            }else {                
                $request['coverages'] = [];
            }
            $client = new Client;
            $resPrice = $client->get(env("TRAVEL_URL").'/price-overview',[
                'query' => [
                    'APIKey' => env('TRAVEL_TOKEN'),
                    'OriginID' => $request['origins'],
                    'RegionID' => $request['destinationArea'],
                    'DestinationID' => $request['destinationCountry'] ?? 277,
                    'ProductID' => $request['zurich_product_id'],
                    'TravelStartDate' => $start_date,
                    'TravelEndDate' => $end_date,
                    'NumOfPersons' => $person,
                    'DateOfBirths' => $date,
                    'CoverageIDs' => $coverages
                ]
            ]);
            
            $getPrice = json_decode($resPrice->getBody(), true);
            if(!empty($getPrice['Status']) && $getPrice['Status'] == "failed") {
                return response([
                    'status' => false,
                    'message' => $getPrice['Message']
                ], 500);
            }

            return response([
                "status" => true,
                "message" => "Success Validation"
            ]);
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage(). " ". $e->getLine(),
                "line" => $e->getLine()
            ], 500);
        }

    }

    public function offering(Request $request)
    {
        try {
            
            $cart_id = $request->cart;
            if($cart_id == "new_cart") {
                $cart_arr['id'] = null;
                $cart_arr['name'] = $request->cartUser;
            }else {
                $cart = Cart::find($cart_id);
                $cart_arr['id'] = $cart->id;
                $cart_arr['name'] = $cart->name;
            }
            $start_date = date_format(date_create_from_format('d/m/Y',$request['start_date']), 'Y-m-d');
            $end_date = date_format(date_create_from_format('d/m/Y',$request['end_date']), 'Y-m-d');
            $dob = date_format(date_create_from_format('d M Y',$request['birth']), 'Y-m-d');

            $person = 0;
            $date = null;
            if($request['product_type'] == "Duo Plus") {
                $person = 2;
                $date = $dob.",".$dob;
            }else {
                $person = 1;
                $date = $dob;
            }

            $coverages = null;
            if(!empty($request['coverages'])) {
                foreach ($request['coverages'] as $keyCover => $coverage) {
                    if(!empty($coverage)) {
                        $coverages = $coverages == null ? $coverage : $coverages.",".$coverage;
                    }
                }
            }else {                
                $request['coverages'] = [];
            }

            $client = new Client;
            $resPrice = $client->get(env("TRAVEL_URL").'/price-overview',[
                'query' => [
                    'APIKey' => env('TRAVEL_TOKEN'),
                    'OriginID' => $request['origins'],
                    'RegionID' => $request['destinationArea'],
                    'DestinationID' => $request['destinationCountry'] ?? 277,
                    'ProductID' => $request['zurich_product_id'],
                    'TravelStartDate' => $start_date,
                    'TravelEndDate' => $end_date,
                    'NumOfPersons' => $person,
                    'DateOfBirths' => $date,
                    'CoverageIDs' => $coverages
                ]
            ]);
            
            $getPrice = json_decode($resPrice->getBody(), true);
            if(!empty($getPrice['Status']) && $getPrice['Status'] == "failed") {
                return response([
                    'status' => false,
                    'message' => $getPrice['Message']
                ], 500);
            }

            DB::beginTransaction();

            $product = Product::find($request['salvus_product_id']);

            $data['product_id'] = $product?->id ?? 7;
            $data['total'] = $getPrice['TotalPremium'];
            $data['inquiry_id'] = null;
        
            $cart = CartController::insert(json_decode(json_encode($data)), $cart_arr);

            $getResponseCart = json_decode(json_encode($cart, true), true)['original'];
            if($getResponseCart['status'] == false) {
                return response([
                    "status" => false,
                    "message" => $getResponseCart['message']
                ], 500);
            }

            $orders = new Order;
            $orders->user_id = auth('api')->user()->id ?? 34;
            $orders->product_id = $request['salvus_product_id'] ?? 334;

            $orders->base_price = $getPrice['TotalPremium'];
            $orders->deduct = 0;
            $orders->total = $getPrice['TotalPremium'];
            $orders->additional_data = [$request->all()];
            $orders->start_date = $start_date;
            $orders->end_date = $end_date;
            $orders->cart_id = $getResponseCart['cart']['id'];
            $orders->status = 0;
            $orders->is_offering = 1;
            $orders->offering_name = $request['offeringName'];
            $orders->offering_email = $request['offeringMail'];
            $orders->offering_telp = $request['offeringPhone'];
            $orders->save();

            DB::commit();

            dispatch(new OfferingTravelJob($orders));

            return response([
                "status" => true,
                "message" => "Success offering"
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response([
                "status" => false,
                "message" => $e->getMessage(). " ". $e->getLine(),
                "line" => $e->getLine()
            ], 500);
        }
    }

    public function update_penawaran(Request $request, $id) 
    {
        try {
            // return $request;
            $start_date = date_format(date_create_from_format('d/m/Y',$request['start_date']), 'Y-m-d');
            $end_date = date_format(date_create_from_format('d/m/Y',$request['end_date']), 'Y-m-d');
            $dob = date_format(date_create_from_format('d M Y',$request['birth']), 'Y-m-d');

            $person = 0;
            $date = null;
            if($request['product_type'] == "Duo Plus") {
                $person = 2;
                $date = $dob.",".$dob;
            }else {
                $person = 1;
                $date = $dob;
            }

            $coverages = null;
            if(!empty($request['coverages'])) {
                foreach ($request['coverages'] as $keyCover => $coverage) {
                    if(!empty($coverage)) {
                        $coverages = $coverages == null ? $coverage : $coverages.",".$coverage;
                    }
                }
            }else {
                $request['coverages'] = [];
            }
            
            $client = new Client;
            $resPrice = $client->get(env("TRAVEL_URL").'/price-overview',[
                'query' => [
                    'APIKey' => env('TRAVEL_TOKEN'),
                    'OriginID' => $request['origins'],
                    'RegionID' => $request['destinationArea'],
                    'DestinationID' => $request['destinationCountry'] ?? 277,
                    'ProductID' => $request['zurich_product_id'],
                    'TravelStartDate' => $start_date,
                    'TravelEndDate' => $end_date,
                    'NumOfPersons' => $person,
                    'DateOfBirths' => $date,
                    'CoverageIDs' => $coverages
                ]
            ]);
            
            $getPrice = json_decode($resPrice->getBody(), true);
            
            // return $getPrice;

            DB::beginTransaction();

            $orders = Order::find($id);
            $orders->base_price = $getPrice['TotalPremium'];
            $orders->deduct = 0;
            $orders->total = $getPrice['TotalPremium'];
            $orders->additional_data = [$request->all()];
            $orders->start_date = $start_date;
            $orders->end_date = $end_date;
            $orders->status = 0;
            $orders->save();

            DB::commit();

            dispatch(new OfferingTravelJob($orders));

            return response([
                "status" => true,
                "message" => "Success offering"
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response([
                "status" => false,
                "message" => $e->getMessage(). " ". $e->getLine(),
                "line" => $e->getLine()
            ], 500);
        }
    }

    public function getCoverage(Request $request, $product_id)
    {
        try {
            $data = $request->data;
            $start_date = date_format(date_create_from_format('d/m/Y',$data['start_date']), 'Y-m-d');
            $end_date = date_format(date_create_from_format('d/m/Y',$data['end_date']), 'Y-m-d');
            $client = new Client;
            $result = $client->get(env("TRAVEL_URL").'/coverages',[
                'query' => [
                    'APIKey' => env('TRAVEL_TOKEN'),
                    'ProductID' => $product_id,
                    'TravelStartDate' => $start_date,
                    'TravelEndDate' => $end_date,
                ]
            ]);
            $products = json_decode($result->getBody(), true);
            // return $products;
            return view('travel.additional', compact('products'));
        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function edit_penawaran(Request $request, $id)
    {
        $token = $request->bearerToken();
        $order = Order::find($id);
        $product_id = $order?->product_id;
        $product = $order?->product;
        if(empty($product)) {
            return response([
                "status" => false,
                "message" => "Invalid request"
            ], 400);
        }

        $client = new Client;
        $result = $client->get(env("TRAVEL_URL").'/regions?APIKey='.env('TRAVEL_TOKEN'));

        $client_type = new Client;
        $res_type = $client_type->get(env("TRAVEL_URL").'/package-types?APIKey='.env('TRAVEL_TOKEN'));

        $client = new Client;
        $res_travel = $client->get(env("TRAVEL_URL").'/travel-needs?APIKey='.env('TRAVEL_TOKEN'));

        $client = new Client;
        $res_origin = $client->get(env("TRAVEL_URL").'/origins?APIKey='.env('TRAVEL_TOKEN'));

        $client = new Client;
        $res_cities = $client->get(env("TRAVEL_URL").'/cities?APIKey='.env('TRAVEL_TOKEN'));

        $data = $order->additional_data[0];
        // return $data;
        $data['start_date'] = date_format(date_create_from_format('d/m/Y',$data['start_date']), 'd M Y');
        $data['end_date'] = date_format(date_create_from_format('d/m/Y',$data['end_date']), 'd M Y');
        if(!empty($data['alreadyTravel']) && $data['alreadyTrav'] == 'yes') {
            if(!$this->validateDate($data['depature_date'])) {
                $data['depature_date'] = date_format(date_create_from_format('d/m/Y',$data['depature_date']), 'd M Y');
            }
        }

        $regions = json_decode($result->getBody(), true);
        $types = json_decode($res_type->getBody(), true)['PackageTypes'];
        $travelneeds = json_decode($res_travel->getBody(), true)['TravelNeeds'];
        $origins = json_decode($res_origin->getBody(), true)['Origins'];
        $cities = json_decode($res_cities->getBody(), true)['Cities'];
        $data['getCountry'] = $this->getCountry($request,$data);
        $data['zproduct'] = $this->product_penawaran($data);
        // return $data['zproduct'];
        if(empty($data['zproduct'])) {
            return $this->error_page('Product tidak ditemukan atau telah berubah');
        }

        $start_date = date_format(date_create_from_format('d M Y',$data['start_date']), 'Y-m-d');
        $end_date = date_format(date_create_from_format('d M Y',$data['end_date']), 'Y-m-d');
        $client_cov = new Client;
        $result_cov = $client_cov->get(env("TRAVEL_URL").'/coverages',[
            'query' => [
                'APIKey' => env('TRAVEL_TOKEN'),
                'ProductID' => $data['zproduct']['ID'],
                'TravelStartDate' => $start_date,
                'TravelEndDate' => $end_date,
            ]
        ]);
        $data['price'] = $order->total;
        $data['zurich_coverages'] = [];
        foreach (json_decode($result_cov->getBody(), true) as $keyCover => $zcover) {
            if(!empty($data['coverages']) && in_array($zcover['ID'], $data['coverages'])) {
                $data['zurich_coverages'][] = $zcover;
            }
        }
        // return $data['zurich_coverages'];
        return view('travel.penawaran.penawaran', compact('regions', 'types', 'travelneeds', 'origins', 'cities','product_id', 'token', 'product', 'data', 'order'));
    }

    public function penutupan(Request $request,$id)
    {
        
        $order = Order::find($id);
        $data = $order->additional_data[0];
        $token = $request->bearerToken();
        $product_id = $order?->product_id;
        $product = $order?->product;
        if(empty($product)) {
            return response([
                "status" => false,
                "message" => "Invalid request"
            ], 400);
        }
        
        return view('travel.penutupan.penutupan', compact('order', 'token', 'data'));

    }

    public function submit_penutupan(Request $request, $id)
    {
        try {
            $order = Order::find($id);
            
            $data = $order->additional_data[0];
            $start_date = date_format(date_create_from_format('d/m/Y',$data['start_date']), 'Y-m-d');
            $end_date = date_format(date_create_from_format('d/m/Y',$data['end_date']), 'Y-m-d');
            $birth_date = date_format(date_create_from_format('d M Y',$data['birth']), 'Y-m-d');
            $depature = null;
            
            if($start_date < date('Y-m-d')) {
                return response([
                    'status' => false,
                    'message' => "Tanggal pergi tidak boleh kurang dari hari ini!"
                ], 500);
            }

            $alreadyTrav = 0;
            if(!empty($data['alreadyTrav']) && $data['alreadyTrav'] == "yes") {
                $alreadyTrav = 1;
            }
            
            if($alreadyTrav == 1) {
                if(!$this->validateDate($data['depature_date'])) {
                    $depature = date_format(date_create_from_format('d/m/Y',$data['depature_date']), 'Y-m-d');
                }else {
                    $depature = date_format(date_create_from_format('d M Y',$data['depature_date']), 'Y-m-d');
                }
            }

            $person = 0;
            $dateBirth = null;

            foreach ($request->penutupan as $key => $value) {
                $isAllNull = array_reduce($request->penutupan[$key],function($result, $elem) {
                    return $result && ($elem === null);
                }, true);
                if($key == 0 && $isAllNull) {
                    return response([
                        "status" => false,
                        "message" => "Terdapat input kosong."
                    ],422);
                }
                if(!$isAllNull) {
                    $validator = Validator::make($request->all(),[
                        'penutupan.*.insured_title' => 'required',
                        'penutupan.*.insured_firstname' => 'required',
                        'penutupan.*.insured_lastname' => 'required',
                        'penutupan.*.insured_birthplace' => 'required',
                        'penutupan.*.insured_dob' => 'required',
                        'penutupan.*.insured_identity' => 'required',
                        'penutupan.*.insured_noindentity' => 'required',
                        'penutupan.*.insured_alamat' => 'required',
                    ], [
                        'penutupan.*.insured_title.required'  => 'Title tidak boleh kosong.',
                        'penutupan.*.insured_firstname.required'  => 'Nama Depan tidak boleh kosong.',
                        'penutupan.*.insured_lastname.required' => 'Nama Belakang tidak boleh kosong.',
                        'penutupan.*.insured_birthplace.required' => 'Tempat Lahir tidak boleh kosong.',
                        'penutupan.*.insured_dob.required' => 'Tanggal tidak boleh kosong.',
                        'penutupan.*.insured_identity.required' => 'Identitas tidak boleh kosong.',
                        'penutupan.*.insured_noindentity.required' => 'No Indentitas tidak boleh kosong.',
                        'penutupan.*.insured_alamat.required' => 'Alamat tidak boleh kosong.',
                    ]);

                    if($validator->fails()) {
                        return response([
                            'message' => 'Terjadi kesalahan',
                            'data' =>  $validator->errors(),
                            'status' => false
                        ], 422);
                    }
                    $person = $person + 1;
                    $dob = date_format(date_create_from_format('d M Y',$value['insured_dob']), 'Y-m-d');
                    $datenow = new \DateTime("today");
                    $valid_date = new \DateTime($birth_date);
                    $find_umur = new \DateTime($dob);
                    if(!empty($value['insured_relationship']) && $value['insured_relationship'] == "Child") {
                        if($datenow->diff($find_umur)->y > 18) {
                            return response([
                                "status" => false,
                                "message" => "Umur anak tidak boleh lebih dari 18 tahun" 
                            ],422);
                        }
                    }
                    if($datenow->diff($valid_date)->y < 70) {
                        if($datenow->diff($find_umur)->y > 70) {
                            return response([
                                "status" => false,
                                "message" => "Umur tidak boleh lebih dari 70 tahun!"
                            ],422);
                        }
                    }

                    $dateBirth = $dateBirth == null ?  $dob : $dateBirth.",".$dob;

                    if($value['insured_identity'] == "KTP") {
                        if(strlen($value['insured_noindentity']) < 16) {
                            return response([
                                "status" => false,
                                "message" => "Nomor Identitas KTP minimal 16 angka"
                            ],500);
                        }elseif(strlen($value['insured_noindentity']) > 20) {
                            return response([
                                "status" => false,
                                "message" => "Nomor Identitas KTP maksimal 20 angka"
                            ],500);
                        }
                    }elseif($value['insured_identity'] == "Passport") {
                        if(strlen($value['insured_noindentity']) > 20) {
                            return response([
                                "status" => false,
                                "message" => "Nomor Identitas PASPOR maksimal 20 angka"
                            ],500);
                        }
                    }
                }
            }
            $coverages = null;
            if(!empty($data['coverages'])) {
                foreach ($data['coverages'] as $keyCover => $coverage) {
                    if(!empty($coverage)) {
                        $coverages = $coverages == null ? $coverage : $coverages.",".$coverage;
                    }
                }
            }else {
                $data['coverages'] = [];
            }
            $data['penutupan'] = $request->penutupan;
            $client = new Client;
            $resPrice = $client->get(env("TRAVEL_URL").'/price-overview',[
                'query' => [
                    'APIKey' => env('TRAVEL_TOKEN'),
                    'OriginID' => $data['origins'],
                    'RegionID' => $data['destinationArea'],
                    'DestinationID' => $data['destinationCountry'] ?? 277,
                    'ProductID' => $data['zurich_product_id'],
                    'TravelStartDate' => $start_date,
                    'TravelEndDate' => $end_date,
                    'NumOfPersons' => $person,
                    'DateOfBirths' => $dateBirth,
                    'CoverageIDs' => $coverages
                ]
            ]);
            
            $getPrice = json_decode($resPrice->getBody(), true);

            $insurer = $this->getFormattedForm($request->penutupan[0]);

            DB::beginTransaction();
            $order->data = $insurer;
            $order->base_price = $getPrice['TotalPremium'];
            $order->total = $getPrice['TotalPremium'];
            $order->additional_data = [$data];
            $order->status = 2;
            $order->is_offering = 0;
            $order->save();

            DB::commit();

            return response([
                "status" => true,
                "message" => "Order done!"
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response([
                "status" => false,
                "message" => $e->getMessage(). " ". $e->getLine(),
                "line" => $e->getLine()
            ], 500);
        }
    }

    public function getFormattedForm($data) 
    {
        if($data['insured_title'] == "tuan") {
            $jenis = "Pria";
        }else {
            $jenis = "Wanita";
        }

        $dob = date_format(date_create_from_format('d M Y',$data['insured_dob']), 'Y-m-d');

        $insurer = [
            "1" => [
                "type" => "text",
                "data" => $data['insured_firstname']." ".$data['insured_lastname'],
                "name" => "NAMA TERTANGGUNG"
            ],
            "33" => [
                "type" => "number",
                "data" => $data['insured_noindentity'],
                "name" => "NOMOR IDENTITAS (KTP)"
            ],
            "44" => [
                "type" => "text",
                "data" => $data['insured_birthplace'],
                "name" => "TEMPAT LAHIR"
            ],
            "5" => [
                "type" => "text",
                "data" => $dob." 00:00:00.000",
                "name" => "TANGGAL LAHIR"
            ],
            "37" => [
                "type" => "drop",
                "data" => $jenis,
                "name" => "JENIS KELAMIN"
            ],
            "36" => [
                "type" => "text",
                "data" => $data['insured_alamat'],
                "name" => "ALAMAT RUMAH"
            ],
            "13" => [
                "type" => "number",
                "data" => $data['insured_phone'],
                "name" => "NOMOR PONSEL"
            ],
            "9" => [
                "type" => "email",
                "data" => $data['insured_email'],
                "name" => "EMAIL"
            ],
        ];

        return $insurer;
    }

    public function validateDate($date, $format = 'd M Y')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
