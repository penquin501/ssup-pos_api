<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\ExtraController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UtilityController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

/**
 * API For Check System
 */
Route::get('/check/connect/db', [UtilityController::class, 'checkConnectDb']);

Route::post('/tokens/create', function (Request $request) {
    $user = User::find(1);
    $token = $user->createToken($request->token_name);
    return ['token' => $token->plainTextToken];
});

Route::get('/imagepath', [UtilityController::class, 'getImagePath']);
Route::post('/uploadImage', [UtilityController::class, 'uploadImage']);
Route::post('/checkDocDate', [UtilityController::class, 'checkDocDate']);
Route::get('/updateDocDate', [UtilityController::class, 'updateDocDate']);
Route::get('/get/locale', [UtilityController::class, 'getLocale']);

/**
 * API For POS
 */
Route::get('/checkip', [UtilityController::class, 'checkIp']);
Route::get('/checkconfiglogin', [UtilityController::class, 'checkConfigLogin']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::prefix("address")->group(
        function () {
            Route::get('/provinces', [AddressController::class, 'getProvinces']);
            Route::post('/districts', [AddressController::class, 'getDistricts']);
            Route::post('/subdistricts', [AddressController::class, 'getSubDistricts']);
        }
    );

    Route::prefix("user")->group(
        function () {
            Route::post('/signup', [UserController::class, 'signUp']);
            Route::post('/edit', [UserController::class, 'editUser']);
            Route::get('/listuser', [UserController::class, 'listUser']);
            Route::get('/getUserInfo', [UserController::class, 'getUserInfo']);
            Route::get('/list/permission', [UserController::class, 'listPermission']);
            Route::post('/update/permission', [UserController::class, 'updatePermission']);
        }
    );

    Route::prefix("member")->group(
        function () {
            Route::get('/listmember', [MemberController::class, 'listMember']);
            Route::post('/memberinfo', [MemberController::class, 'memberInfo']);
            Route::post('/register', [MemberController::class, 'register']);
            // Route::post('/edit/member', [MemberController::class, 'editMemberData']);
        }
    );

    Route::prefix("cart")->group(
        function () {
            Route::get('/listpaid', [CartController::class, 'listCreditType']);
            Route::post('/listbilltype', [CartController::class, 'listBillType']);
            Route::post('/listfreebag', [CartController::class, 'listFreeBag']);
            Route::post('/addcart/temp', [CartController::class, 'addCartTemp']);
            Route::post('/delcart/temp', [CartController::class, 'delCartTemp']);
            Route::post('/save/bill/main', [CartController::class, 'saveBillMain']);
            Route::get('/receipt', [CartController::class, 'receipt']);
        }
    );

    Route::prefix("cashier")->group(
        function () {
            Route::post('/start', [CashierController::class, 'start']);
            Route::post('/end', [CashierController::class, 'end']);
        }
    );

    Route::prefix("product")->group(
        function () {
            Route::post('/productinfo', [ProductController::class, 'productInfo']);
            Route::get('/listproduct', [ProductController::class, 'listProduct']);
            Route::get('/get-productmaster', [ProductController::class, 'getAllProductMaster']);
            Route::post('/sync-productmaster', [ProductController::class, 'syncProductMaster']);
        }
    );

    Route::prefix("promotion")->group(
        function () {
            // Route::get('/promotioninfo', [ProductController::class, 'promotionInfo']);
            // Route::post('/listpromotion', [ProductController::class, 'listPromotion']);
            // Route::post('/get-promotion', [ProductController::class, 'getAllPromotion']);
            // Route::post('/sync-promotion', [ProductController::class, 'syncPromotion']);
        }
    );

    Route::prefix("menu")->group(
        function () {
            Route::get('/listmenu', [UtilityController::class, 'listMenu']);
        }
    );

    Route::prefix("sql")->group(
        function () {
            Route::post('/createTable', [ExtraController::class, 'createTable']);
            Route::post('/dropTable', [ExtraController::class, 'dropTable']);
            Route::post('/addColumn', [ExtraController::class, 'addColumn']);
            Route::post('/deleteColumn', [ExtraController::class, 'deleteColumn']);
        }
    );

    Route::prefix("access")->group(
        function () {
            Route::post('/check/system', [ExtraController::class, 'checkAccessSystem']);
        }
    );
});
