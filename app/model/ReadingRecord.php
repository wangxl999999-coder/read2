<?php

namespace app\model;

use think\Model;

class ReadingRecord extends Model
{
    protected $name = 'reading_records';
    protected $pk = 'id';

    protected $type = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
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

    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    public static function startReading($userId, $novelId, $chapterId)
    {
        $record = new self();
        $record->user_id = $userId;
        $record->novel_id = $novelId;
        $record->chapter_id = $chapterId;
        $record->start_time = date('Y-m-d H:i:s');
        $record->save();

        return $record;
    }

    public static function endReading($recordId, $wordsRead = 0)
    {
        $record = self::find($recordId);
        if ($record) {
            $endTime = date('Y-m-d H:i:s');
            $duration = strtotime($endTime) - strtotime($record->start_time);
            
            $record->end_time = $endTime;
            $record->duration_seconds = $duration;
            $record->words_read = $wordsRead;
            $record->save();

            return $record;
        }

        return null;
    }

    public static function getTodayReadingTime($userId)
    {
        $start = date('Y-m-d 00:00:00');
        $end = date('Y-m-d 23:59:59');

        $totalSeconds = self::where('user_id', $userId)
            ->whereBetween('start_time', [$start, $end])
            ->sum('duration_seconds');

        return (int)$totalSeconds;
    }

    public static function getWeekReadingTime($userId)
    {
        $start = date('Y-m-d 00:00:00', strtotime('-7 days'));
        $end = date('Y-m-d 23:59:59');

        $totalSeconds = self::where('user_id', $userId)
            ->whereBetween('start_time', [$start, $end])
            ->sum('duration_seconds');

        return (int)$totalSeconds;
    }

    public static function getMonthReadingTime($userId)
    {
        $start = date('Y-m-01 00:00:00');
        $end = date('Y-m-t 23:59:59');

        $totalSeconds = self::where('user_id', $userId)
            ->whereBetween('start_time', [$start, $end])
            ->sum('duration_seconds');

        return (int)$totalSeconds;
    }

    public static function getDailyStats($userId, $days = 7)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        $endDate = date('Y-m-d');

        $records = self::where('user_id', $userId)
            ->whereBetween('start_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select();

        $stats = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $stats[$date] = 0;
        }

        foreach ($records as $record) {
            $date = date('Y-m-d', strtotime($record->start_time));
            if (isset($stats[$date])) {
                $stats[$date] += $record->duration_seconds;
            }
        }

        return $stats;
    }
}
