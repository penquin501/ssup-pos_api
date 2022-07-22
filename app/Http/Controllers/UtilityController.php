<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Image;

class UtilityController extends Controller
{
    public function checkIp(Request $request)
    {
        $ip = $request->ip;
        $port = $request->port;
        $url = $ip . ':' . $port;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $health = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        if ($health) {
            $json = ['health' => $health, 'status' => '1'];
        } else {
            $json = ['health' => $health, 'status' => '0'];
        }
        return response()->json($json, 200);
    }

    public function checkConnectDb(Request $request)
    {
        DB::table('conf_check_pos')->upsert(['id' => 1, 'status' => 'connect db', 'timestamp' => date("Y-m-d H:i:s")], ['id', 'status'], ['timestamp']);
        $health = DB::table('conf_check_pos')->get();

        return response()->json($health->all(), 200);
    }

    public function checkConfigLogin(Request $request)
    {
        $ip = $request->ip;

        $hardwareConfig = DB::table('conf_branch_hardware')
            ->where('hardware_ip', '=', $ip)
            ->where('status', '=', 1)
            ->get();

        if (count($hardwareConfig) < 1) {
            $message = ['message' => 'error_ip_not_found'];
            return response()->json($message, 201);
        }

        $brandInfo = DB::table('master_brand')
            ->where('brand_id', '=', $hardwareConfig[0]->brand_id)
            ->get();

        $confLogin = DB::table('conf_branch')
            ->where('brand_id', '=', $hardwareConfig[0]->brand_id)
            ->where('branch_id', '=', $hardwareConfig[0]->branch_id)
            ->get();

        $startLogin = [];
        foreach ($confLogin as $el) {
            if ($el->default_type == "Y" && $el->module_type == "LOGIN") {
                $startLogin = $el;
            }
        }
        $output = [
            "brandInfo" => $brandInfo[0],
            "configLogin" => $startLogin,
            // "hardwareConfig" => $hardwareConfig
        ];

        return response()->json($output, 200);
    }

    public function uploadImage(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $image = $request->file('image');
        // ex. $input['imagename'] = '1648030892.jpg'
        $input['imagename'] = time() . '.' . $image->extension(); //กำหนด filename

        $destinationPath = public_path('/thumbnail');
        $img = Image::make($image->path());
        $img->resize(100, 100, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $input['imagename']);

        $destinationPath = public_path('/images');
        $image->move($destinationPath, $input['imagename']);

        $output = ['img_name' => $input['imagename']];
        return response()->json($output, 200);
        // return back()
        //     ->with('success', 'Image Upload successful')
        //     ->with('imageName', $input['imagename']);
    }

    public function getImagePath(Request $request)
    {
        $image = $request->image;

        //image address
        return Redirect::to('images/' . $image . '.jpg');
    }

    public function checkDocDate(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        DB::table('com_doc_date')->upsert(
            [
                "brand_id" => $data['brand_id'],
                "branch_id" => $data['branch_id'],
                "doc_date" => date("Y-m-d"),
                "remark" => "",
                "reg_date" => date("Y-m-d"),
                "reg_time" => date("H:i:s"),
                "reg_user" => "system",
                "upd_date" => date("Y-m-d"),
                "upd_time" => date("H:i:s"),
                "upd_user" => "system",
            ],
            ['brand_id', 'branch_id', 'doc_date', 'remark', 'reg_date', 'reg_time', 'reg_user', 'upd_date', 'upd_time', 'upd_user'],
            ['doc_date', 'upd_date', 'upd_time', 'upd_user']
        );
        $docDate = DB::table('com_doc_date')->get();

        return response()->json($docDate, 200);
    }

    public function updateDocDate(Request $request)
    {
        $brand_id = $request->brand_id;
        $branch_id = $request->branch_id;
        $result = DB::table('com_doc_date')->where('brand_id', '=', $brand_id)->where('branch_id', '=', $branch_id)->update(['doc_date' => date('Y-m-d')]);
        return response()->json($result, 200);
    }

    public function listMenu(Request $request)
    {
        $brand_id = $request->brand_id;
        $type = $request->type;

        $listMenu = DB::table('com_menu')
            ->where('brand_id', '=', $brand_id)
            ->where('type', '=', $type)
            ->get();

        return response()->json($listMenu, 200);
    }

    public function getLocale(Request $request)
    {
        $brand_id = $request->brand_id;
        $listMenu = DB::table('locale')
            ->where('brand_id', '=', $brand_id)
            ->get();
        $langs = [];
        foreach ($listMenu as $menu) {
            $langs[$menu->lang][$menu->page][] = [
                $menu->key => $menu->msg
            ];
        }
        return response()->json($langs, 200);
    }
}
