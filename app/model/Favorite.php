<?php

namespace app\model;

use think\Model;

class Favorite extends Model
{
    protected $name = 'favorites';
    protected $pk = 'id';

    protected $type = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function novel()
    {
        return $this->belongsTo(Novel::class, 'novel_id');
    }

    public static function getByUser($userId)
    {
        return self::with('novel')
            ->where('user_id', $userId)
            ->order('created_at', 'desc')
            ->select();
    }

    public static function addFavorite($userId, $novelId)
    {
        $existing = self::where('user_id', $userId)
            ->where('novel_id', $novelId)
            ->find();

        if ($existing) {
            return false;
        }

        $favorite = new self();
        $favorite->user_id = $userId;
        $favorite->novel_id = $novelId;
        $favorite->save();

        $novel = Novel::find($novelId);
        if ($novel) {
            $novel->incrementFavorite();
        }

        return true;
    }

    public static function removeFavorite($userId, $novelId)
    {
        $favorite = self::where('user_id', $userId)
            ->where('novel_id', $novelId)
            ->find();

        if ($favorite) {
            $favorite->delete();

            $novel = Novel::find($novelId);
            if ($novel) {
                $novel->decrementFavorite();
            }

            return true;
        }

        return false;
    }

    public static function isFavorite($userId, $novelId)
    {
        return self::where('user_id', $userId)
            ->where('novel_id', $novelId)
            ->find() ? true : false;
    }
}
