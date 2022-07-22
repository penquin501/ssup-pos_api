<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class Cart extends Model
{
    use HasFactory;

    public function validateCartTemp($data)
    {
        $v = Validator::make($data, [
            'product_id' => 'required|string',
            'qty' => 'required|integer|min:1',
            'bill_type' => 'required',
            'bill_type.doc_tp' => 'required|string',
            'bill_type.status_no' => 'required|string',
            'bill_type.description' => 'required|string',
            'branch_id' => 'required|string',
            'brand_id' => 'required|string',
            'member_id' => 'required|string',
            'member_name' => 'required|string',
            'member_level' => 'required|string',
            'invoice_no_temp' => 'required|string',
            'emp_id' => 'required|string',
        ]);

        if (count($v->errors()->messages()) !== 0) {
            return ['message' => $v->errors()->messages()];
        }

        return ['message' => true];
    }

    public function validateDelCartTemp($data)
    {
        $v = Validator::make($data, [
            'selectedItem' => 'required|array',
            'bill_type' => 'required',
            'bill_type.doc_tp' => 'required|string',
            'bill_type.status_no' => 'required|string',
            'bill_type.description' => 'required|string',
            'branch_id' => 'required|string',
            'brand_id' => 'required|string',
            'member_id' => 'required|string',
            'member_name' => 'required|string',
            'member_level' => 'required|string',
            'invoice_no_temp' => 'required|string',
            'emp_id' => 'required|string',
        ]);

        if (count($v->errors()->messages()) !== 0) {
            return ['message' => $v->errors()->messages()];
        }

        return ['message' => true];
    }

    public function validateCheckListBillType($data)
    {
        $v = Validator::make($data, [
            'brand_id' => 'required|string',
            'status_no' => 'required|string',
        ]);

        if (count($v->errors()->messages()) !== 0) {
            return ['message' => $v->errors()->messages()];
        }

        return ['message' => true];
    }

    public function validateCheckListFreeBag($data)
    {
        $v = Validator::make($data, [
            'brand_id' => 'required|string',
        ]);

        if (count($v->errors()->messages()) !== 0) {
            return ['message' => $v->errors()->messages()];
        }

        return ['message' => true];
    }

    public function validateCheckBillMain($data)
    {
        $v = Validator::make($data, [
            'invoice_no_temp' => 'required|string',
            'memberInfo' => 'required|array',
            'paymentInfo' => 'required|array',
            'selectedBag' => 'required|array',
        ]);

        if (count($v->errors()->messages()) !== 0) {
            return ['message' => $v->errors()->messages()];
        }

        if ($data['paymentInfo']['sumOnTop'] !== 0 && count($data['promotionOnTopInfo']) <= 0) {
            $output = ['message' => ['error_no_data_promotion_on_top']];
            return response()->json($output, 201);
        } else {
            $err = [];
            foreach ($data['promotionOnTopInfo'] as $item) {
                $vPromotionOnTopInfo = Validator::make($item, [
                    'promotion_name' => 'required|string',
                    'promotion_code' => 'required|string',
                    'value' => 'required'
                ]);
                if (count($vPromotionOnTopInfo->errors()->messages()) !== 0) {
                    $err[] = $vPromotionOnTopInfo->errors()->messages();
                }
            }
            if (count($err) !== 0) {
                return ['message' => $err];
            }
        }

        if ($data['paymentInfo']['sumVoucher'] !== 0 && count($data['listCoupon']) <= 0) {
            $output = ['message' => ['error_no_data_coupon']];
            return response()->json($output, 201);
        } else {
            $err = [];
            foreach ($data['listCoupon'] as $item) {
                $vlistCoupon = Validator::make($item, [
                    'couponCode' => 'required|string',
                    'value' => 'required|string'
                ]);
                if (count($vlistCoupon->errors()->messages()) !== 0) {
                    $err[] = $vlistCoupon->errors()->messages();
                }
            }
            if (count($err) !== 0) {
                return ['message' => $err];
            }
        }

        if ($data['paymentInfo']['credit'] !== 0 && !isset($data['creditCardInfo'])) {
            // $output = ['message' => ['error_no_data_credit_info']];
            // return response()->json($output, 201);
            // } else {
            $vCreditCardInfo = Validator::make($data['creditCardInfo'], [
                'creditCardType' => 'required|string',
                'creditCardNo' => 'required|string',
                'creditCardValue' => 'required|string',
                'selectedCard' => 'required|array',
                'selectedCard.brand_id' => 'required|string',
                'selectedCard.paid_tp' => 'required|string',
                'selectedCard.paid' => 'required|string',
                'selectedCard.description' => 'required|string',
            ]);
            if (count($vCreditCardInfo->errors()->messages()) !== 0) {
                return ['message' => $vCreditCardInfo->errors()->messages()];
            }
        }

        $errBag = [];
        foreach ($data['selectedBag'] as $item) {
            $vSelectedBag = Validator::make($item, [
                'brand_id' => 'required|string',
                'promo_code' => 'required|string',
                'product_id' => 'required|string',
                'product_desc' => 'required|string',
                'numBag' => 'required|string',
            ]);
            if (count($vSelectedBag->errors()->messages()) !== 0) {
                $errBag[] = $vSelectedBag->errors()->messages();
            }
        }
        if (count($errBag) !== 0) {
            return ['message' => $errBag];
        }

        $billMainTemp = DB::table('bill_main_temp')->where('invoice_no', '=', $data['invoice_no_temp'])->get();
        if (count($billMainTemp) <= 0) {
            return ['message' => ["bill_main_temp" => "error_no_data_in_invoice_temp"]];
        }
        $sumPaymentInfo = $data['paymentInfo']['sumOnTop'] + $data['paymentInfo']['sumVoucher'] + $data['paymentInfo']['cash'] + $data['paymentInfo']['credit'];

        if ($sumPaymentInfo > $billMainTemp[0]->net && $data['paymentInfo']['change'] == 0) {
            return ['message' => ["paymentInfo change" => "error_no_data_payment_change"]];
        }
        if ($sumPaymentInfo < $billMainTemp[0]->net) {
            return ['message' => ["paymentInfo sum" => "error_total_less_than_net"]];
        }

        return ['message' => true];
    }
}
