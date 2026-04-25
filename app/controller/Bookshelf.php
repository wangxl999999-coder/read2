<?php

namespace app\controller;

use app\BaseController;
use app\model\Bookshelf as BookshelfModel;
use app\model\Novel;
use app\model\Chapter;
use app\model\Favorite;
use think\facade\View;

class Bookshelf extends BaseController
{
    public function index()
    {
        $needLogin = $this->needLogin();
        if ($needLogin) {
            return $needLogin;
        }

        $userId = $this->getUserId();
        $sortField = $this->request->get('sort', 'sort');
        $sortOrder = $this->request->get('order', 'asc');

        $allowedSorts = ['sort', 'last_read_time', 'read_progress'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'sort';
        }

        $books = BookshelfModel::getByUser($userId, $sortField, $sortOrder);

        View::assign([
            'books' => $books,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
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

        if (BookshelfModel::isInShelf($userId, $novelId)) {
            return $this->error('已在书架中');
        }

        BookshelfModel::addToShelf($userId, $novelId);

        return $this->success('已加入书架');
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

        BookshelfModel::removeFromShelf($userId, $novelId);

        return $this->success('已从书架移除');
    }

    public function reorder()
    {
        $needLogin = $this->needLogin();
        if ($needLogin) {
            return $this->error('请先登录');
        }

        $novelId = $this->request->post('novel_id', 0);
        $newSort = $this->request->post('new_sort', 0);

        if ($novelId <= 0 || $newSort <= 0) {
            return $this->error('参数错误');
        }

        $userId = $this->getUserId();

        BookshelfModel::reorder($userId, $novelId, $newSort);

        return $this->success('排序已更新');
    }

    public function download()
    {
        $needLogin = $this->needLogin();
        if ($needLogin) {
            return $this->error('请先登录');
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

        if (!BookshelfModel::isInShelf($userId, $novelId)) {
            return $this->error('请先将小说加入书架');
        }

        BookshelfModel::setOffline($userId, $novelId, true);

        $chapters = Chapter::where('novel_id', $novelId)
            ->order('sort', 'asc')
            ->select();

        $content = $novel->title . "\n" . str_repeat('=', 50) . "\n\n";
        $content .= "作者：" . $novel->author . "\n";
        $content .= "简介：" . $novel->description . "\n\n";
        $content .= str_repeat('=', 50) . "\n\n";

        foreach ($chapters as $chapter) {
            $content .= "第" . $chapter->sort . "章 " . $chapter->title . "\n\n";
            $content .= $chapter->content . "\n\n";
            $content .= str_repeat('-', 30) . "\n\n";
        }

        $filename = $novel->title . '.txt';
        $filepath = runtime_path() . 'download/' . $filename;

        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        file_put_contents($filepath, $content);

        return $this->success('下载已准备', [
            'url' => url('/bookshelf/downloadFile', ['filename' => $filename]),
            'filename' => $filename
        ]);
    }

    public function downloadFile()
    {
        $needLogin = $this->needLogin();
        if ($needLogin) {
            return $needLogin;
        }

        $filename = $this->request->get('filename', '');
        $filepath = runtime_path() . 'download/' . $filename;

        if (!file_exists($filepath)) {
            return $this->error('文件不存在');
        }

        return download($filepath, $filename);
    }
}
