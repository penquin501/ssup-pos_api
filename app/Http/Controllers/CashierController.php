<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    // public function listCreditType(Request $request)
    // {
    //     $list = DB::table('com_paid')->get(); //local only
    //     if (count($list) == 0) {
    //         $output = ['message' => 'error_no_data'];
    //         return response()->json($output, 201);
    //     } else {
    //         $output = [
    //             "list" => $list,
    //         ];
    //         return response()->json($output, 200);
    //     }
    // }
}
