<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class ConfAccessSystem extends Model
{
    use HasFactory;

    protected $table = 'conf_access_system';

    public function validateCheckAccess($data)
    {
        $v = Validator::make($data, [
            'ip_address' => 'required|string',
            'path' => 'required|string',
            'emp_id' => 'required|string'
        ]);

        if (count($v->errors()->messages()) !== 0) {
            return ['message' => $v->errors()->messages()];
        }

        return ['message' => true];
    }

    // public function validateSaveAccess($data)
    // {
    //     $v = Validator::make($data, [
    //         'branch_id' => 'required|string',
    //         'emp_id' => 'required|string',
    //         'module' => 'required|string',
    //         'module_type' => 'required|string'
    //     ]);

    //     if (count($v->errors()->messages()) !== 0) {
    //         return ['message' => $v->errors()->messages()];
    //     }

    //     return ['message' => true];
    // }
}
