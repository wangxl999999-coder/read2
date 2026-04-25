<?php

namespace app\controller\admin;

use app\BaseController;
use app\model\Post as PostModel;
use app\model\Comment as CommentModel;
use app\model\Like;
use think\facade\View;

class Post extends BaseController
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

        $query = PostModel::with(['user', 'novel']);

        if (!empty($keyword)) {
            $query->where('title', 'like', "%{$keyword}%")
                ->whereOr('content', 'like', "%{$keyword}%");
        }

        if ($status >= 0) {
            $query->where('status', $status);
        }

        $posts = $query->order('created_at', 'desc')
            ->paginate([
                'page' => $page,
                'list_rows' => 15,
                'query' => $this->request->get(),
            ]);

        View::assign([
            'posts' => $posts,
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

        $postId = $this->request->get('id', 0);
        $post = PostModel::with(['user', 'novel'])->find($postId);

        if (!$post) {
            return $this->error('帖子不存在');
        }

        $comments = CommentModel::with(['user', 'replyTo'])
            ->where('post_id', $postId)
            ->order('created_at', 'desc')
            ->select();

        View::assign([
            'post' => $post,
            'comments' => $comments,
            'admin' => [
                'id' => session('admin_id'),
                'username' => session('admin_username'),
                'nickname' => session('admin_nickname'),
                'role' => session('admin_role'),
            ],
        ]);

        return View::fetch();
    }

    public function toggleStatus()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        if ($this->request->isPost()) {
            $postId = $this->request->post('id', 0);

            $post = PostModel::find($postId);
            if (!$post) {
                return $this->error('帖子不存在');
            }

            $post->status = $post->status == 1 ? 0 : 1;
            $post->save();

            return $this->success('状态更新成功', ['status' => $post->status]);
        }

        return $this->error('请求方法错误');
    }

    public function toggleTop()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        if ($this->request->isPost()) {
            $postId = $this->request->post('id', 0);

            $post = PostModel::find($postId);
            if (!$post) {
                return $this->error('帖子不存在');
            }

            $post->is_top = $post->is_top == 1 ? 0 : 1;
            $post->save();

            return $this->success('置顶状态更新成功', ['is_top' => $post->is_top]);
        }

        return $this->error('请求方法错误');
    }

    public function delete()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        $postId = $this->request->post('id', 0);
        $post = PostModel::find($postId);

        if (!$post) {
            return $this->error('帖子不存在');
        }

        $post->delete();

        return $this->success('删除成功');
    }

    public function comments()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        $page = $this->request->get('page', 1);
        $keyword = $this->request->get('keyword', '');
        $status = $this->request->get('status', -1);

        $query = CommentModel::with(['user', 'post', 'replyTo']);

        if (!empty($keyword)) {
            $query->where('content', 'like', "%{$keyword}%");
        }

        if ($status >= 0) {
            $query->where('status', $status);
        }

        $comments = $query->order('created_at', 'desc')
            ->paginate([
                'page' => $page,
                'list_rows' => 15,
                'query' => $this->request->get(),
            ]);

        View::assign([
            'comments' => $comments,
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

    public function commentToggleStatus()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        if ($this->request->isPost()) {
            $commentId = $this->request->post('id', 0);

            $comment = CommentModel::find($commentId);
            if (!$comment) {
                return $this->error('评论不存在');
            }

            $comment->status = $comment->status == 1 ? 0 : 1;
            $comment->save();

            return $this->success('状态更新成功', ['status' => $comment->status]);
        }

        return $this->error('请求方法错误');
    }

    public function commentDelete()
    {
        $needLogin = $this->needAdminLogin();
        if ($needLogin) {
            return $needLogin;
        }

        $commentId = $this->request->post('id', 0);
        $comment = CommentModel::find($commentId);

        if (!$comment) {
            return $this->error('评论不存在');
        }

        $comment->delete();

        return $this->success('删除成功');
    }
}
