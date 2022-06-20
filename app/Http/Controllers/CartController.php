<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function getInvoiceNo(Request $request)
    {
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

    public function listBillType(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $validate = Cart::validateCheckListBillType($data);

        if ($validate['message'] !== true) {
            $output = ['message' => $validate['message']];
            return response()->json($output, 201);
        }

        $list = DB::table('com_doc_status')->where('brand_id', '=', $data['brand_id'])->where('status_no', '=', $data['status_no'])->get(); //local only
        if (count($list) == 0) {
            $output = ['message' => 'error_no_data'];
            return response()->json($output, 201);
        } else {
            $output = [
                "listBillType" => $list,
            ];
            return response()->json($output, 200);
        }
    }

    public function listCreditType(Request $request)
    {
        $list = DB::table('com_paid')->get(); //local only
        if (count($list) == 0) {
            $output = ['message' => 'error_no_data'];
            return response()->json($output, 201);
        } else {
            $output = [
                "listCreditType" => $list,
            ];
            return response()->json($output, 200);
        }
    }
}
