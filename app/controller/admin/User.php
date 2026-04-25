<?php

namespace app\controller\admin;

use app\BaseController;
use app\model\User as UserModel;
use app\model\ReadingRecord;
use think\facade\View;

class User extends BaseController
{
    public function index()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        $page = $this->request->get('page', 1);
        $keyword = $this->request->get('keyword', '');
        $status = $this->request->get('status', -1);

        $query = UserModel::withCount(['bookshelves', 'favorites', 'posts', 'comments']);

        if (!empty($keyword)) {
            $query->where('username', 'like', "%{$keyword}%")
                ->whereOr('nickname', 'like', "%{$keyword}%")
                ->whereOr('email', 'like', "%{$keyword}%");
        }

        if ($status >= 0) {
            $query->where('status', $status);
        }

        $users = $query->order('created_at', 'desc')
            ->paginate([
                'page' => $page,
                'list_rows' => 15,
                'query' => $this->request->get(),
            ]);

        View::assign([
            'users' => $users,
            'keyword' => $keyword,
            'status' => $status,
            'admin' => [
                'id' => session('admin_id'),
                'username' => session('admin_username'),
                'nickname' => session('admin_nickname'),
                'role' => session('admin_role'),
            ],
        ]);

        return View::fetch();
    }

    public function detail()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        $userId = $this->request->get('id', 0);
        $user = UserModel::find($userId);

        if (!$user) {
            return $this->error('用户不存在');
        }

        $stats = $user->getReadingStats($userId);
        $todayReading = ReadingRecord::getTodayReadingTime($userId);
        $weekReading = ReadingRecord::getWeekReadingTime($userId);
        $dailyStats = ReadingRecord::getDailyStats($userId, 14);

        View::assign([
            'user' => $user,
            'stats' => $stats,
            'todayReading' => $todayReading,
            'weekReading' => $weekReading,
            'dailyStats' => $dailyStats,
            'admin' => [
                'id' => session('admin_id'),
                'username' => session('admin_username'),
                'nickname' => session('admin_nickname'),
                'role' => session('admin_role'),
            ],
        ]);

        return View::fetch();
    }

    public function edit()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        $userId = $this->request->get('id', 0);
        $user = UserModel::find($userId);

        if (!$user) {
            return $this->error('用户不存在');
        }

        if ($this->request->isPost()) {
            $nickname = $this->request->post('nickname', '');
            $email = $this->request->post('email', '');
            $status = $this->request->post('status', 1);

            if (!empty($email)) {
                $existingEmail = UserModel::where('email', $email)
                    ->where('id', '<>', $userId)
                    ->find();
                if ($existingEmail) {
                    return $this->error('邮箱已被使用');
                }
                $user->email = $email;
            }

            $user->nickname = $nickname;
            $user->status = $status;
            $user->save();

            return $this->success('更新成功', null, url('/admin/user/detail', ['id' => $userId]));
        }

        View::assign([
            'user' => $user,
            'admin' => [
                'id' => session('admin_id'),
                'username' => session('admin_username'),
                'nickname' => session('admin_nickname'),
                'role' => session('admin_role'),
            ],
        ]);

        return View::fetch();
    }

    public function resetPassword()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        if ($this->request->isPost()) {
            $userId = $this->request->post('id', 0);
            $newPassword = $this->request->post('new_password', '');

            if (strlen($newPassword) < 6) {
                return $this->error('密码长度不能少于6个字符');
            }

            $user = UserModel::find($userId);
            if (!$user) {
                return $this->error('用户不存在');
            }

            $user->password = password_hash($newPassword, PASSWORD_DEFAULT);
            $user->save();

            return $this->success('密码重置成功');
        }

        return $this->error('请求方法错误');
    }

    public function toggleStatus()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        if ($this->request->isPost()) {
            $userId = $this->request->post('id', 0);

            $user = UserModel::find($userId);
            if (!$user) {
                return $this->error('用户不存在');
            }

            $user->status = $user->status == 1 ? 0 : 1;
            $user->save();

            return $this->success('状态更新成功', ['status' => $user->status]);
        }

        return $this->error('请求方法错误');
    }
}
