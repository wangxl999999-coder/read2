<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class User extends Model
{
    protected $name = 'users';
    protected $pk = 'id';

    protected $type = [
        'last_login_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function bookshelves()
    {
        return $this->hasMany(Bookshelf::class, 'user_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }

    public function readingRecords()
    {
        return $this->hasMany(ReadingRecord::class, 'user_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'user_id');
    }

    public function getReadingStats($userId)
    {
        $stats = [
            'total_reading_seconds' => 0,
            'total_books_read' => 0,
            'reading_books_count' => 0,
            'finished_books_count' => 0,
        ];

        $totalSeconds = ReadingRecord::where('user_id', $userId)
            ->sum('duration_seconds');
        $stats['total_reading_seconds'] = (int)$totalSeconds;

        $readingBooks = Bookshelf::where('user_id', $userId)
            ->where('read_progress', '<', 100)
            ->count();
        $stats['reading_books_count'] = $readingBooks;

        $finishedBooks = Bookshelf::where('user_id', $userId)
            ->where('read_progress', '>=', 100)
            ->count();
        $stats['finished_books_count'] = $finishedBooks;

        $uniqueBooks = ReadingRecord::where('user_id', $userId)
            ->distinct(true)
            ->field('novel_id')
            ->count();
        $stats['total_books_read'] = $uniqueBooks;

        return $stats;
    }
}
