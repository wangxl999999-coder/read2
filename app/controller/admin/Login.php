<?php

namespace app\controller\admin;

use app\BaseController;
use app\model\Admin as AdminModel;
use think\facade\View;
use think\facade\Session;

class Login extends BaseController
{
    public function index()
    {
        if ($this->isAdminLogin()) {
            return redirect(url('/admin/index'));
        }

        if ($this->request->isPost()) {
            $username = $this->request->post('username', '');
            $password = $this->request->post('password', '');

            if (empty($username) || empty($password)) {
                return $this->error('请输入用户名和密码');
            }

            $admin = AdminModel::login($username, $password);

            if (!$admin) {
                return $this->error('用户名或密码错误');
            }

            Session::set('admin_id', $admin->id);
            Session::set('admin_username', $admin->username);
            Session::set('admin_nickname', $admin->nickname);
            Session::set('admin_role', $admin->role);

            return $this->success('登录成功', null, url('/admin/index'));
        }

        return View::fetch();
    }

    public function logout()
    {
        Session::delete('admin_id');
        Session::delete('admin_username');
        Session::delete('admin_nickname');
        Session::delete('admin_role');

        return redirect(url('/admin/login'));
    }
}
