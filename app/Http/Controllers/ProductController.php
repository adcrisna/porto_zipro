<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use App\Models\PartnerProduct;
use App\Models\FormRepoCategory;
use App\Models\FormRepo;
use App\Models\Arrays;
use App\Models\Category;
use Illuminate\Support\Str;

class ProductController extends Controller
{

    public function list(Request $request)
    {
        try {
            $user = auth('api')->user();
            // return $user;
            if (empty($user)) {
                $user = User::where('email', env('DEFAULT_EMAIL_PARTNER'))->first();
            }
            if ($request->has('category')) {
                return $this->searchByCategory($request->category);
            }

            $rel = PartnerProduct::where("partner_id", $user->partner_id)->with(["product" => function ($query) {
                $query->select(['id', 'name', 'category_id', 'description', 'price', 'logo', 'learn', 'is_enable', 'flow', 'is_pg'])->where("is_enable", 1)->latest();
            }])->get();
            
            $cat = [];
            $data = [];
            $prod = [];
            foreach ($rel as $keyP => $re) {
                if (!empty($re['product'])) {
                    $prod[$keyP] = $re->product;
                    $prod[$keyP]['display_name'] = $re->product->name;
                    if ($re->product->flow == "web") {
                        $dname = strtoupper($re->product->name);
                        if (Str::contains($dname, ['TRAVEL', 'TRAVELLING', 'TRAVELLIN', 'ZTI'])) {
                            $prod[$keyP]['link'] = route('travel.index');
                        } elseif (Str::contains($dname, ['PA MUDIK', 'MUDIK', "PERJALANAN", "PA PERJALANAN", "PERSONAL", "ACCIDENT"])) {
                            $prod[$keyP]['link'] = route('perjalanan.index');
                        }
                    } else {
                        $prod[$keyP]['link'] = "";
                    }
                    if (isset($re->product->category_id) && !in_array($re->product->category_id, $cat)) {
                        $cat[] = $re->product->category_id;
                    }
                    $prod[$keyP]['logo'] = asset('uploads/product/' . $re->product->logo);
                }
            }
            
            return response([
                'data' => array_values($prod),
                'message' => count($prod) > 0 ? "Data found" : "Data is empty",
                'status' => true
            ], 200);
        } catch (\Exception $e) {
            return response(['message' => $e->getLine(), 'errors' => $e->getMessage(), 'status' => false], 500);
        }
    }

    public function detailProduct($id)
    {
        try {
            $product = Product::find($id);
            if (empty($product)) {
                return response(['message' => "Data not found", 'errors' => "", 'status' => false], 400);
            }

            $form = $this->getFormRepo($product->category_id);
            $product->logo = asset('uploads/product/' . $product->logo);
            return response(['data' => $product, 'form' => $form, 'message' => 'data found', 'status' => true], 200);
        } catch (\Exception $e) {
            return response(['message' => 'Oops, something went wrong', 'errors' => $e->getMessage(), 'status' => false], 500);
        }
    }

    public function search(Request $request)
    {
        try {
            $user = auth('api')->user();
            $partner_id = 2;
            if (!empty($user)) {
                $partner_id = $user->partner_id;
            }
            $getproducts = PartnerProduct::where("partner_id", $partner_id)->with(["product" => function ($query) use ($request) {
                $query->select(['id', 'name', 'category_id', 'description', 'price', 'logo', 'learn', 'is_enable', 'flow', 'is_pg'])->where("is_enable", 1)
                    ->where('name', 'LIKE', '%' . $request->get('name') . '%');
            }])->get();

            $products = [];

            foreach ($getproducts as $keyP => $product) {
                if (!empty($product->product)) {
                    $products[$keyP] = $product->product;
                    $form = $this->getFormRepo($product->product->category_id);
                    $products[$keyP]['form'] = $form;
                    $products[$keyP]['logo'] = asset('uploads/product/' . $product->product->logo);
                    if ($product->product->flow == "web") {
                        $dname = strtoupper($product->product->name);
                        if (Str::contains($dname, ['TRAVEL', 'TRAVELLING', 'TRAVELLIN'])) {
                            $products[$keyP]['link'] = route('travel.index');
                        } elseif (Str::contains($dname, ['PA MUDIK', 'MUDIK', "PERJALANAN", "PA PERJALANAN", "PERSONAL", "ACCIDENT"])) {
                            $products[$keyP]['link'] = route('perjalanan.index');
                        }
                    } else {
                        $products[$keyP]['link'] = "";
                    }
                }
            }

            return response(['data' => array_values($products), 'message' => count($products) > 0 ? "Data found" : "Data is empty", 'status' => true], 200);
        } catch (\Exception $e) {
            return response(['message' => 'Oops, something went wrong', 'errors' => $e->getMessage(), 'status' => false], 500);
        }
    }

    public function searchByCategory($category_id)
    {
        $delimiter = explode(",", $category_id);

        $user = auth('api')->user();
        if (empty($user)) {
            $user = User::where('email', env('DEFAULT_EMAIL_PARTNER'))->first();
        }
        $getproducts = PartnerProduct::where("partner_id", $user->partner_id)->with(["product" => function ($query) use ($delimiter) {
            $query->select(['id', 'name', 'category_id', 'description', 'price', 'logo', 'learn', 'is_enable', 'flow', 'is_pg'])->where("is_enable", 1)->whereIn('category_id', $delimiter);
        }])->get();

        // $product = Product::select(['id', 'name', 'category_id', 'description', 'price', 'logo', 'learn', 'is_enable', 'flow', 'is_pg'])->whereIn('category_id', $delimiter)->get();
        $products = [];
        foreach ($getproducts as $key => $value) {
            if (!empty($value->product)) {
                $products[$key] = $value->product;
                if ($value->product->flow == "web") {
                    $dname = strtoupper($value->product->name);
                    if (Str::contains($dname, ['TRAVEL', 'TRAVELLING', 'TRAVELLIN', 'ZTI'])) {
                        $products[$key]['link'] = route('travel.index');
                    } elseif (Str::contains($dname, ['PA MUDIK', 'MUDIK', "PERJALANAN", "PA PERJALANAN", "PERSONAL", "ACCIDENT"])) {
                        $products[$key]['link'] = route('perjalanan.index');
                    }
                } else {
                    $products[$key]['link'] = "";
                }
            }
        }
        return response([
            "status" => true,
            "data" => array_values($products),
        ], 200);
    }

    public function recommendation()
    {
        $user = auth('api')->user();
        // return $user;
        if (empty($user)) {
            $user = User::where('email', env('DEFAULT_EMAIL_PARTNER'))->first();
        }

        $prod = [];
        $products = PartnerProduct::where("partner_id", $user->partner_id)->with(["product" => function ($query) {
            $query->select(['id', 'name', 'category_id', 'description', 'price', 'logo', 'learn', 'is_enable', 'flow', 'is_pg'])->where("is_enable", 1)->limit(7)->inRandomOrder();
        }])->get();

        foreach ($products as $key => $product) {
            if (!empty($product->product)) {
                $prod[$key] = $product->product;
                $prod[$key]['display_name'] = $product->product->name;
                if ($product->product->flow == "web") {
                    $dname = strtoupper($product->product->name);
                    if (Str::contains($dname, ['TRAVEL', 'TRAVELLING', 'TRAVELLIN', 'ZTI'])) {
                        $prod[$key]['link'] = route('travel.index');
                    } elseif (Str::contains($dname, ['PA MUDIK', 'MUDIK', "PERJALANAN", "PA PERJALANAN", "PERSONAL", "ACCIDENT"])) {
                        $prod[$key]['link'] = route('perjalanan.index');
                    }
                } else {
                    $prod[$key]['link'] = "";
                }
                $prod[$key]['logo'] = asset('uploads/product/' . $product->product->logo);
            }
        }

        // $product = Product::select(['id', 'name', 'category_id', 'description', 'price', 'logo', 'learn', 'is_enable', 'flow', 'is_pg'])
        //     ->limit(7)->inRandomOrder()->get();

        return response([
            "status" => true,
            "data" => array_values($prod),
        ], 200);
    }

    private function getFormRepo($category_id)
    {
        $cont = FormRepoCategory::where('category_id', $category_id)->first();
        $form = [];

        if (empty($cont->form_json)) {
            return response()->json(['data' => [], 'message' => "Data is empty", 'status' => true]);
        }
        $contract = $cont->form_json['contract'];

        $formrepo = FormRepo::whereIn('id', $contract)->orderByRaw('FIELD(id,' . implode(", ", $contract) . ')')->get();
        if (!empty($cont->form_validation)) {
            foreach ($cont->form_validation['contract'] as $key_valid => $form_validation) {
                $valid[$form_validation['contract']] = $form_validation;
            }
        }
        foreach ($formrepo as $key => $repo) {
            if ($repo->value != null) {
                $value = Arrays::find($repo->value)->value;
            }

            if ($repo->form_type == "text" || $repo->form_type == "number" || $repo->form_type == "images") {
                $validation = [
                    "is_required" => !empty($valid[$repo->id]['required']) ? true : false,
                    "min_length" => !empty($valid[$repo->id]['minlength']) ? (int) $valid[$repo->id]['minlength'] : NULL,
                    "max_length" => !empty($valid[$repo->id]['maxlength']) ? (int) $valid[$repo->id]['maxlength'] : NULL
                ];
            } else {
                $validation = ["is_required" => !empty($valid[$repo->id]['required']) ? true : false];
            }

            $form[] = [
                "id" => $repo->id,
                "type" => $repo->form_type,
                "text" => $repo->name,
                "validator" => $validation,
                "validate_link" => $repo->validate_link,
                "value" => $value ?? null
            ];
        }
        return $form;
    }
}
