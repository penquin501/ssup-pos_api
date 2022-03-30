<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function promotionInfo(Request $request)
    {
        // Http
        // if (count($request->only(['member_no'])) == 0 || $request->input('member_no') == null) {
        //     $output = ['message' => 'error_invalid_data'];
        //     return response()->json($output, 201);
        // } else {
        //     $member = DB::table('member_data')->where('member_no', '=', $request->input('member_no'))->get();
        //     if (count($member) == 0) {
        //         //TODO: เช็คที่ crm ด้วย
        //         $output = ['message' => 'error_no_data'];
        //         return response()->json($output, 201);
        //     } else {

        //         $output = [
        //             'message' => 'success',
        //             "member" => $member,
        //         ];
        //         return response()->json($output, 200);
        //     }
        // }
    }

    public function listPromotion(Request $request)
    {
        // $member = DB::table('member_data')->get(); //local only
        // if (count($member) == 0) {
        //     $output = ['message' => 'error_no_data'];
        //     return response()->json($output, 201);
        // } else {
        //     $output = [
        //         "member" => $member,
        //     ];
        //     return response()->json($output, 200);
        // }
    }

    public function getAllPromotionMaster(Request $request)
    {
        // $member = DB::table('member_data')->get(); //local only
        // if (count($member) == 0) {
        //     $output = ['message' => 'error_no_data'];
        //     return response()->json($output, 201);
        // } else {
        //     $output = [
        //         "member" => $member,
        //     ];
        //     return response()->json($output, 200);
        // }
    }

    public function syncPromotionMaster(Request $request)
    {
        // $member = DB::table('member_data')->get(); //local only
        // if (count($member) == 0) {
        //     $output = ['message' => 'error_no_data'];
        //     return response()->json($output, 201);
        // } else {
        //     $output = [
        //         "member" => $member,
        //     ];
        //     return response()->json($output, 200);
        // }
    }
}
