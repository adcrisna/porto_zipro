<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bank;

class BankController extends Controller
{
    public function list()
    {
        try {
            $data = Bank::all();
            return response()->json(['data' => $data,'message' => count($data) > 0 ? "Data found" : "Data is empty", 'status' => true], 200);

        } catch (\Exception $e) {
            return response(['message' => 'Oops, something went wrong', 'errors' => $e->getMessage(), 'status' => false], 500);
        }
    }
}
