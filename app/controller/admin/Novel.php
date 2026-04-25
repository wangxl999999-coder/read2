<?php

namespace app\controller\admin;

use app\BaseController;
use app\model\Novel as NovelModel;
use app\model\Category;
use app\model\Chapter;
use think\facade\View;

class Novel extends BaseController
{
    public function index()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        $page = $this->request->get('page', 1);
        $keyword = $this->request->get('keyword', '');
        $categoryId = $this->request->get('category_id', 0);
        $status = $this->request->get('status', -1);

        $query = NovelModel::with('category');

        if (!empty($keyword)) {
            $query->where('title', 'like', "%{$keyword}%")
                ->whereOr('author', 'like', "%{$keyword}%");
        }

        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        if ($status >= 0) {
            $query->where('status', $status);
        }

        $novels = $query->order('created_at', 'desc')
            ->paginate([
                'page' => $page,
                'list_rows' => 15,
                'query' => $this->request->get(),
            ]);

        $categories = Category::select();

        View::assign([
            'novels' => $novels,
            'categories' => $categories,
            'keyword' => $keyword,
            'categoryId' => $categoryId,
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

    public function create()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        if ($this->request->isPost()) {
            $data = [
                'title' => $this->request->post('title', ''),
                'author' => $this->request->post('author', ''),
                'cover' => $this->request->post('cover', ''),
                'category_id' => $this->request->post('category_id', 0),
                'description' => $this->request->post('description', ''),
                'status' => $this->request->post('status', 1),
                'is_recommend' => $this->request->post('is_recommend', 0),
                'is_top' => $this->request->post('is_top', 0),
            ];

            if (empty($data['title']) || empty($data['author'])) {
                return $this->error('请填写小说名称和作者');
            }

            if ($data['category_id'] <= 0) {
                return $this->error('请选择分类');
            }

            $novel = new NovelModel();
            $novel->save($data);

            return $this->success('添加成功', null, url('/admin/novel/index'));
        }

        $categories = Category::select();

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

    public function edit()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        $novelId = $this->request->get('id', 0);
        $novel = NovelModel::find($novelId);

        if (!$novel) {
            return $this->error('小说不存在');
        }

        if ($this->request->isPost()) {
            $data = [
                'title' => $this->request->post('title', ''),
                'author' => $this->request->post('author', ''),
                'cover' => $this->request->post('cover', ''),
                'category_id' => $this->request->post('category_id', 0),
                'description' => $this->request->post('description', ''),
                'status' => $this->request->post('status', 1),
                'is_recommend' => $this->request->post('is_recommend', 0),
                'is_top' => $this->request->post('is_top', 0),
            ];

            if (empty($data['title']) || empty($data['author'])) {
                return $this->error('请填写小说名称和作者');
            }

            $novel->save($data);

            return $this->success('更新成功', null, url('/admin/novel/index'));
        }

        $categories = Category::select();

        View::assign([
            'novel' => $novel,
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

    public function delete()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        $novelId = $this->request->post('id', 0);
        $novel = NovelModel::find($novelId);

        if (!$novel) {
            return $this->error('小说不存在');
        }

        $novel->delete();

        return $this->success('删除成功');
    }

    public function chapters()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        $novelId = $this->request->get('novel_id', 0);
        $page = $this->request->get('page', 1);

        $novel = NovelModel::find($novelId);
        if (!$novel) {
            return $this->error('小说不存在');
        }

        $chapters = Chapter::where('novel_id', $novelId)
            ->order('sort', 'asc')
            ->paginate([
                'page' => $page,
                'list_rows' => 30,
            ]);

        View::assign([
            'novel' => $novel,
            'chapters' => $chapters,
            'admin' => [
                'id' => session('admin_id'),
                'username' => session('admin_username'),
                'nickname' => session('admin_nickname'),
                'role' => session('admin_role'),
            ],
        ]);

        return View::fetch();
    }

    public function chapterCreate()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        $novelId = $this->request->get('novel_id', 0);
        $novel = NovelModel::find($novelId);

        if (!$novel) {
            return $this->error('小说不存在');
        }

        if ($this->request->isPost()) {
            $title = $this->request->post('title', '');
            $content = $this->request->post('content', '');
            $sort = $this->request->post('sort', 0);
            $isVip = $this->request->post('is_vip', 0);

            if (empty($title) || empty($content)) {
                return $this->error('请填写章节标题和内容');
            }

            if ($sort <= 0) {
                $sort = Chapter::where('novel_id', $novelId)->count() + 1;
            }

            $chapter = new Chapter();
            $chapter->novel_id = $novelId;
            $chapter->title = $title;
            $chapter->content = $content;
            $chapter->sort = $sort;
            $chapter->is_vip = $isVip;
            $chapter->word_count = mb_strlen(strip_tags($content));
            $chapter->save();

            $novel->chapter_count += 1;
            $novel->word_count += $chapter->word_count;
            $novel->save();

            return $this->success('添加成功', null, url('/admin/novel/chapters', ['novel_id' => $novelId]));
        }

        $maxSort = Chapter::where('novel_id', $novelId)->count() + 1;

        View::assign([
            'novel' => $novel,
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

    public function chapterEdit()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        $chapterId = $this->request->get('id', 0);
        $chapter = Chapter::with('novel')->find($chapterId);

        if (!$chapter) {
            return $this->error('章节不存在');
        }

        if ($this->request->isPost()) {
            $title = $this->request->post('title', '');
            $content = $this->request->post('content', '');
            $sort = $this->request->post('sort', 0);
            $isVip = $this->request->post('is_vip', 0);

            if (empty($title) || empty($content)) {
                return $this->error('请填写章节标题和内容');
            }

            $oldWordCount = $chapter->word_count;
            $newWordCount = mb_strlen(strip_tags($content));

            $chapter->title = $title;
            $chapter->content = $content;
            $chapter->sort = $sort > 0 ? $sort : $chapter->sort;
            $chapter->is_vip = $isVip;
            $chapter->word_count = $newWordCount;
            $chapter->save();

            if ($oldWordCount != $newWordCount) {
                $novel = NovelModel::find($chapter->novel_id);
                $novel->word_count += ($newWordCount - $oldWordCount);
                $novel->save();
            }

            return $this->success('更新成功', null, url('/admin/novel/chapters', ['novel_id' => $chapter->novel_id]));
        }

        View::assign([
            'chapter' => $chapter,
            'novel' => $chapter->novel,
            'admin' => [
                'id' => session('admin_id'),
                'username' => session('admin_username'),
                'nickname' => session('admin_nickname'),
                'role' => session('admin_role'),
            ],
        ]);

        return View::fetch();
    }

    public function chapterDelete()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        $chapterId = $this->request->post('id', 0);
        $chapter = Chapter::find($chapterId);

        if (!$chapter) {
            return $this->error('章节不存在');
        }

        $novelId = $chapter->novel_id;
        $wordCount = $chapter->word_count;

        $chapter->delete();

        $novel = NovelModel::find($novelId);
        if ($novel) {
            $novel->chapter_count = max(0, $novel->chapter_count - 1);
            $novel->word_count = max(0, $novel->word_count - $wordCount);
            $novel->save();
        }

        return $this->success('删除成功');
    }
}
