<?php

namespace app\controller;

use app\BaseController;
use app\model\Favorite as FavoriteModel;
use app\model\Novel;
use think\facade\View;

class Favorite extends BaseController
{
    public function index()
    {
        $needLogin = $this->needLogin();
        if ($needLogin) {
            return $needLogin;
        }

        $userId = $this->getUserId();
        $favorites = FavoriteModel::getByUser($userId);

        View::assign([
            'favorites' => $favorites,
            'isLogin' => $this->isLogin(),
            'user' => $this->getUser(),
        ]);

        return View::fetch();
    }

    public function add()
    {
        $needLogin = $this->needLogin();
        if ($needLogin) {
            return $this->error('请先登录', null, url('/user/login'));
        }

        $novelId = $this->request->post('novel_id', 0);

        if ($novelId <= 0) {
            return $this->error('参数错误');
        }

        $novel = Novel::find($novelId);
        if (!$novel) {
            return $this->error('小说不存在');
        }

        $userId = $this->getUserId();

        if (FavoriteModel::isFavorite($userId, $novelId)) {
            return $this->error('已收藏');
        }

        FavoriteModel::addFavorite($userId, $novelId);

        return $this->success('收藏成功');
    }

    public function remove()
    {
        $needLogin = $this->needLogin();
        if ($needLogin) {
            return $this->error('请先登录');
        }

        $novelId = $this->request->post('novel_id', 0);

        if ($novelId <= 0) {
            return $this->error('参数错误');
        }

        $userId = $this->getUserId();

        FavoriteModel::removeFavorite($userId, $novelId);

        return $this->success('已取消收藏');
    }

    public function toggle()
    {
        $needLogin = $this->needLogin();
        if ($needLogin) {
            return $this->error('请先登录', null, url('/user/login'));
        }

        $novelId = $this->request->post('novel_id', 0);

        if ($novelId <= 0) {
            return $this->error('参数错误');
        }

        $userId = $this->getUserId();

        if (FavoriteModel::isFavorite($userId, $novelId)) {
            FavoriteModel::removeFavorite($userId, $novelId);
            return $this->success('已取消收藏', ['is_favorite' => false]);
        } else {
            FavoriteModel::addFavorite($userId, $novelId);
            return $this->success('收藏成功', ['is_favorite' => true]);
        }
    }
}
