<?php

namespace app\model;

use think\Model;

class Novel extends Model
{
    protected $name = 'novels';
    protected $pk = 'id';

    protected $type = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class, 'novel_id')->order('sort', 'asc');
    }

    public function firstChapter()
    {
        return $this->hasOne(Chapter::class, 'novel_id')->order('sort', 'asc');
    }

    public function lastChapter()
    {
        return $this->hasOne(Chapter::class, 'novel_id')->order('sort', 'desc');
    }

    public function bookshelves()
    {
        return $this->hasMany(Bookshelf::class, 'novel_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'novel_id');
    }

    public function readingRecords()
    {
        return $this->hasMany(ReadingRecord::class, 'novel_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'novel_id');
    }

    public function getStatusTextAttr($value, $data)
    {
        return $data['status'] == 1 ? '连载中' : '已完结';
    }

    public static function getLatest($limit = 10)
    {
        return self::with('category')
            ->order('created_at', 'desc')
            ->limit($limit)
            ->select();
    }

    public static function getRecommend($limit = 10)
    {
        return self::with('category')
            ->where('is_recommend', 1)
            ->order('view_count', 'desc')
            ->limit($limit)
            ->select();
    }

    public static function getByCategory($categoryId, $page = 1, $limit = 10)
    {
        return self::with('category')
            ->where('category_id', $categoryId)
            ->order('created_at', 'desc')
            ->paginate([
                'page' => $page,
                'list_rows' => $limit,
            ]);
    }

    public static function search($keyword, $page = 1, $limit = 10)
    {
        return self::with('category')
            ->where('title', 'like', "%{$keyword}%")
            ->whereOr('author', 'like', "%{$keyword}%")
            ->order('view_count', 'desc')
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

    public function incrementFavorite()
    {
        $this->favorite_count += 1;
        $this->save();
    }

    public function decrementFavorite()
    {
        if ($this->favorite_count > 0) {
            $this->favorite_count -= 1;
            $this->save();
        }
    }
}
