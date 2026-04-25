<?php

namespace app\model;

use think\Model;

class Category extends Model
{
    protected $name = 'categories';
    protected $pk = 'id';

    protected $type = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function novels()
    {
        return $this->hasMany(Novel::class, 'category_id');
    }

    public static function getActiveCategories()
    {
        return self::where('status', 1)
            ->order('sort', 'asc')
            ->select();
    }
}
