<?php

namespace app\controller\admin;

use app\BaseController;
use app\model\Category as CategoryModel;
use think\facade\View;

class Category extends BaseController
{
    public function index()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        $categories = CategoryModel::order('sort', 'asc')
            ->withCount('novels')
            ->select();

        View::assign([
            'categories' => $categories,
            'admin' => [
                'id' => session('admin_id'),
                'username' => session('admin_username'),
                'nickname' => session('admin_nickname'),
                'role' => session('admin_role'),
            ],
        ]);

        return View::fetch();
    }

    public function create()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        if ($this->request->isPost()) {
            $name = $this->request->post('name', '');
            $icon = $this->request->post('icon', '');
            $sort = $this->request->post('sort', 0);
            $status = $this->request->post('status', 1);

            if (empty($name)) {
                return $this->error('请填写分类名称');
            }

            $category = new CategoryModel();
            $category->name = $name;
            $category->icon = $icon;
            $category->sort = $sort;
            $category->status = $status;
            $category->save();

            return $this->success('添加成功', null, url('/admin/category/index'));
        }

        $maxSort = CategoryModel::count() + 1;

        View::assign([
            'maxSort' => $maxSort,
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

        $categoryId = $this->request->get('id', 0);
        $category = CategoryModel::find($categoryId);

        if (!$category) {
            return $this->error('分类不存在');
        }

        if ($this->request->isPost()) {
            $name = $this->request->post('name', '');
            $icon = $this->request->post('icon', '');
            $sort = $this->request->post('sort', 0);
            $status = $this->request->post('status', 1);

            if (empty($name)) {
                return $this->error('请填写分类名称');
            }

            $category->name = $name;
            $category->icon = $icon;
            $category->sort = $sort;
            $category->status = $status;
            $category->save();

            return $this->success('更新成功', null, url('/admin/category/index'));
        }

        View::assign([
            'category' => $category,
            'admin' => [
                'id' => session('admin_id'),
                'username' => session('admin_username'),
                'nickname' => session('admin_nickname'),
                'role' => session('admin_role'),
            ],
        ]);

        return View::fetch();
    }

    public function delete()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        $categoryId = $this->request->post('id', 0);
        $category = CategoryModel::withCount('novels')->find($categoryId);

        if (!$category) {
            return $this->error('分类不存在');
        }

        if ($category->novels_count > 0) {
            return $this->error('该分类下还有小说，无法删除');
        }

        $category->delete();

        return $this->success('删除成功');
    }

    public function reorder()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        if ($this->request->isPost()) {
            $orders = $this->request->post('orders', []);

            foreach ($orders as $item) {
                $category = CategoryModel::find($item['id']);
                if ($category) {
                    $category->sort = $item['sort'];
                    $category->save();
                }
            }

            return $this->success('排序成功');
        }

        return $this->error('请求方法错误');
    }
}
