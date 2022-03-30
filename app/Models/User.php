<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        // 'email_verified_at' => 'datetime',
    ];

    // public function roles()
    // {
    //     return $this->belongsToMany(Roles::class);
    // }

    public function validationLogin($data)
    {
        $v = Validator::make($data, [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (count($v->errors()->messages()) !== 0) {
            return ['message' => $v->errors()->messages()];
        }

        return ['message' => true];
    }

    public function validationLogout($data)
    {
        $v = Validator::make($data, [
            'username' => 'required|string',
            // 'password' => 'required|string',
        ]);

        if (count($v->errors()->messages()) !== 0) {
            return ['message' => $v->errors()->messages()];
        }

        return ['message' => true];
    }

    public function validationListUser($data)
    {
        $v = Validator::make($data, [
            'branch_id' => 'required|string',
            // 'role_name' => 'required|string',
            // 'brand_id' => 'required|string',
            // 'permission' => 'required|string',
        ]);

        if (count($v->errors()->messages()) !== 0) {
            return ['message' => $v->errors()->messages()];
        }

        return ['message' => true];
    }

    public function validationUpdatePermission($data)
    {
        $v = Validator::make($data, [
            'emp_id' => 'required|string',
            'role_name' => 'required|string',
            'brand_id' => 'required|string',
            'permission' => 'required|string',
        ]);

        if (count($v->errors()->messages()) !== 0) {
            return ['message' => $v->errors()->messages()];
        }

        return ['message' => true];
    }

    public function validationSignUp($data)
    {
        $v = Validator::make($data, [
            'emp_id' => 'required|string',
            'username' => 'required|string',
            'roles' => 'required|string',
            'password' => 'required|string',
            'numoffice_d' => 'required|string|size:10',
            'emp_prefix_id' => 'required|string',
            'emp_name' => 'required|string',
            'emp_surname' => 'required|string',
            'emp_name_e' => 'required|string',
            'emp_surname_e' => 'required|string',
            'card_id' => 'required|string|size:13',
            'position_id' => 'required|string',
            'group_id' => 'required|string',
            'brand_id' => 'required|string',
            // 'corporation_id' => 'required|string',
            'branch_id' => 'required|string',
            'reg_user' => 'required|string',
        ]);

        if (count($v->errors()->messages()) !== 0) {
            return ['message' => $v->errors()->messages()];
        }

        return ['message' => true];
    }

    public function validationEditUser($data)
    {
        $v = Validator::make($data, [
            'emp_id' => 'required|string',
            'username' => 'required|string',
            'roles' => 'required|string',
            'password' => 'required|string',
            'numoffice_d' => 'required|string|size:10',
            'emp_prefix_id' => 'required|string',
            'emp_name' => 'required|string',
            'emp_surname' => 'required|string',
            'emp_name_e' => 'required|string',
            'emp_surname_e' => 'required|string',
            'card_id' => 'required|string|size:13',
            'position_id' => 'required|string',
            'group_id' => 'required|string',
            'brand_id' => 'required|string',
            // 'corporation_id' => 'required|string',
            'branch_id' => 'required|string',
            'emp_status' => 'required|integer',
            'end_date' => 'required|string',
            'end_time' => 'required|string',
        ]);

        if (count($v->errors()->messages()) !== 0) {
            return ['message' => $v->errors()->messages()];
        }

        return ['message' => true];
    }

    public function validationUser($data)
    {
        $v = Validator::make($data, [
            'id' => 'required|integer',
            'username' => 'required|string',
            'roles' => 'required|string',
            'emp_name' => 'required|string',
            'emp_surname' => 'required|string',
            'shop.shop_code' => 'required|integer',
        ]);

        if (count($v->errors()->messages()) !== 0) {
            return ['message' => $v->errors()->messages()];
        }

        return ['message' => true];
    }
}
