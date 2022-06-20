<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Cart extends Model
{
    use HasFactory;

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
