<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
}
