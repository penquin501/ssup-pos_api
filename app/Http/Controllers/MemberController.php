<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Mockery\Undefined;

class MemberController extends Controller
{
    public function memberInfo(Request $request)
    {
        if (count($request->only(['member_no'])) == 0 || $request->input('member_no') == null) {
            $output = ['message' => 'error_invalid_data'];
            return response()->json($output, 201);
        } else {
            $member = DB::table('com_member')->where('member_no', '=', $request->input('member_no'))->get();
            if (count($member) == 0) {
                //TODO: เช็คที่ crm ด้วย
                $output = ['message' => 'error_no_data'];
                return response()->json($output, 201);
            } else {

                $output = [
                    'message' => 'success',
                    "member" => $member,
                ];
                return response()->json($output, 200);
            }
        }
    }

    public function listMember(Request $request)
    {
        $member = DB::table('com_member')->get(); //local only
        if (count($member) == 0) {
            $output = ['message' => 'error_no_data'];
            return response()->json($output, 201);
        } else {
            $output = [
                "member" => $member,
            ];
            return response()->json($output, 200);
        }
    }

    public function register(Request $request)
    {
        $output = [];
        $data = json_decode($request->getContent(), true);

        $validateContent = Member::validationContent($data);

        if ($validateContent['message'] !== true) {
            $output = ['message' => $validateContent['message']];
            return response()->json($output, 201);
        }

        $validateUser = User::validationUser($data['userInfo']);

        if ($validateUser['message'] !== true) {
            $output = ['message' => $validateUser['message']];
            return response()->json($output, 201);
        }

        $validateMember = Member::validationMember($data['member']);

        if ($validateMember['message'] !== true) {
            $output = ['message' => $validateMember['message']];
            return response()->json($output, 201);
        }
        dd('ok');
        // $ip = '10.100.53.2';
        //     $data = Http::timeout(30)->get("http://" . $ip . "/stock/file/check_product_master_g2.php"); //TODO:เขียน api เป็น Laravel ที่ 192.168.2.248
        //     if (!$data->successful()) {
        //         $output = ['message' => 'error_cannot_connect_product_master'];
        //         return response()->json($output, $data->getStatusCode());
        // }
        // $memberNo = $this->genMemberNo('');
        $prepareData = [
            'id' => 3,
            'transfer_date' => $request->input('member_no'),
            'customer_id' => 3,
            'member_no' => 3,
            'mobile_no' => 3,
            'h_tel_no' => 3,
            'o_tel_no' => 3,
            'email' => 3,
            'name' => 3,
            'name_en' => 3,
            'surname' => 3,
            'surname_en' => 3,
            'sex' => 3,
            'address' => 3,
            'road' => 3,
            'id_card' => 3,
            'shop' => 3,
            'area' => 3,
            'region_id' => 3,
            'province_id' => 3,
            'district_id' => 3,
            'subdistrict_id' => 3,
            'province_name' => 3,
            'district' => 3,
            'sub_district' => 3,
            'zip' => 3,
            'amt' => 0,
            'net' => 0,
            'qty' => 0,
            'point' => 0,
            'discount' => 0,
            'cust_day' => 0,
            'apply_date' => date("Y-m-d"),
            'expire_date' => date("Y-m-d"),
            'birthday' => date("Y-m-d"),
            'mem_status' => 3,
            'first_discount' => 3,
            'card_level' => 3,
            'special_day' => 3,
            'status' => 3,
            'update_date' => 3,
            'update_time' => 3,
            'update_api' => 3,
        ];
        // try {
        //     // $saveProduct = DB::table('product_master')->insert($prepareData);
        //     // $result = DB::table('member_data')->insert($prepareData);
        //     // if (!$result) {
        //     //     $error[] = $result;
        //     // }

        //     // if ($result == false) {
        //     //     $output = [
        //     //         'message' => 'cannot_save_member'
        //     //     ];
        //     //     return response()->json($output, 201);
        //     // } else {
        //     //     $output = [
        //     //         'message' => 'success'
        //     //     ];
        //     //     return response()->json($output, 200);
        //     // }
        //     // $result[] = $product['barcode'];
        // } catch (\Throwable $th) {
        //     $output = ['message' => $th->getMessage()];
        //     return response()->json($output, 500);
        // }
        // $result = DB::table('member_data')->insert($data);

    }

    public function editMemberData(Request $request)
    {
        $output = [];
        $data = json_decode($request->getContent(), true);

        $validateContent = Member::validationContent($data);

        if ($validateContent['message'] !== true) {
            $output = ['message' => $validateContent['message']];
            return response()->json($output, 201);
        }

        $validateUser = User::validationUser($data['userInfo']);

        if ($validateUser['message'] !== true) {
            $output = ['message' => $validateUser['message']];
            return response()->json($output, 201);
        }

        $validateMember = Member::validationUpdateMember($data['member']);

        if ($validateMember['message'] !== true) {
            $output = ['message' => $validateMember['message']];
            return response()->json($output, 201);
        }

        return response()->json($output, 200);
    }

    public function validatePID($pid)
    {
        if (preg_match("/^(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)(\d)$/", $pid, $matches)) { //ใช้ preg_match
            if (strlen($pid) != 13) {
                $returncheck = false;
            } else {
                $rev = strrev($pid); // reverse string ขั้นที่ 0 เตรียมตัว
                $total = 0;
                for ($i = 1; $i < 13; $i++) { // ขั้นตอนที่ 1 - เอาเลข 12 หลักมา เขียนแยกหลักกันก่อน
                    $mul = $i + 1;
                    $count = $rev[$i] * $mul; // ขั้นตอนที่ 2 - เอาเลข 12 หลักนั้นมา คูณเข้ากับเลขประจำหลักของมัน
                    $total = $total + $count; // ขั้นตอนที่ 3 - เอาผลคูณทั้ง 12 ตัวมา บวกกันทั้งหมด
                }
                $mod = $total % 11; //ขั้นตอนที่ 4 - เอาเลขที่ได้จากขั้นตอนที่ 3 มา mod 11 (หารเอาเศษ)
                $sub = 11 - $mod; //ขั้นตอนที่ 5 - เอา 11 ตั้ง ลบออกด้วย เลขที่ได้จากขั้นตอนที่ 4
                $check_digit = $sub % 10; //ถ้าเกิด ลบแล้วได้ออกมาเป็นเลข 2 หลัก ให้เอาเลขในหลักหน่วยมาเป็น Check Digit
                if ($rev[0] == $check_digit) {  // ตรวจสอบ ค่าที่ได้ กับ เลขตัวสุดท้ายของ บัตรประจำตัวประชาชน
                    $returncheck = true; /// ถ้า ตรงกัน แสดงว่าถูก
                } else {
                    $returncheck = false; // ไม่ตรงกันแสดงว่าผิด
                }
            }
        } else {
            $returncheck = false;
        }
        return $returncheck;
    }
}
