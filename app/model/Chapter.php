<?php

namespace app\model;

use think\Model;

class Chapter extends Model
{
    protected $name = 'chapters';
    protected $pk = 'id';

    protected $type = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function novel()
    {
        return $this->belongsTo(Novel::class, 'novel_id');
    }

    public function readingRecords()
    {
        return $this->hasMany(ReadingRecord::class, 'chapter_id');
    }

    public static function getByNovel($novelId, $page = 1, $limit = 100)
    {
        return self::where('novel_id', $novelId)
            ->order('sort', 'asc')
            ->paginate([
                'page' => $page,
                'list_rows' => $limit,
            ]);
    }

    public static function getBySort($novelId, $sort)
    {
        return self::where('novel_id', $novelId)
            ->where('sort', $sort)
            ->find();
    }

    public static function getFirst($novelId)
    {
        return self::where('novel_id', $novelId)
            ->order('sort', 'asc')
            ->find();
    }

    public static function getLast($novelId)
    {
        return self::where('novel_id', $novelId)
            ->order('sort', 'desc')
            ->find();
    }

    public function getPrev()
    {
        return self::where('novel_id', $this->novel_id)
            ->where('sort', '<', $this->sort)
            ->order('sort', 'desc')
            ->find();
    }

    public function getNext()
    {
        return self::where('novel_id', $this->novel_id)
            ->where('sort', '>', $this->sort)
            ->order('sort', 'asc')
            ->find();
    }
}
