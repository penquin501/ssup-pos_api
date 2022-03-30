<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
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
}
