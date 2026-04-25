<?php

namespace app\model;

use think\Model;

class Comment extends Model
{
    protected $name = 'comments';
    protected $pk = 'id';

    protected $type = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replyTo()
    {
        return $this->belongsTo(User::class, 'reply_to_user_id');
    }

    public function children()
    {
        return $this->hasMany(Comment::class, 'parent_id')->order('created_at', 'asc');
    }

    public static function getByPost($postId, $page = 1, $limit = 20)
    {
        return self::with(['user', 'replyTo'])
            ->where('post_id', $postId)
            ->where('parent_id', null)
            ->order('created_at', 'desc')
            ->paginate([
                'page' => $page,
                'list_rows' => $limit,
            ]);
    }

    public static function getByUser($userId, $page = 1, $limit = 10)
    {
        return self::with(['post', 'user'])
            ->where('user_id', $userId)
            ->order('created_at', 'desc')
            ->paginate([
                'page' => $page,
                'list_rows' => $limit,
            ]);
    }

    public function incrementLike()
    {
        $this->like_count += 1;
        $this->save();
    }

    public function decrementLike()
    {
        if ($this->like_count > 0) {
            $this->like_count -= 1;
            $this->save();
        }
    }
}
