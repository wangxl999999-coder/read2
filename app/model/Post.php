<?php

namespace app\model;

use think\Model;

class Post extends Model
{
    protected $name = 'posts';
    protected $pk = 'id';

    protected $type = [
        'images' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function novel()
    {
        return $this->belongsTo(Novel::class, 'novel_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    public function topComments()
    {
        return $this->hasMany(Comment::class, 'post_id')
            ->where('parent_id', null)
            ->order('created_at', 'desc')
            ->limit(10);
    }

    public static function getList($page = 1, $limit = 10, $novelId = null)
    {
        $query = self::with(['user', 'novel'])
            ->where('status', 1)
            ->order('is_top', 'desc')
            ->order('created_at', 'desc');

        if ($novelId) {
            $query->where('novel_id', $novelId);
        }

        return $query->paginate([
            'page' => $page,
            'list_rows' => $limit,
        ]);
    }

    public static function getByUser($userId, $page = 1, $limit = 10)
    {
        return self::with(['novel'])
            ->where('user_id', $userId)
            ->order('created_at', 'desc')
            ->paginate([
                'page' => $page,
                'list_rows' => $limit,
            ]);
    }

    public function incrementView()
    {
        $this->view_count += 1;
        $this->save();
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

    public function incrementComment()
    {
        $this->comment_count += 1;
        $this->save();
    }
}
