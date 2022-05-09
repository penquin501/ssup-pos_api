<?php

namespace App\Http\Controllers;

use App\Models\ConfAccessSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class ExtraController extends Controller
{
    public function createTable(Request $request)
    {
        $output = [];
        $data = json_decode($request->getContent(), true);

        $validate = $this->validationCheckCol($data);
        if ($validate['message'] !== true) {
            $output = ['message' => $validate['message']];
            return response()->json($output, 201);
        }

        if (Schema::hasTable($data['table'])) {
            $output = ['message' => 'error_existed_table'];
            return response()->json($output, 201);
        } else {
            $str = "";
            $strIndex = "";

            foreach ($data['column'] as $col) {
                $col['canNull'] = $col['canNull'] ? "NULL" : "NOT NULL";
                $col['length'] = $col['length'] == 0 ? " " : "(" . $col['length'] . ") ";

                $str .= "`" . $col['name'] . "` " . $col['type'] . $col['length'] . $col['canNull'] . ", ";

                if ($col['index'] !== "") {
                    $strIndex .= "INDEX `" . $col['index'] . "` (`" . $col['index'] . "`),";
                }
            }

            $rStrIndex = rtrim($strIndex, ",");

            $sql = "CREATE TABLE `pos_g2`.`" . $data['table'] . "`(`id` INT NOT NULL AUTO_INCREMENT," . rtrim($str, ", ") . ", PRIMARY KEY (`id`), " . $rStrIndex . ") ENGINE = InnoDB;";

            $result = DB::statement($sql);
            if ($result) {
                $output = ['message' => 'success'];
                return response()->json($output, 200);
            } else {
                $output = ['message' => 'error_cannot_create_table = ' . $data['table']];
                return response()->json($output, 201);
            }
        }
    }

    public function dropTable(Request $request)
    {
        $output = [];
        $data = json_decode($request->getContent(), true);

        $validate = $this->validationSqlDropTable($data);
        if ($validate['message'] !== true) {
            $output = ['message' => $validate['message']];
            return response()->json($output, 201);
        }

        if (!Schema::hasTable($data['table'])) {
            $output = ['message' => 'error_no_table'];
            return response()->json($output, 201);
        } else {
            $result = DB::statement("DROP TABLE " . $data['table']);
            if ($result) {
                $output = ['message' => 'success'];
                return response()->json($output, 200);
            } else {
                $output = ['message' => 'error_cannot_drop_table = ' . $data['table']];
                return response()->json($output, 201);
            }
        }
    }

    public function addColumn(Request $request)
    {
        $output = [];
        $data = json_decode($request->getContent(), true);

        $validate = $this->validationCheckCol($data);
        if ($validate['message'] !== true) {
            $output = ['message' => $validate['message']];
            return response()->json($output, 201);
        }
        $arrCol = [];
        foreach ($data['column'] as $col) {
            $arrCol[] = $col['name'];
        }

        if (Schema::hasColumns($data['table'], $arrCol)) {
            $output = ['message' => 'error_existed_column'];
            return response()->json($output, 201);
        } else {
            $str = "";
            $strIndex = "";

            foreach ($data['column'] as $col) {
                $col['canNull'] = $col['canNull'] ? "NULL" : "NOT NULL";
                $col['length'] = $col['length'] == 0 ? " " : "(" . $col['length'] . ") ";

                $str .= " ADD `" . $col['name'] . "` " . $col['type'] . $col['length'] . $col['canNull'] . ",";

                if ($col['index'] !== "") {
                    $strIndex .= " ADD INDEX `" . $col['index'] . "` (`" . $col['index'] . "`),";
                }
            }

            $rStrIndex = rtrim($strIndex, ",");
            $sql = "ALTER TABLE `" . $data['table'] . "`" . $str . $rStrIndex . ";";

            $result = DB::statement($sql);
            if ($result) {
                $output = ['message' => 'success'];
                return response()->json($output, 200);
            } else {
                $output = ['message' => 'error_cannot_create_column = ' . $data['table']];
                return response()->json($output, 201);
            }
        }
    }

    public function deleteColumn(Request $request)
    {
        $output = [];
        $data = json_decode($request->getContent(), true);

        $validate = $this->validationCheckCol($data);
        if ($validate['message'] !== true) {
            $output = ['message' => $validate['message']];
            return response()->json($output, 201);
        }

        $arrCol = [];
        foreach ($data['column'] as $col) {
            $arrCol[] = $col['name'];
        }

        if (!Schema::hasColumns($data['table'], $arrCol)) {
            $output = ['message' => 'error_no_column'];
            return response()->json($output, 201);
        } else {
            $str = "";
            foreach ($data['column'] as $col) {
                $str .= " DROP `" . $col['name'] . "`,";
            }

            $rStrSql = rtrim($str, ",");
            $sql = "ALTER TABLE `" . $data['table'] . "`" . $rStrSql . ";";

            $result = DB::statement($sql);
            if ($result) {
                $output = ['message' => 'success'];
                return response()->json($output, 200);
            } else {
                $output = ['message' => 'error_cannot_drop_column = ' . $data['table']];
                return response()->json($output, 201);
            }
        }
    }

    public function checkAccessSystem(Request $request)
    {
        $output = [];
        $data = json_decode($request->getContent(), true);

        $validate = ConfAccessSystem::validateCheckAccess($data);
        if ($validate['message'] !== true) {
            $output = ['message' => $validate['message']];
            return response()->json($output, 201);
        }

        $checkAccessSystem = DB::table('conf_access_system')
            ->where('ip_address', '=', $data['ip_address'])
            ->where('path', '=', $data['path'])
            ->where('emp_id', '=', $data['emp_id'])
            ->get();

        if (count($checkAccessSystem) > 0) {
            $output = [
                "message" => "existed_access_system",
                "data" => $checkAccessSystem
            ];
            return response()->json($output, 200);
        } else {
            DB::table('conf_access_system')->upsert(
                [
                    "ip_address" => $data['ip_address'],
                    "path" => $data['path'],
                    "emp_id" => $data['emp_id'],
                    "record_at" => date("Y-m-d H:i:s"),
                ],
                ['ip_address', 'path', 'emp_id'],
                ['ip_address', 'path', 'emp_id', 'record_at']
            );

            $checkAccessSystem = DB::table('conf_access_system')
                ->where('ip_address', '=', $data['ip_address'])
                ->where('path', '=', $data['path'])
                ->where('emp_id', '=', $data['emp_id'])
                ->get();

            $output = [
                "message" => "new_access_system",
                "data" => $checkAccessSystem
            ];
            return response()->json($output, 200);
        }
    }

    function validationSqlDropTable($data)
    {
        $v = Validator::make($data, [
            'table' => 'required|string'
        ]);

        if (count($v->errors()->messages()) !== 0) {
            return ['message' => $v->errors()->messages()];
        }

        return ['message' => true];
    }

    function validationCheckCol($data)
    {
        $v = Validator::make($data, [
            'table' => 'required|string',
            'column' => 'required|Array'
        ]);

        if (count($v->errors()->messages()) !== 0) {
            return ['message' => $v->errors()->messages()];
        }

        return ['message' => true];
    }
}
