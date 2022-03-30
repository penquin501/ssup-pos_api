<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function getProvinces(Request $request)
    {
        $provinces = DB::table('com_provinces_th')->get();
        if (!$provinces) {
            abort(500);
        } else {
            $output = [
                "province" => $provinces,
            ];
            return response()->json($output, 200);
        }
    }

    public function getDistricts(Request $request)
    {
        $districts = DB::table('com_amphur_th')->where('province_id', '=', $request->input('id'))->get();

        if (!$districts) {
            abort(500);
        } else {
            $output = [
                "districts" => $districts,
            ];
            return response()->json($output, 200);
        }
    }

    public function getSubDistricts(Request $request)
    {
        $subDistricts = DB::table('com_tambon_th')->where('district_id', '=', $request->input('id'))->get();

        if (!$subDistricts) {
            abort(500);
        } else {
            $output = [
                "sub_districts" => $subDistricts,
            ];
            return response()->json($output, 200);
        }
    }
}
