<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\FormRepo;
use App\Models\FormRepoCategory;
use App\Models\PartnerProduct;
use App\Models\Arrays;

class CategoryController extends Controller
{
    public function list()
    {
        try {
            $data = Category::select('id', 'name', 'is_enable', 'image')->where('is_enable', true)->get();

            if (count($data) == 0) {
                return response([
                    "status" => false,
                    "message" => "Data not found"
                ], 404);
            }

            $category = [];
            foreach ($data as $keyCat => $cat) {
                $category[$keyCat] = $cat;
                $category[$keyCat]['image'] = isset($cat->image) ? asset('uploads/category/' . $cat->image) : null;
            }
            return response(['data' => $category, 'message' => "Data found", 'status' => true], 200);
        } catch (\Exception $e) {
            return response(['message' => 'Oops, something went wrong', 'errors' => $e->getMessage(), 'status' => false], 500);
        }
    }

    public function productCategory($id)
    {
        $user = auth('api')->user();
        $rel = PartnerProduct::where("partner_id", $user->partner_id)->with(['product' => function ($q) use ($id) {
            $q->where('category_id', $id);
        }])->get();

        $pro = [];
        $new = [];
        $cont = FormRepoCategory::where('category_id', $id)->first();
        if (empty($cont->form_json)) {
            return response()->json([
                'status' => false,
                'message' => "Data is empty"
            ]);
        }

        $contract = $cont->form_json['contract'];
        $formRepo = FormRepo::whereIn('id', $contract)->orderByRaw('FIELD(id,' . implode(", ", $contract) . ')')->get();

        if (!empty($cont->form_validation)) {
            foreach ($cont->form_validation['contract'] as $key_valid => $form_validation) {
                $valid[$form_validation['contract']] = $form_validation;
            }
        }

        foreach ($formRepo as $key => $repo) {
            if ($repo->value != null) {
                $values = Arrays::find($repo->value);
                $value = $values->value;
            } else {
                $value = null;
            }
            if ($repo->form_type == "text" || $repo->form_type == "number" || $repo->form_type == "images") {
                $validation = array(
                    "is_required" => !empty($valid[$repo->id]['required']) ? true : false,
                    "min_length" => !empty($valid[$repo->id]['minlength']) ? (int)$valid[$repo->id]['minlength'] : NULL,
                    "max_length" => !empty($valid[$repo->id]['maxlength']) ? (int)$valid[$repo->id]['maxlength'] : NULL
                );
            } else {
                $validation = array("is_required" => !empty($valid[$repo->id]['required']) ? true : false,);
            }
            $new[] = array("id" => $repo->id, "type" => $repo->form_type, "text" => $repo->name, "validator" => $validation, 'validate_link' => $repo->validate_link, "value" => $value);
        }
        foreach ($rel as $keys => $r) {
            if ($r->product) {
                $r->product["form"] = $new;
                $pro[] = $r->product;
            }
        }
        return response()->json(['data' => $pro, 'message' => count($pro) > 0 ? "Data found" : "Data is empty", 'status' => true]);
    }
}
