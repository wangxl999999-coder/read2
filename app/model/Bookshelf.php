<?php

namespace app\model;

use think\Model;

class Bookshelf extends Model
{
    protected $name = 'bookshelves';
    protected $pk = 'id';

    protected $type = [
        'last_read_time' => 'datetime',
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

    public function lastReadChapter()
    {
        return $this->belongsTo(Chapter::class, 'last_read_chapter_id');
    }

    public static function getByUser($userId, $sortField = 'sort', $sortOrder = 'asc')
    {
        return self::with(['novel', 'lastReadChapter'])
            ->where('user_id', $userId)
            ->order($sortField, $sortOrder)
            ->select();
    }

    public static function addToShelf($userId, $novelId)
    {
        $existing = self::where('user_id', $userId)
            ->where('novel_id', $novelId)
            ->find();

        if ($existing) {
            return $existing;
        }

        $bookshelf = new self();
        $bookshelf->user_id = $userId;
        $bookshelf->novel_id = $novelId;
        $bookshelf->sort = self::where('user_id', $userId)->count() + 1;
        $bookshelf->save();

        return $bookshelf;
    }

    public static function removeFromShelf($userId, $novelId)
    {
        $bookshelf = self::where('user_id', $userId)
            ->where('novel_id', $novelId)
            ->find();

        if ($bookshelf) {
            $bookshelf->delete();
            return true;
        }

        return false;
    }

    public static function isInShelf($userId, $novelId)
    {
        return self::where('user_id', $userId)
            ->where('novel_id', $novelId)
            ->find() ? true : false;
    }

    public static function updateReadProgress($userId, $novelId, $chapterId, $progress = 0)
    {
        $bookshelf = self::where('user_id', $userId)
            ->where('novel_id', $novelId)
            ->find();

        if ($bookshelf) {
            $bookshelf->last_read_chapter_id = $chapterId;
            $bookshelf->last_read_time = date('Y-m-d H:i:s');
            if ($progress > 0) {
                $bookshelf->read_progress = $progress;
            }
            $bookshelf->save();
        }
    }

    public static function setOffline($userId, $novelId, $isOffline = true)
    {
        $bookshelf = self::where('user_id', $userId)
            ->where('novel_id', $novelId)
            ->find();

        if ($bookshelf) {
            $bookshelf->is_offline = $isOffline ? 1 : 0;
            $bookshelf->save();
        }
    }

    public static function reorder($userId, $novelId, $newSort)
    {
        $bookshelf = self::where('user_id', $userId)
            ->where('novel_id', $novelId)
            ->find();

        if ($bookshelf) {
            $oldSort = $bookshelf->sort;
            
            if ($newSort < $oldSort) {
                self::where('user_id', $userId)
                    ->where('sort', '>=', $newSort)
                    ->where('sort', '<', $oldSort)
                    ->inc('sort')
                    ->update();
            } elseif ($newSort > $oldSort) {
                self::where('user_id', $userId)
                    ->where('sort', '>', $oldSort)
                    ->where('sort', '<=', $newSort)
                    ->dec('sort')
                    ->update();
            }

            $bookshelf->sort = $newSort;
            $bookshelf->save();
        }
    }
}
