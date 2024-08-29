<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inbox;
use DB;

class InboxController extends Controller
{
    public function list()
    {
        $inbox = Inbox::all();
        return $inbox;
    }

    public function getinboxuser(Request $request)
    {
        $user_id = auth('api')->user()->id;
        $inboxes = Inbox::select('to_user_id','message','created_at')->where('to_user_id', $user_id)->orWhere('to_user_id', null)->latest()->get();
        if(empty($inboxes)) {
            return response(["message" => "Data not found!", "status" => 404], 404);
        }
        $format = [];
        foreach($inboxes as $key => $inbox) {
            if($inbox->to_user_id == null) {
                $inbox->to_user_id = $user_id;
            }
            $format[$key] = $inbox;
        }
        if(empty($format)) {
            return response(["message" => "Data Empty!","data" => []], 404);
        }
        return response(["message" => "Data found!","data" => $format], 200);
    }

    public function create(Request $request)
    {
        try {
            $user_id = auth('api')->user()->id;
            $to_userid = isset($request->to_userid) ? $request->to_userid : null;
            DB::beginTransaction();
            $inbox = Inbox::create([
                "from_user_id" => $user_id,
                "to_user_id" => $to_userid,
                "message" => $request->message 
            ]);
            DB::commit();
            if($inbox) {
                return response(['message' => 'Messages broadcasted!', 'status' => 200], 200);
            }

        } catch (\Exception $e) {
            return response(['message' => $e->getMessage(), 'status' => 500], 500);
        }
    }
}
