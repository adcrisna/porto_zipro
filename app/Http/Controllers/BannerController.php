<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banners;

class BannerController extends Controller
{
    public function list(Request $request)
    {
        try {
            $user = auth('api')->user();
            if (!empty($user)) {
                $user->update(["platform" => $request->header('platform')]);
            }
            $banners = Banners::where('isActive', 1)->get();
            foreach ($banners as $key => $banner) {
                if ($banner->is_internal == 1) {
                    $banner->link = route('banner.page', $banner->page_name);
                }

                $banner->image = asset('uploads/banner/' . $banner->image);

                if ($banner->is_header == true && $banner->isActive == true) {
                    $data['header'] = $banner->image;
                } else {
                    $data['carousel'][] = $banner;
                }
            }
            $data["no_cs_salvus"] = env('NOMOR_CS_SALVUS');
            return response()->json(['data' => $data, 'message' => count($banners) > 0 ? "Data found" : "Data is empty", 'status' => true], 200);
        } catch (\Exception $e) {
            return response(['message' => 'Oops, something went wrong', 'errors' => $e->getMessage(), 'status' => false], 500);
        }
    }

    public function detail($slug)
    {
        $data = Banners::where('page_name', $slug)->first();
        if (isset($data)) {
            return view('banner', compact('data'));
        }
        abort(404);
    }
}
