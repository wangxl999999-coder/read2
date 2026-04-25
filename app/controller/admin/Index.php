<?php

namespace app\controller\admin;

use app\BaseController;
use app\model\Novel;
use app\model\User;
use app\model\Post;
use app\model\ReadingRecord;
use think\facade\View;

class Index extends BaseController
{
    public function index()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        $totalNovels = Novel::count();
        $totalUsers = User::count();
        $totalPosts = Post::count();
        
        $today = date('Y-m-d');
        $todayUsers = User::whereDate('created_at', $today)->count();
        $todayPosts = Post::whereDate('created_at', $today)->count();

        $todayStart = $today . ' 00:00:00';
        $todayEnd = $today . ' 23:59:59';
        $todayReadingSeconds = ReadingRecord::whereBetween('start_time', [$todayStart, $todayEnd])
            ->sum('duration_seconds');

        $latestNovels = Novel::with('category')
            ->order('created_at', 'desc')
            ->limit(10)
            ->select();

        $latestUsers = User::order('created_at', 'desc')
            ->limit(10)
            ->select();

        View::assign([
            'totalNovels' => $totalNovels,
            'totalUsers' => $totalUsers,
            'totalPosts' => $totalPosts,
            'todayUsers' => $todayUsers,
            'todayPosts' => $todayPosts,
            'todayReadingSeconds' => (int)$todayReadingSeconds,
            'latestNovels' => $latestNovels,
            'latestUsers' => $latestUsers,
            'admin' => [
                'id' => session('admin_id'),
                'username' => session('admin_username'),
                'nickname' => session('admin_nickname'),
                'role' => session('admin_role'),
            ],
        ]);

        return View::fetch();
    }
}
