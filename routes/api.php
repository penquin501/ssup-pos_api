<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CashierController;
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
            Route::post('/update/permission', [UserController::class, 'updatePermission']);
            Route::get('/listuser', [UserController::class, 'listUser']);
            Route::get('/getUserInfo', [UserController::class, 'getUserInfo']);
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
            Route::get('/invoice', [CartController::class, 'invoice']);
            // Route::post('/add/temp', [CartController::class, 'register']);
            // Route::post('/edit/temp', [CartController::class, 'editUserData']);
            // Route::post('/save/bill', [CartController::class, 'editUserData']);
            // Route::post('/edit/temp', [CartController::class, 'editUserData']);
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
});
