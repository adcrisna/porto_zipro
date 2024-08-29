<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bundle;
use App\Models\Product;
use DB;

class BundleController extends Controller
{
    public function index()
    {
        try {
            $user = auth('api')->user();
            $bundles = Bundle::where('user_id', $user->id)->get();
            if(count($bundles) == 0) {
                return response([
                    "status" => true,
                    "message" => "Empty data",
                    "data" => []
                ], 404);
            }
            $data = [];
            foreach ($bundles as $key => $bundle) {
                $data[$key]['id'] = $bundle->id;
                $data[$key]['user_id'] = $bundle->user_id;
                $data[$key]['name'] = $bundle->name;
                $product = Product::select('id','category_id','logo','name','display_name')->whereIn('id', $bundle->product_id)->get();
                $data[$key]['products'] = $product;
            }
            return response([
                "status" => true,
                "message" => "Data found",
                "data" => $data
            ], 200);
        } catch (\Exception $e) {
            return redirect([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $json = json_decode($request->getContent(), true);

            DB::beginTransaction();
            $bundle = new Bundle;
            $bundle->name = $json['name'];
            $bundle->product_id = $json['product_id'];
            $bundle->user_id = auth('api')->user()->id;
            $bundle->save();
            DB::commit();

            return response([
                "status" => true,
                "message" => "Bundle successfuly created",
                "data" => $bundle
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $json = json_decode($request->getContent(), true);

            DB::beginTransaction();
            $bundle = Bundle::find($id);
            
            if(!$bundle) {
                return response([
                    "status" => false,
                    "message" => "Bundle not found",
                    "data" => []
                ], 404);
            }
            
            $key = 0;
            foreach ($bundle->product_id as $prod_id) {
                $product_id[$key] = $prod_id;
                $key++;
            }
            $product_id[] = $json['product_id'];


            $bundle->product_id = $product_id;
            $bundle->save();
            DB::commit();

            return response([
                "status" => true,
                "message" => "Bundle successfuly updated",
                "data" => $bundle
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        } 
    }

    public function destroy(Request $request, $id)
    {
        try {
            $bundle = Bundle::find($id)->delete();
            return response([
                "status" => true,
                "message" => "Bundle successfuly destroyed",
            ], 200);
        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
