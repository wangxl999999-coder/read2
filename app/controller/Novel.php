<?php

namespace app\controller;

use app\BaseController;
use app\model\Category;
use app\model\Novel as NovelModel;
use app\model\Chapter;
use app\model\Bookshelf;
use app\model\Favorite;
use think\facade\View;

class Novel extends BaseController
{
    public function detail()
    {
        $novelId = $this->request->get('id', 0);

        if ($novelId <= 0) {
            return redirect(url('/'));
        }

        $novel = NovelModel::with(['category', 'firstChapter', 'lastChapter'])->find($novelId);

        if (!$novel) {
            return redirect(url('/'));
        }

        $novel->incrementView();

        $chapters = Chapter::where('novel_id', $novelId)
            ->order('sort', 'asc')
            ->select();

        $isInShelf = false;
        $isFavorite = false;

        if ($this->isLogin()) {
            $userId = $this->getUserId();
            $isInShelf = Bookshelf::isInShelf($userId, $novelId);
            $isFavorite = Favorite::isFavorite($userId, $novelId);
        }

        $categories = Category::getActiveCategories();

        $recommendNovels = NovelModel::where('category_id', $novel->category_id)
            ->where('id', '<>', $novelId)
            ->order('view_count', 'desc')
            ->limit(6)
            ->select();

        View::assign([
            'categories' => $categories,
            'novel' => $novel,
            'chapters' => $chapters,
            'isInShelf' => $isInShelf,
            'isFavorite' => $isFavorite,
            'recommendNovels' => $recommendNovels,
            'isLogin' => $this->isLogin(),
            'user' => $this->isLogin() ? $this->getUser() : null,
        ]);

        return View::fetch();
    }

    public function chapters()
    {
        $novelId = $this->request->get('id', 0);
        $page = $this->request->get('page', 1);

        if ($novelId <= 0) {
            return redirect(url('/'));
        }

        $novel = NovelModel::find($novelId);
        if (!$novel) {
            return redirect(url('/'));
        }

        $chapters = Chapter::getByNovel($novelId, $page, 50);
        $categories = Category::getActiveCategories();

        View::assign([
            'categories' => $categories,
            'novel' => $novel,
            'chapters' => $chapters,
            'isLogin' => $this->isLogin(),
            'user' => $this->isLogin() ? $this->getUser() : null,
        ]);

        return View::fetch();
    }
}
