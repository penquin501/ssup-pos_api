<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PhpParser\Node\Stmt\TryCatch;

class ProductController extends Controller
{
    public function productInfo(Request $request)
    {
        if (count($request->only(['product_id'])) == 0 || $request->input('product_id') == null) {
            $output = ['message' => 'error_invalid_data'];
            return response()->json($output, 201);
        } else {
            $product = DB::table('product_master')
                ->where('product_id', '=', $request->input('product_id'))
                ->orWhere('barcode', '=', $request->input('product_id'))->get();
            if (count($product) == 0) {
                $output = ['message' => 'error_no_data'];
                return response()->json($output, 201);
            } else {

                $output = [
                    'message' => 'success',
                    "product" => $product,
                ];
                return response()->json($output, 200);
            }
        }
    }

    public function listProduct(Request $request)
    {
        $product = DB::table('product_master')->get(); //local only
        if (count($product) == 0) {
            $output = ['message' => 'error_no_data'];
            return response()->json($output, 201);
        } else {
            $output = [
                "product" => $product,
            ];
            return response()->json($output, 200);
        }
    }

    public function getAllProductMaster(Request $request)
    {
        // $ip = $request->ip;
        $ip = '192.168.2.248';
        try {
            //code...
            $data = Http::timeout(30)->get("http://" . $ip . "/stock/file/check_product_master_g2.php"); //TODO:เขียน api เป็น Laravel ที่ 192.168.2.248
            if (!$data->successful()) {
                $output = ['message' => 'error_cannot_connect_product_master'];
                return response()->json($output, $data->getStatusCode());
            }

            $listProductMaster = json_decode($data->body(), true);

            $result = [];
            $error = [];

            foreach ($listProductMaster['data'] as $product) {
                if ($product['tax_type'] == 'V') {
                    $product['tax_type'] = 7;
                }
                $prepareData = [
                    'product_id' => $product['product_id'],
                    'barcode' => $product['barcode'] == '' ? $product['product_id'] : $product['barcode'],
                    'name_product' => $product['name_product'] == '' ? $product['product_id'] : $product['name_product'],
                    'name_print' => $product['name_print'] == '' ? $product['product_id'] : $product['name_print'],
                    'price' => $product['price'],
                    'cost' => $product['cost'],
                    'group_name' => $product['group'],
                    'type_name' => $product['type'],
                    'unit' => $product['unit'],
                    'tax' => $product['tax_type'],
                    'picture' => $product['picture'],
                    'fix_stock' => $product['fix_stock'],
                    'first_sale_date' => $product['first_sale_date'],
                    'last_sale_date' => $product['last_sale_date'],
                    'reg_date' => $product['reg_date'],
                    'reg_time' => $product['reg_time'],
                    'reg_user' => $product['reg_user'],
                    'upd_date' => $product['upd_date'],
                    'upd_time' => $product['upd_time'],
                    'upd_user' => $product['upd_user'],
                    'tran_date' => $product['tran_date'],
                    'tran_system' => $product['tran_system']
                ];
                $productInfo = DB::table('product_master')->where('product_id', '=', $product["product_id"])->get();

                if (count($productInfo) == 0) {
                    //insert
                    try {
                        $saveProduct = DB::table('product_master')->insert($prepareData);
                        if (!$saveProduct) {
                            $error[] = $saveProduct;
                        }
                        $result[] = $product['barcode'];
                    } catch (\Throwable $th) {
                        $output = ['message' => $th->getMessage()];
                        return response()->json($output, 500);
                    }
                } else {
                    //update
                    try {
                        $updateProduct = DB::table('product_master')->where('product_id', '=', $product["product_id"])->update($prepareData);
                        if (!$updateProduct) {
                            $error[] = $updateProduct;
                        }
                        $result[] = $updateProduct;
                    } catch (\Throwable $th) {
                        $output = ['message' => $th->getMessage()];
                        return response()->json($output, 500);
                    }
                }
            }

            $diff = [];
            $lError = [];

            foreach ($result as $el) {
                if ($el !== 0) {
                    $diff[] = $el;
                }
            }
            foreach ($error as $er) {
                if ($er !== 0) {
                    $lError[] = $er;
                }
            }

            $output = ['message' => 'success'];
            if (count($lError) !== 0) {
                $output['error'] = $lError;
            }
            if (count($diff) !== 0) {
                $output['diff_data'] = $diff;
            }

            return response()->json($output, 200);
        } catch (\Throwable $th) {
            $output = ['message' => $th->getMessage()];
            return response()->json($output, 500);
        }
    }

    public function syncProductMaster(Request $request)
    {
        //TODO: เลือกเอาข้อมูล Product ที่อยากให้เอามาใส่ใน local
        $product_id = $request->input("product_id");
        try {
            //code...
            $ip = '192.168.2.248';
            $data = Http::timeout(30)->get("http://" . $ip . "/stock/file/check_product_master_g2.php"); //TODO:เขียน api เป็น Laravel ที่ 192.168.2.248
            if (!$data->successful()) {
                $output = ['message' => 'error_cannot_connect_product_master'];
                return response()->json($output, $data->getStatusCode());
            }

            $listProductMaster = json_decode($data->body(), true);
            $product = $listProductMaster[0];
            $prepareData = [
                'product_id' => $product['product_id'],
                'barcode' => $product['barcode'] == '' ? $product['product_id'] : $product['barcode'],
                'name_product' => $product['name_product'] == '' ? $product['product_id'] : $product['name_product'],
                'name_print' => $product['name_print'] == '' ? $product['product_id'] : $product['name_print'],
                'price' => $product['price'],
                'cost' => $product['cost'],
                'group_name' => $product['group'],
                'type_name' => $product['type'],
                'unit' => $product['unit'],
                'tax' => $product['tax_type'],
                'picture' => $product['picture'],
                'fix_stock' => $product['fix_stock'],
                'first_sale_date' => $product['first_sale_date'],
                'last_sale_date' => $product['last_sale_date'],
                'reg_date' => $product['reg_date'],
                'reg_time' => $product['reg_time'],
                'reg_user' => $product['reg_user'],
                'upd_date' => $product['upd_date'],
                'upd_time' => $product['upd_time'],
                'upd_user' => $product['upd_user'],
                'tran_date' => $product['tran_date'],
                'tran_system' => $product['tran_system']
            ];
            try {
                $saveProduct = DB::table('master_products')->insert($prepareData);
                if (!$saveProduct) {
                    $error[] = $saveProduct;
                }
                $result[] = $product['barcode'];
            } catch (\Throwable $th) {
                $output = ['message' => $th->getMessage()];
                return response()->json($output, 500);
            }
        } catch (\Throwable $th) {
            //throw $th;
            $output = ['message' => $th->getMessage()];
            return response()->json($output, 500);
        }
    }
}
