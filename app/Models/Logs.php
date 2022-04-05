<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class Logs extends Model
{
    use HasFactory;
    protected $table = 'log_system';


    public function validate($data)
    {
        $v = Validator::make($data, [
            'branch_id' => 'required|string',
            'emp_id' => 'required|string',
            'module' => 'required|string',
            'module_type' => 'required|string'
        ]);

        if (count($v->errors()->messages()) !== 0) {
            return ['message' => $v->errors()->messages()];
        }

        return ['message' => true];
    }

    public function saveLogs($data)
    {
        $data['recorded_at'] = date("Y-m-d H:i:s");

        $result = DB::table('log_system')->insert($data);

        if (!$result) {
            return ['message' => 'cannot_save_logs'];
        } else {
            return ['message' => true];
        }
    }
}
