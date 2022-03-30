<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Member extends Model
{
    use HasFactory;

    protected $errors;

    public function validationContent($data)
    {
        $v = Validator::make($data, [
            'userInfo' => 'required',
            'member' => 'required',
        ]);

        if (count($v->errors()->messages()) !== 0) {
            return ['message' => $v->errors()->messages()];
        }

        return ['message' => true];
    }

    public function validationMember($data)
    {
        $v = Validator::make($data, [
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'firstNameEng' => 'required|string',
            'lastNameEng' => 'required|string',
            'cardId' => 'required|string|size:13',
            'phone' => 'required|string|size:10',
            'address' => 'required|string',
            'subDistrictId' => 'required|integer',
            'subDistrict' => 'required|string',
            'districtId' => 'required|integer',
            'district' => 'required|string',
            'provinceId' => 'required|integer',
            'province' => 'required|string',
            'zipcode' => 'required|string|size:5',
            'type' => 'required|string',
            'district' => 'required|string',
            'birthday' => 'required|string',
            'email' => 'required|email',
        ]);

        if (count($v->errors()->messages()) !== 0) {
            return ['message' => $v->errors()->messages()];
        }

        return ['message' => true];
    }

    public function validationUpdateMember($data)
    {
        $v = Validator::make($data, [
            'memberNo' => 'required|string',
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'firstNameEng' => 'required|string',
            'lastNameEng' => 'required|string',
            'cardId' => 'required|string|size:13',
            'phone' => 'required|string|size:10',
            'address' => 'required|string',
            'subDistrictId' => 'required|integer',
            'subDistrict' => 'required|string',
            'districtId' => 'required|integer',
            'district' => 'required|string',
            'provinceId' => 'required|integer',
            'province' => 'required|string',
            'zipcode' => 'required|string|size:5',
            'type' => 'required|string',
            'district' => 'required|string',
            'birthday' => 'required|string',
            'email' => 'required|email',
        ]);

        if (count($v->errors()->messages()) !== 0) {
            return ['message' => $v->errors()->messages()];
        }

        return ['message' => true];
    }
}
