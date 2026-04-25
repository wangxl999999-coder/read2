<?php

namespace app\model;

use think\Model;

class Admin extends Model
{
    protected $name = 'admins';
    protected $pk = 'id';

    protected $type = [
        'last_login_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = ['password'];

    public function getRoleTextAttr($value, $data)
    {
        return $data['role'] == 2 ? '超级管理员' : '普通管理员';
    }

    public function getStatusTextAttr($value, $data)
    {
        return $data['status'] == 1 ? '正常' : '禁用';
    }

    public static function login($username, $password)
    {
        $admin = self::where('username', $username)
            ->where('status', 1)
            ->find();

        if ($admin && password_verify($password, $admin->password)) {
            $admin->last_login_time = date('Y-m-d H:i:s');
            $admin->last_login_ip = request()->ip();
            $admin->save();

            return $admin;
        }

        return null;
    }
}
