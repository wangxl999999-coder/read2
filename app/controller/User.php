<?php

namespace app\controller;

use app\BaseController;
use app\model\User as UserModel;
use app\model\ReadingRecord;
use think\facade\View;
use think\facade\Session;

class User extends BaseController
{
    public function login()
    {
        if ($this->isLogin()) {
            return redirect(url('/'));
        }

        if ($this->request->isPost()) {
            $username = $this->request->post('username', '');
            $password = $this->request->post('password', '');

            if (empty($username) || empty($password)) {
                return $this->error('请输入用户名和密码');
            }

            $user = UserModel::where('username', $username)->find();

            if (!$user || !password_verify($password, $user->password)) {
                return $this->error('用户名或密码错误');
            }

            if ($user->status != 1) {
                return $this->error('账号已被禁用，请联系管理员');
            }

            $user->last_login_time = date('Y-m-d H:i:s');
            $user->last_login_ip = $this->request->ip();
            $user->save();

            Session::set('user_id', $user->id);
            Session::set('username', $user->username);
            Session::set('nickname', $user->nickname ?: $user->username);
            Session::set('avatar', $user->avatar);

            return $this->success('登录成功', null, url('/'));
        }

        View::assign([
            'isLogin' => $this->isLogin(),
            'user' => $this->isLogin() ? $this->getUser() : null,
        ]);

        return View::fetch();
    }

    public function register()
    {
        if ($this->isLogin()) {
            return redirect(url('/'));
        }

        if ($this->request->isPost()) {
            $username = $this->request->post('username', '');
            $password = $this->request->post('password', '');
            $confirmPassword = $this->request->post('confirm_password', '');
            $email = $this->request->post('email', '');
            $nickname = $this->request->post('nickname', '');

            if (empty($username) || empty($password)) {
                return $this->error('请输入用户名和密码');
            }

            if ($password != $confirmPassword) {
                return $this->error('两次密码输入不一致');
            }

            if (strlen($username) < 3 || strlen($username) > 20) {
                return $this->error('用户名长度应在3-20个字符之间');
            }

            if (strlen($password) < 6) {
                return $this->error('密码长度不能少于6个字符');
            }

            $existing = UserModel::where('username', $username)->find();
            if ($existing) {
                return $this->error('用户名已存在');
            }

            if (!empty($email)) {
                $existingEmail = UserModel::where('email', $email)->find();
                if ($existingEmail) {
                    return $this->error('邮箱已被注册');
                }
            }

            $user = new UserModel();
            $user->username = $username;
            $user->password = password_hash($password, PASSWORD_DEFAULT);
            $user->nickname = $nickname ?: $username;
            $user->email = $email;
            $user->save();

            Session::set('user_id', $user->id);
            Session::set('username', $user->username);
            Session::set('nickname', $user->nickname);
            Session::set('avatar', null);

            return $this->success('注册成功', null, url('/'));
        }

        View::assign([
            'isLogin' => $this->isLogin(),
            'user' => $this->isLogin() ? $this->getUser() : null,
        ]);

        return View::fetch();
    }

    public function logout()
    {
        Session::delete('user_id');
        Session::delete('username');
        Session::delete('nickname');
        Session::delete('avatar');

        return redirect(url('/'));
    }

    public function profile()
    {
        $needLogin = $this->needLogin();
        if ($needLogin) {
            return $needLogin;
        }

        $userId = $this->getUserId();
        $user = UserModel::find($userId);
        $stats = $user->getReadingStats($userId);

        $todayReading = ReadingRecord::getTodayReadingTime($userId);
        $weekReading = ReadingRecord::getWeekReadingTime($userId);
        $dailyStats = ReadingRecord::getDailyStats($userId, 7);

        View::assign([
            'isLogin' => $this->isLogin(),
            'user' => $this->getUser(),
            'userInfo' => $user,
            'stats' => $stats,
            'todayReading' => $todayReading,
            'weekReading' => $weekReading,
            'dailyStats' => $dailyStats,
        ]);

        return View::fetch();
    }

    public function update()
    {
        $needLogin = $this->needLogin();
        if ($needLogin) {
            return $needLogin;
        }

        if ($this->request->isPost()) {
            $userId = $this->getUserId();
            $user = UserModel::find($userId);

            $nickname = $this->request->post('nickname', '');
            $email = $this->request->post('email', '');

            if (!empty($nickname)) {
                $user->nickname = $nickname;
            }

            if (!empty($email)) {
                $existingEmail = UserModel::where('email', $email)
                    ->where('id', '<>', $userId)
                    ->find();
                if ($existingEmail) {
                    return $this->error('邮箱已被使用');
                }
                $user->email = $email;
            }

            $user->save();

            Session::set('nickname', $user->nickname);

            return $this->success('更新成功', null, url('/user/profile'));
        }

        return $this->error('请求方法错误');
    }

    public function updatePassword()
    {
        $needLogin = $this->needLogin();
        if ($needLogin) {
            return $needLogin;
        }

        if ($this->request->isPost()) {
            $userId = $this->getUserId();
            $user = UserModel::find($userId);

            $oldPassword = $this->request->post('old_password', '');
            $newPassword = $this->request->post('new_password', '');
            $confirmPassword = $this->request->post('confirm_password', '');

            if (!password_verify($oldPassword, $user->password)) {
                return $this->error('原密码错误');
            }

            if ($newPassword != $confirmPassword) {
                return $this->error('两次密码输入不一致');
            }

            if (strlen($newPassword) < 6) {
                return $this->error('密码长度不能少于6个字符');
            }

            $user->password = password_hash($newPassword, PASSWORD_DEFAULT);
            $user->save();

            return $this->success('密码修改成功');
        }

        return $this->error('请求方法错误');
    }
}
