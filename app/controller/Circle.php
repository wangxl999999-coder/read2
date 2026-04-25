<?php

namespace app\controller;

use app\BaseController;
use app\model\Post;
use app\model\Comment;
use app\model\Like;
use app\model\Novel;
use app\model\Category;
use think\facade\View;

class Circle extends BaseController
{
    public function index()
    {
        $page = $this->request->get('page', 1);
        $novelId = $this->request->get('novel_id', 0);

        $posts = Post::getList($page, 10, $novelId > 0 ? $novelId : null);
        $categories = Category::getActiveCategories();

        $novel = null;
        if ($novelId > 0) {
            $novel = Novel::find($novelId);
        }

        View::assign([
            'posts' => $posts,
            'novel' => $novel,
            'novelId' => $novelId,
            'categories' => $categories,
            'isLogin' => $this->isLogin(),
            'user' => $this->isLogin() ? $this->getUser() : null,
        ]);

        return View::fetch();
    }

    public function post()
    {
        $needLogin = $this->needLogin();
        if ($needLogin) {
            return $needLogin;
        }

        if ($this->request->isPost()) {
            $title = $this->request->post('title', '');
            $content = $this->request->post('content', '');
            $novelId = $this->request->post('novel_id', 0);

            if (empty($title) || empty($content)) {
                return $this->error('请填写标题和内容');
            }

            $post = new Post();
            $post->user_id = $this->getUserId();
            $post->title = $title;
            $post->content = $content;
            if ($novelId > 0) {
                $post->novel_id = $novelId;
            }
            $post->save();

            return $this->success('发布成功', null, url('/circle/detail', ['id' => $post->id]));
        }

        $novels = [];
        if ($this->isLogin()) {
            $userId = $this->getUserId();
            $novels = Novel::whereHas('bookshelves', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })->select();
        }

        $categories = Category::getActiveCategories();

        View::assign([
            'novels' => $novels,
            'categories' => $categories,
            'isLogin' => $this->isLogin(),
            'user' => $this->getUser(),
        ]);

        return View::fetch();
    }

    public function detail()
    {
        $postId = $this->request->get('id', 0);

        if ($postId <= 0) {
            return redirect(url('/circle'));
        }

        $post = Post::with(['user', 'novel', 'topComments'])->find($postId);

        if (!$post) {
            return redirect(url('/circle'));
        }

        $post->incrementView();

        $comments = Comment::getByPost($postId, 1, 20);

        $isLiked = false;
        if ($this->isLogin()) {
            $isLiked = Like::isLiked($this->getUserId(), 'post', $postId);
        }

        $categories = Category::getActiveCategories();

        View::assign([
            'post' => $post,
            'comments' => $comments,
            'isLiked' => $isLiked,
            'categories' => $categories,
            'isLogin' => $this->isLogin(),
            'user' => $this->isLogin() ? $this->getUser() : null,
        ]);

        return View::fetch();
    }

    public function comment()
    {
        $needLogin = $this->needLogin();
        if ($needLogin) {
            return $this->error('请先登录');
        }

        if ($this->request->isPost()) {
            $postId = $this->request->post('post_id', 0);
            $parentId = $this->request->post('parent_id', 0);
            $replyToUserId = $this->request->post('reply_to_user_id', 0);
            $content = $this->request->post('content', '');

            if ($postId <= 0 || empty($content)) {
                return $this->error('参数错误');
            }

            $comment = new Comment();
            $comment->post_id = $postId;
            $comment->user_id = $this->getUserId();
            $comment->content = $content;
            if ($parentId > 0) {
                $comment->parent_id = $parentId;
            }
            if ($replyToUserId > 0) {
                $comment->reply_to_user_id = $replyToUserId;
            }
            $comment->save();

            $post = Post::find($postId);
            if ($post) {
                $post->incrementComment();
            }

            return $this->success('评论成功', null, url('/circle/detail', ['id' => $postId]));
        }

        return $this->error('请求方法错误');
    }

    public function like()
    {
        $needLogin = $this->needLogin();
        if ($needLogin) {
            return $this->error('请先登录');
        }

        if ($this->request->isPost()) {
            $targetType = $this->request->post('target_type', 'post');
            $targetId = $this->request->post('target_id', 0);

            if ($targetId <= 0) {
                return $this->error('参数错误');
            }

            $userId = $this->getUserId();

            if (Like::isLiked($userId, $targetType, $targetId)) {
                Like::removeLike($userId, $targetType, $targetId);
                return $this->success('已取消点赞', ['is_liked' => false]);
            } else {
                Like::addLike($userId, $targetType, $targetId);
                return $this->success('点赞成功', ['is_liked' => true]);
            }
        }

        return $this->error('请求方法错误');
    }
}
