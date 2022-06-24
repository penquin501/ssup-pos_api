<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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

    public function addCartTemp(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $output = [];
        $validate = Cart::validateCartTemp($data);

        if ($validate['message'] !== true) {
            $output = ['message' => $validate['message']];
            return response()->json($output, 201);
        }

        $product = DB::table('master_products')
            ->where('brand_id', '=', $data['brand_id'])
            ->where('barcode', '=', $data['product_id'])
            ->orWhere('product_id', '=', $data['product_id'])
            ->get();
        if (count($product) <= 0) {
            $output = ['message' => ['product_not_found']];
            return response()->json($output, 201);
        }

        $empInfo = DB::table('users')
            ->where('emp_id', '=', $data['emp_id'])
            ->where('branch_id', '=', $data['branch_id'])
            ->get();

        if (count($empInfo) <= 0) {
            $output = ['message' => ['user_not_found']];
            return response()->json($output, 201);
        }

        // TODO: คำนวน ส่วนที่สมาชิกเป็น VIP ex. วงเงิน/ส่วนลดสุทธิ
        /**
         * ยิง api -ของ crm รับข้อมูล member
         * $crmIp = "192.168.0.113:8100/api";
         *    $body = [
         *       "member_code" => "0846533676",
         *       "brand_id" => "OP",
         *       "branch_id" => "7888",
         *       "type" => ""
         *   ];
         * $dataCrmMember = Http::timeout(30)->post("http://" . $crmIp . "/get/member/profile", $body);
         * $memberInfo = json_decode($dataCrmMember->body(), true);
         * ถ้าเป็น vip จะมี ส่วนลด และวงเงินจำกัด
         * ถ้าไม่ใช่ vip จะมี level เป็นตัวกำหนดส่วนลด แต่ไม่มีวงเงินจำกัด
         */
        $tax = $product[0]->tax_type == 'V' ? 0.07 : 0;

        $dataSaveBillMainTemp = [
            'date' => date('Y-m-d'),
            'customer_id' => $data['member_id'],
            'brand_id' => $data['brand_id'],
            'branch_id' => $data['branch_id'],
            'sales_type' => $data['bill_type']['doc_tp'],
            'sales_note' => $data['bill_type']['description'],
            'type' => $data['bill_type']['status_no'],
            'status' => "1",
            'cash' => 0,
            'change' => 0,
            'payment_type' => "",
            'point_receive' => 0,
            'point_use' => 0,
            'point_before' => 0,
            'point_after' => 0,
            'user_id' => $data['emp_id'],
            'user_name' => $empInfo[0]->emp_name . " " . $empInfo[0]->emp_surname,
            'saleman_id' => $data['emp_id'],
            'saleman_name' => $empInfo[0]->emp_name . " " . $empInfo[0]->emp_surname,
            'created_at' => date("Y-m-d H:i:s"),
        ];

        $dataSaveBillItemTemp = [
            'date' => date('Y-m-d'),
            'product_id' => $product[0]->product_id,
            'product_name' => $product[0]->name_product,
            'product_name_print' => $product[0]->name_print,
            'product_type' => $product[0]->type,
            'quantity' => $data['qty'],
            'unit' => $product[0]->unit,
            'price' => $product[0]->price,
            'product_taxs' => $product[0]->tax_type == 'V' ? 0.07 : 0,
            'user_id' => $data['emp_id'],
            'user_name' => $empInfo[0]->emp_name . " " . $empInfo[0]->emp_surname,
            'saleman_id' => $data['emp_id'],
            'saleman_name' => $empInfo[0]->emp_name . " " . $empInfo[0]->emp_surname,
            'created_at' => date("Y-m-d H:i:s"),
        ];

        $dataSaveBillItemPromotionTemp = [
            'brand_id' => $data['brand_id'],
            'branch_id' => $data['branch_id'],
            'created_at' => date("Y-m-d H:i:s"), //
        ];

        if ($data['invoice_no_temp'] == "-") {
            // new bill/only 1st item
            $invoiceTemp = $this->generateBillNoTemp($data['brand_id'], $data['branch_id'], $data['bill_type']['doc_tp'], $data['bill_type']['status_no']);

            $dataSaveBillItemTemp['invoice_no'] = $invoiceTemp;
            $dataSaveBillItemTemp['promotion_code'] = $data['member_id'] == "00" ? "" : $data['promotion_code']; //TODO: เช็คตาราง promotion
            $dataSaveBillItemTemp['point'] = $data['member_id'] == "00" ? "0" : $data['point']; // TODO:ส่วนลดตาม member level
            $dataSaveBillItemTemp['discount'] = $data['member_id'] == "00" ? "0" : $data['discount']; // TODO:ส่วนลดตาม member level
            $dataSaveBillItemTemp['total'] = intval($data['qty']) * $product[0]->price;
            $dataSaveBillItemTemp['taxs'] = $dataSaveBillItemTemp['total'] * $dataSaveBillItemTemp['product_taxs'];
            $dataSaveBillItemTemp['net'] = $dataSaveBillItemTemp['total'] - $dataSaveBillItemTemp['discount'];
            $dataSaveBillItemTemp['stock_before'] = $data['member_id'] == "00" ? "0" : $data['stock_before']; // TODO:ดึงข้อมูล stock ในร้าน
            $dataSaveBillItemTemp['stock_arter'] = $data['member_id'] == "00" ? "0" : $data['stock_arter']; // TODO: ลบจำนวน ออกจาก ข้อมูล stock ในร้าน

            $dataSaveBillItemPromotionTemp['invoice_no'] = $invoiceTemp;
            $dataSaveBillItemPromotionTemp['promotion_code'] = $data['member_id'] == "00" ? "" : $data['promotion_code']; // TODO: เช็คจาก promotion set ที่ user เลือก

            $dataSaveBillMainTemp['invoice_no'] = $invoiceTemp;
            $dataSaveBillMainTemp['total_tax'] = $dataSaveBillItemTemp['taxs'];
            $dataSaveBillMainTemp['total'] = $dataSaveBillItemTemp['total'];
            $dataSaveBillMainTemp['sub_total'] = $dataSaveBillItemTemp['total'] - $dataSaveBillMainTemp['total_tax'];
            $dataSaveBillMainTemp['discount'] = $dataSaveBillItemTemp['discount']; // TODO:ส่วนลดตาม member level
            $dataSaveBillMainTemp['total_discount'] = $dataSaveBillItemTemp['discount'];
            $dataSaveBillMainTemp['net'] = $dataSaveBillItemTemp['total'] - $dataSaveBillItemTemp['discount'];

            $resultSaveBillMainTemp = DB::table('bill_main_temp')->insert($dataSaveBillMainTemp);
            $resultSaveBillItemTemp = DB::table('bill_item_temp')->insert($dataSaveBillItemTemp);
            $resultSaveBillItemPromotionTemp = DB::table('bill_item_promotion_temp')->insert($dataSaveBillItemPromotionTemp);

            if ($resultSaveBillMainTemp && $resultSaveBillItemTemp && $resultSaveBillItemPromotionTemp) {
                $billMainTemp = DB::table('bill_main_temp')->where('invoice_no', '=', $invoiceTemp)->get();
                $billItemTemp = DB::table('bill_item_temp')->where('invoice_no', '=', $invoiceTemp)->get();
                $billItemPromotionTemp = DB::table('bill_item_promotion_temp')->where('invoice_no', '=', $invoiceTemp)->get();
                $output = [
                    'message' => 'success',
                    'invoice_no_temp' => $invoiceTemp,
                    'main_temp' => $billMainTemp,
                    'item_temp' => $billItemTemp,
                    'item_promotion_temp' => $billItemPromotionTemp,
                ];
                return response()->json($output, 200);
            }
        } else {
            // existed bill

            $listBillItemTemp = DB::table('bill_item_temp')
                ->where('invoice_no', '=', $data['invoice_no_temp'])
                ->where('product_id', '=', $product[0]->product_id)
                ->get();

            if (count($listBillItemTemp) > 0) {
                foreach ($listBillItemTemp as $item) {
                    if ($item->product_id == $product[0]->product_id) {
                        $item->quantity += $data['qty'];
                        $item->discount += $data['member_id'] == "00" ? "0" : $data['discount']; // TODO:ส่วนลดตาม member level
                        $item->total = $item->quantity * $product[0]->price;
                        $item->taxs = $item->total * $item->product_taxs;
                        $item->net = $item->total * $item->discount;
                        $dataSaveBillItemTemp = (array)$item;

                        $resultUpdateBillItemTemp = DB::table('bill_item_temp')
                            ->where('id', '=', $item->id)
                            ->delete();
                    }
                }
            }

            $dataSaveBillItemTemp['invoice_no'] = $data['invoice_no_temp'];
            $dataSaveBillItemTemp['promotion_code'] = $data['member_id'] == "00" ? "" : $data['promotion_code']; //TODO: เช็คตาราง promotion
            $dataSaveBillItemTemp['point'] = $data['member_id'] == "00" ? "0" : $data['point']; // TODO:ส่วนลดตาม member level
            $dataSaveBillItemTemp['discount'] = $data['member_id'] == "00" ? "0" : $data['discount']; // TODO:ส่วนลดตาม member level
            $dataSaveBillItemTemp['total'] = $dataSaveBillItemTemp['quantity'] !== 0 ? intval($dataSaveBillItemTemp['quantity']) * $product[0]->price : intval($data['qty']) * $product[0]->price;
            $dataSaveBillItemTemp['taxs'] = $dataSaveBillItemTemp['total'] * $dataSaveBillItemTemp['product_taxs'];
            $dataSaveBillItemTemp['net'] = $dataSaveBillItemTemp['total'] - $dataSaveBillItemTemp['discount'];
            $dataSaveBillItemTemp['stock_before'] = $data['member_id'] == "00" ? "0" : $data['stock_before']; // TODO:ดึงข้อมูล stock ในร้าน
            $dataSaveBillItemTemp['stock_arter'] = $data['member_id'] == "00" ? "0" : $data['stock_arter']; // TODO: ลบจำนวน ออกจาก ข้อมูล stock ในร้าน

            $resultSaveBillItemTemp = DB::table('bill_item_temp')->insert($dataSaveBillItemTemp);

            $data['promotion_code'] = $data['member_id'] == "00" ? "" : $data['promotion_code']; //TODO: เช็คตาราง promotion
            $listBillItemPromotionTemp = DB::table('bill_item_promotion_temp')
                ->where('invoice_no', '=', $data['invoice_no_temp'])
                ->where('promotion_code', '=', $data['promotion_code'])
                ->get();
            if (count($listBillItemPromotionTemp) > 0) {
                foreach ($listBillItemPromotionTemp as $item) {
                    $dataSaveBillItemPromotionTemp = (array)$item;

                    $resultUpdateBillItemTemp = DB::table('bill_item_promotion_temp')
                        ->where('id', '=', $item->id)
                        ->delete();
                }
            }

            $dataSaveBillItemPromotionTemp['promotion_code'] = $data['member_id'] == "00" ? "" : $data['promotion_code'];
            $resultSaveBillItemPromotionTemp = DB::table('bill_item_promotion_temp')->insert($dataSaveBillItemPromotionTemp);

            $dataItemTemp = DB::table('bill_item_temp')->where('invoice_no', '=', $data['invoice_no_temp'])->get();

            $sumTotalTaxs = 0;
            $sumTotal = 0;
            $sumTotalDiscount = 0;

            foreach ($dataItemTemp as $item) {
                $sumTotalTaxs += $item->taxs;
                $sumTotal += $item->total;
                $sumTotalDiscount += $item->discount;
            }

            $dataUpdateBillMainTemp = [
                'total_tax' => $sumTotalTaxs,
                'total' => $sumTotal,
                'total_discount' => $sumTotalDiscount,
            ];

            $dataUpdateBillMainTemp['sub_total'] = $sumTotal - $sumTotalTaxs;
            $dataUpdateBillMainTemp['net'] = $sumTotal - $sumTotalDiscount;

            $dataItemTemp = DB::table('bill_main_temp')->where('invoice_no', '=', $data['invoice_no_temp'])->update($dataUpdateBillMainTemp);

            $billMainTemp = DB::table('bill_main_temp')->where('invoice_no', '=', $data['invoice_no_temp'])->get();
            $billItemTemp = DB::table('bill_item_temp')->where('invoice_no', '=', $data['invoice_no_temp'])->get();
            $billItemPromotionTemp = DB::table('bill_item_promotion_temp')->where('invoice_no', '=', $data['invoice_no_temp'])->get();
            $output = [
                'message' => 'success',
                'invoice_no_temp' => $data['invoice_no_temp'],
                'main_temp' => $billMainTemp,
                'item_temp' => $billItemTemp,
                'item_promotion_temp' => $billItemPromotionTemp,
            ];
            return response()->json($output, 200);
        }
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

    function generateBillNoTemp($brand_id, $branch_id, $doc_tp, $status_no)
    {
        do {
            $random = rand(1, 9999999);

            $invoiceTemp = $brand_id . $branch_id . $doc_tp . "-" . $status_no . "-" . str_pad($random, 8, "0", STR_PAD_LEFT);
            $checkExistedInvoice = false;

            $bill_no = DB::table('com_reserved_bill_no')->where('invoice_temp', "=", $invoiceTemp)->get();
            if (count($bill_no) <= 0) {
                $result = DB::table('com_reserved_bill_no')->insert(['invoice_temp' => $invoiceTemp, 'recorded_at' => date("Y-m-d H:i:s")]);
                if ($result) {
                    $checkExistedInvoice = true;
                }
            }
        } while (!$checkExistedInvoice);
        return $invoiceTemp;
    }
}
