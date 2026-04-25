<?php

namespace app\model;

use think\Model;

class Like extends Model
{
    protected $name = 'likes';
    protected $pk = 'id';

    protected $type = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function addLike($userId, $targetType, $targetId)
    {
        $existing = self::where('user_id', $userId)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->find();

        if ($existing) {
            return false;
        }

        $like = new self();
        $like->user_id = $userId;
        $like->target_type = $targetType;
        $like->target_id = $targetId;
        $like->save();

        if ($targetType == 'post') {
            $post = Post::find($targetId);
            if ($post) {
                $post->incrementLike();
            }
        } elseif ($targetType == 'comment') {
            $comment = Comment::find($targetId);
            if ($comment) {
                $comment->incrementLike();
            }
        }

        return true;
    }

    public static function removeLike($userId, $targetType, $targetId)
    {
        $like = self::where('user_id', $userId)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->find();

        if ($like) {
            $like->delete();

            if ($targetType == 'post') {
                $post = Post::find($targetId);
                if ($post) {
                    $post->decrementLike();
                }
            } elseif ($targetType == 'comment') {
                $comment = Comment::find($targetId);
                if ($comment) {
                    $comment->decrementLike();
                }
            }

            return true;
        }

        return false;
    }

    public static function isLiked($userId, $targetType, $targetId)
    {
        return self::where('user_id', $userId)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->find() ? true : false;
    }
}
