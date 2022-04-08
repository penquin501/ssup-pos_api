<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $output = [];
        $data = json_decode($request->getContent(), true);
        if (!isset($data['type'])) {
            $output = ['message' => [
                "type" => ["The type is missing"]
            ]];
            return response()->json($output, 201);
        } else {
            if ($data['type'] == "LOCK_KEYIN_LOGIN") {
                $validate = User::validationLogin($data);
                if ($validate['message'] !== true) {
                    $output = ['message' => $validate['message']];
                    return response()->json($output, 201);
                }

                $credentials = $request->validate([
                    'username' => 'required|string',
                    'password' => 'required|string',
                ]);

                if (!Auth::attempt($credentials)) {
                    $output = ['message' => [
                        "username" => ["not found username"]
                    ]];
                    return response()->json($output, 201);
                } else {
                    $user = json_decode(Auth::user(), true);

                    Auth::user()->tokens()->where('name', $data['username'])->delete();
                    $token = Auth::user()->createToken($data['username']);

                    $userRole = Auth::user()->roles == null ? "default" : Auth::user()->roles;
                    $user['emp_id'] = $userRole == "default" ? "1" : $user['emp_id'];

                    $roles = DB::table('roles')
                        ->where('role_name', '=', $userRole)
                        ->where('emp_id', '=', $user['emp_id'])
                        ->where('brand_id', '=', $user['brand_id'])
                        ->where('status', '1')
                        ->get();

                    $brand = DB::table('master_brand')->where('brand_id', '=', $user['brand_id'])->get();
                    $branch = DB::table('master_branch')->where('branch_id', '=', $user['branch_id'])->get();
                    $position = DB::table('master_position')->where('emp_pos_id', '=', $user['position_id'])->get();
                    $group = DB::table('conf_user_group')->where('group_id', '=', $user['group_id'])->get();
                    $doc_date = DB::table('com_doc_date')
                        ->where('brand_id', '=', $user['brand_id'])
                        ->where('branch_id', '=', $user['branch_id'])
                        ->first();

                    $saveLogData = [
                        "branch_id" => $user['branch_id'],
                        "emp_id" => $user['emp_id'],
                        "module" => "LOGIN_POS",
                        "module_type" => "LOGIN",
                    ];
                    $saveLogs = Logs::saveLogs($saveLogData);
                    if ($saveLogs['message'] !== true) {
                        $output = ['message' => $validate['message']];
                        return response()->json($output, 201);
                    }

                    $output = [
                        "message" => "success",
                        "doc_date" => $doc_date->doc_date,
                        "data" =>  [
                            "id" => $user['id'],
                            "username" => $user['username'],
                            "roles" => $user['roles'],
                            // "fing_path" => $user['fing_path'],
                            // "numoffice_d" => $user['numoffice_d'],
                            "emp_id" => $user['emp_id'],
                            "emp_prefix_id" => $user['emp_prefix_id'],
                            "emp_name" => $user['emp_name'],
                            "emp_surname" => $user['emp_surname'],
                            "emp_name_e" => $user['emp_name_e'],
                            "emp_surname_e" => $user['emp_surname_e'],
                            "img_profile" => $user['img_profile'],
                            // "card_id" => $user['card_id'],
                            "position_id" => $user['position_id'],
                            "position" => $position[0]->emp_pos_name,
                            "group_id" => $user['group_id'],
                            "group" => $group[0]->group_id,
                            "brand_id" => $user['brand_id'],
                            "brand" => $brand[0]->name_th,
                            "brand_print" => $brand[0]->name_print,
                            "brand_logo" => $brand[0]->logo,
                            "brand_tax_id" => $brand[0]->tax_id,
                            "brand_theme_code" => $brand[0]->theme_code,
                            "branch_id" => $user['branch_id'],
                            "branch" => $branch[0]->branch_name,
                            "emp_status" => $user['emp_status'],
                        ],
                        "roles" => json_decode($roles[0]->permission, true),
                        "token" => $token->plainTextToken,
                    ];
                    return response()->json($output, 200);
                }
                // } else if ($data['type'] == "LOCK_FINGER_SCAN_LOGIN") {
                // } else if ($data['type'] == "LOCK_IDCARD_LOGIN") {
            } else {
                $output = ['message' => [
                    "error_login" => ["error_login"]
                ]];
                return response()->json($output, 201);
            }
        }
    }

    public function signUp(Request $request)
    {
        $output = [];
        $data = json_decode($request->getContent(), true);

        $validate = User::validationSignUp($data);
        if ($validate['message'] !== true) {
            $output = ['message' => $validate['message']];
            return response()->json($output, 201);
        }

        $prepareData = [
            'emp_id' => $data['emp_id'],
            'username' => $data['username'],
            'roles' => $data['roles'],
            'password' => bcrypt($data['password']),
            'fing_path' => $data['emp_id'],
            'numoffice_d' => $data['numoffice_d'],
            'emp_prefix_id' => $data['emp_prefix_id'],
            'emp_name' => $data['emp_name'],
            'emp_surname' => $data['emp_surname'],
            'emp_name_e' => $data['emp_name_e'],
            'emp_surname_e' => $data['emp_surname_e'],
            'img_profile' => $data['username'],
            'card_id' => $data['card_id'],
            'position_id' => $data['position_id'],
            'group_id' => $data['group_id'],
            'brand_id' => $data['brand_id'],
            'branch_id' => $data['branch_id'],
            'emp_status' => 1,
            'regis_date' => date("Y-m-d"),
            'regis_time' => date("H:i:s"),
            'start_date' => date("Y-m-d"),
            'start_time' => date("H:i:s"),
            'end_date' => date('Y-m-d', strtotime("+1 year")),
            'end_time' => date("00:00:00"),
            'reg_user' => $data['reg_user'],
        ];

        $roleData = [
            'role_name' => $data['roles'],
            'brand_id' => $data['brand_id'],
            'emp_id' => $data['emp_id'],
            // 'permission' => $data['permission'],
            'status' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ];
        try {
            $result = DB::table('users')->insert($prepareData);

            if (!$result) {
                $error[] = $result;
            }

            if ($result == false) {
                $output = [
                    'message' => 'cannot_save_users'
                ];
                return response()->json($output, 201);
            } else {
                try {
                    $resultRole = DB::table('roles')->insert($roleData);
                    if ($resultRole == false) {
                        $output = [
                            'message' => 'cannot_save_users'
                        ];
                        return response()->json($output, 201);
                    } else {
                        $output = [
                            'message' => 'success'
                        ];
                        return response()->json($output, 200);
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                    $output = ['message' => $th->getMessage()];
                    return response()->json($output, 500);
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
            $output = ['message' => $th->getMessage()];
            return response()->json($output, 500);
        }
    }

    public function editUser(Request $request)
    {
        $output = [];
        $data = json_decode($request->getContent(), true);

        $validate = User::validationEditUser($data);
        if ($validate['message'] !== true) {
            $output = ['message' => $validate['message']];
            return response()->json($output, 201);
        }

        $user = DB::table('users')->where('emp_id', '=', $data['emp_id'])->count();

        if ($user < 1) {
            $output = ['message' => 'no data user'];
            return response()->json($output, 201);
        } else if ($user > 1) {
            $output = ['message' => 'please contact admin to check duplicate user'];
            return response()->json($output, 201);
        } else {
            $end_date = strtotime($data['end_date']);
            $end_time = strtotime($data['end_time']);

            $prepareData = [
                'emp_id' => $data['emp_id'],
                'username' => $data['username'],
                'roles' => $data['roles'],
                'password' => bcrypt($data['password']),
                'fing_path' => $data['emp_id'],
                'numoffice_d' => $data['numoffice_d'],
                'emp_prefix_id' => $data['emp_prefix_id'],
                'emp_name' => $data['emp_name'],
                'emp_surname' => $data['emp_surname'],
                'emp_name_e' => $data['emp_name_e'],
                'emp_surname_e' => $data['emp_surname_e'],
                'img_profile' => $data['username'],
                'card_id' => $data['card_id'],
                'position_id' => $data['position_id'],
                'group_id' => $data['group_id'],
                'brand_id' => $data['brand_id'],
                'branch_id' => $data['branch_id'],
                'emp_status' => $data['emp_status'],
                'end_date' => date("Y-m-d", $end_date),
                'end_time' => date("H:i:s", $end_time),
            ];
            try {
                $result = DB::table('users')->where('emp_id', '=', $data["emp_id"])->update($prepareData);
                if (!$result) {
                    $error[] = $result;
                }

                if ($result == false) {
                    $output = [
                        'message' => 'cannot_save_users'
                    ];
                    return response()->json($output, 201);
                } else {
                    $output = [
                        'message' => 'success'
                    ];
                    return response()->json($output, 200);
                }
            } catch (\Throwable $th) {
                //throw $th;
                $output = ['message' => $th->getMessage()];
                return response()->json($output, 500);
            }
        }
    }

    public function getUserInfo(Request $request)
    {
        $validate = User::validationGetUser(["emp_id" => $request->emp_id, "branch_id" => $request->branch_id]);

        if ($validate['message'] !== true) {
            $output = ['message' => $validate['message']];
            return response()->json($output, 201);
        }

        $user = DB::table('users')
            ->join('master_position as pos', 'users.position_id', '=', 'pos.emp_pos_id')
            ->join('conf_user_group as grp', 'users.group_id', '=', 'grp.group_id')
            ->join('master_brand as brand', 'users.brand_id', '=', 'brand.brand_id')
            ->join('master_branch as branch', 'users.branch_id', '=', 'branch.branch_id')
            ->select('users.emp_id', 'users.emp_name', 'users.emp_surname', 'users.roles', 'users.position_id', 'users.group_id', 'users.brand_id', 'users.branch_id',  'pos.emp_pos_name', 'branch.branch_name', 'brand.name_th', 'brand.name_en')
            ->where('users.emp_id', '=', $request->emp_id)
            ->where('users.branch_id', '=', $request->branch_id)
            ->get(); //local only
        if (count($user) == 0) {
            $output = ['message' => ['error_no_data']];
            return response()->json($output, 201);
        } else {
            $roles = DB::table('roles')
                ->where('role_name', '=', $user[0]->roles)
                ->where('emp_id', '=', $user[0]->emp_id)
                ->where('brand_id', '=', $user[0]->brand_id)
                ->where('status', '1')
                ->get();

            $output = [
                "message" => "success",
                "user" => $user,
                "roles" => json_decode($roles[0]->permission, true),
            ];
            return response()->json($output, 200);
        }
    }

    public function listUser(Request $request)
    {
        if (count($request->only(['branch_id'])) == 0 || $request->input('branch_id') == null) {
            $output = ['message' => 'error_invalid_data'];
            return response()->json($output, 201);
        } else {
            $users = DB::table('users')
                ->join('master_position as pos', 'users.position_id', '=', 'pos.emp_pos_id')
                ->join('conf_user_group as grp', 'users.group_id', '=', 'grp.group_id')
                ->join('master_brand as brand', 'users.brand_id', '=', 'brand.brand_id')
                ->join('master_branch as branch', 'users.branch_id', '=', 'branch.branch_id')
                ->select('users.emp_id', 'users.emp_name', 'users.emp_surname', 'users.roles', 'users.position_id', 'users.group_id', 'users.brand_id', 'users.branch_id',  'pos.emp_pos_name', 'branch.branch_name', 'brand.name_th', 'brand.name_en')
                ->where('users.branch_id', '=', $request->branch_id)->get(); //local only
            if (count($users) == 0) {
                $output = ['message' => 'error_no_data'];
                return response()->json($output, 201);
            } else {
                $output = [
                    "users" => $users,
                ];
                return response()->json($output, 200);
            }
        }
    }

    public function updatePermission(Request $request)
    {
        $output = [];
        $data = json_decode($request->getContent(), true);

        $validate = User::validationUpdatePermission($data);
        if ($validate['message'] !== true) {
            $output = ['message' => $validate['message']];
            return response()->json($output, 201);
        }

        $user = DB::table('users')->where('emp_id', '=', $data['emp_id'])->where('brand_id', '=', $data['brand_id'])->get();
        $role = DB::table('roles')->where('emp_id', '=', $data['emp_id'])->where('brand_id', '=', $data['brand_id'])->where('role_name', '=', $data['role_name'])->get();

        if (count($user) < 1) {
            $output = ['message' => 'error_no_data_user'];
            return response()->json($output, 201);
        } else if (count($role) < 1) {
            $output = ['message' => 'error_no_data_role'];
            return response()->json($output, 201);
        } else {
            $saveLogData = [
                "branch_id" => $user[0]->branch_id,
                "emp_id" => $user[0]->emp_id,
                "module" => "UPDATE_PERMISSION_POS",
                "module_type" => "PERMISSION",
            ];
            $saveLogs = Logs::saveLogs($saveLogData);
            if ($saveLogs['message'] !== true) {
                $output = ['message' => $validate['message']];
                return response()->json($output, 201);
            }

            $prepareData = [
                'role_name' => $data['role_name'],
                'brand_id' => $data['brand_id'],
                'emp_id' => $data['emp_id'],
                'permission' =>  $data['permission'],
                'updated_at' => date("Y-m-d H:i:s")
            ];
            try {
                $result = DB::table('roles')
                    ->where('emp_id', '=', $data["emp_id"])
                    ->where('role_name', '=', $data["role_name"])
                    ->where('brand_id', '=', $data["brand_id"])
                    ->update($prepareData);
                if (!$result) {
                    $error[] = $result;
                }

                if ($result == false) {
                    $output = [
                        'message' => 'cannot_save_permission'
                    ];
                    return response()->json($output, 201);
                } else {
                    $output = [
                        'message' => 'success'
                    ];
                    return response()->json($output, 200);
                }
            } catch (\Throwable $th) {
                //throw $th;
                $output = ['message' => $th->getMessage()];
                return response()->json($output, 500);
            }
        }
    }

    public function logout(Request $request)
    {
        $output = [];
        $data = json_decode($request->getContent(), true);

        $validate = User::validationLogout($data);
        if ($validate['message'] !== true) {
            $output = ['message' => $validate['message']];
            return response()->json($output, 201);
        }

        $user = DB::table('users')->where('username', '=', $data['username'])->get();

        $saveLogData = [
            "branch_id" => $user[0]->branch_id,
            "emp_id" => $user[0]->emp_id,
            "module" => "LOGOUT_POS",
            "module_type" => "LOGOUT",
        ];
        $saveLogs = Logs::saveLogs($saveLogData);
        if ($saveLogs['message'] !== true) {
            $output = ['message' => $validate['message']];
            return response()->json($output, 201);
        }

        $result = DB::table('personal_access_tokens')->where('name', '=', $data['username'])->delete();
        if ($result < 1) {
            $output = [
                'message' => 'not_existed_login'
            ];
            return response()->json($output, 201);
        } else {
            $output = [
                'message' => 'success'
            ];
            return response()->json($output, 200);
        }
    }
}
