<?php

namespace app\controller;

use app\BaseController;
use app\model\Category;
use app\model\Novel;
use think\facade\View;

class Index extends BaseController
{
    public function index()
    {
        $categories = Category::getActiveCategories();
        $latestNovels = Novel::getLatest(12);
        $recommendNovels = Novel::getRecommend(12);

        $categoryNovels = [];
        foreach ($categories as $category) {
            $categoryNovels[$category->id] = Novel::where('category_id', $category->id)
                ->order('view_count', 'desc')
                ->limit(6)
                ->select();
        }

        View::assign([
            'categories' => $categories,
            'latestNovels' => $latestNovels,
            'recommendNovels' => $recommendNovels,
            'categoryNovels' => $categoryNovels,
            'isLogin' => $this->isLogin(),
            'user' => $this->isLogin() ? $this->getUser() : null,
        ]);

        return View::fetch();
    }

    public function category()
    {
        $categoryId = $this->request->get('id', 0);
        $page = $this->request->get('page', 1);
        $limit = 20;

        $categories = Category::getActiveCategories();
        $currentCategory = null;

        if ($categoryId > 0) {
            $currentCategory = Category::find($categoryId);
        }

        $novels = Novel::getByCategory($categoryId, $page, $limit);

        View::assign([
            'categories' => $categories,
            'currentCategory' => $currentCategory,
            'novels' => $novels,
            'isLogin' => $this->isLogin(),
            'user' => $this->isLogin() ? $this->getUser() : null,
        ]);

        return View::fetch();
    }

    public function search()
    {
        $keyword = $this->request->get('keyword', '');
        $page = $this->request->get('page', 1);
        $limit = 20;

        $categories = Category::getActiveCategories();
        $novels = collect([]);

        if (!empty($keyword)) {
            $novels = Novel::search($keyword, $page, $limit);
        }

        View::assign([
            'categories' => $categories,
            'keyword' => $keyword,
            'novels' => $novels,
            'isLogin' => $this->isLogin(),
            'user' => $this->isLogin() ? $this->getUser() : null,
        ]);

        return View::fetch();
    }
}
