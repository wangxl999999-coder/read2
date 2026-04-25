<?php

namespace app\controller;

use app\BaseController;
use app\model\Novel as NovelModel;
use app\model\Chapter;
use app\model\Bookshelf;
use app\model\Favorite;
use app\model\ReadingRecord;
use think\facade\View;
use think\facade\Session;

class Reader extends BaseController
{
    protected $currentRecordId = null;

    public function index()
    {
        $novelId = $this->request->get('novel_id', 0);
        $chapterId = $this->request->get('chapter_id', 0);

        if ($novelId <= 0) {
            return redirect(url('/'));
        }

        $novel = NovelModel::find($novelId);
        if (!$novel) {
            return redirect(url('/'));
        }

        $chapter = null;
        if ($chapterId > 0) {
            $chapter = Chapter::with('novel')->find($chapterId);
        } else {
            $chapter = Chapter::getFirst($novelId);
        }

        if (!$chapter) {
            return redirect(url('/novel/detail', ['id' => $novelId]));
        }

        $prevChapter = $chapter->getPrev();
        $nextChapter = $chapter->getNext();

        $isInShelf = false;
        $isFavorite = false;

        if ($this->isLogin()) {
            $userId = $this->getUserId();
            $isInShelf = Bookshelf::isInShelf($userId, $novelId);
            $isFavorite = Favorite::isFavorite($userId, $novelId);

            $record = ReadingRecord::startReading($userId, $novelId, $chapter->id);
            Session::set('reading_record_id', $record->id);

            $totalChapters = Chapter::where('novel_id', $novelId)->count();
            $progress = 0;
            if ($totalChapters > 0) {
                $progress = round(($chapter->sort / $totalChapters) * 100, 2);
            }

            if ($isInShelf) {
                Bookshelf::updateReadProgress($userId, $novelId, $chapter->id, $progress);
            }
        }

        $chapters = Chapter::where('novel_id', $novelId)
            ->order('sort', 'asc')
            ->select();

        $readingSettings = $this->getReadingSettings();

        View::assign([
            'novel' => $novel,
            'chapter' => $chapter,
            'prevChapter' => $prevChapter,
            'nextChapter' => $nextChapter,
            'chapters' => $chapters,
            'isInShelf' => $isInShelf,
            'isFavorite' => $isFavorite,
            'readingSettings' => $readingSettings,
            'isLogin' => $this->isLogin(),
            'user' => $this->isLogin() ? $this->getUser() : null,
        ]);

        return View::fetch();
    }

    public function saveProgress()
    {
        if (!$this->isLogin()) {
            return $this->error('请先登录');
        }

        $novelId = $this->request->post('novel_id', 0);
        $chapterId = $this->request->post('chapter_id', 0);
        $progress = $this->request->post('progress', 0);

        if ($novelId <= 0 || $chapterId <= 0) {
            return $this->error('参数错误');
        }

        $userId = $this->getUserId();

        if (Bookshelf::isInShelf($userId, $novelId)) {
            Bookshelf::updateReadProgress($userId, $novelId, $chapterId, $progress);
        }

        return $this->success('进度已保存');
    }

    public function endReading()
    {
        $recordId = Session::get('reading_record_id');
        if ($recordId && $this->isLogin()) {
            $wordsRead = $this->request->post('words_read', 0);
            ReadingRecord::endReading($recordId, $wordsRead);
            Session::delete('reading_record_id');
        }

        return $this->success();
    }

    public function saveSettings()
    {
        if ($this->request->isPost()) {
            $settings = [
                'font_size' => $this->request->post('font_size', 18),
                'line_height' => $this->request->post('line_height', 1.8),
                'theme' => $this->request->post('theme', 'default'),
                'bg_color' => $this->request->post('bg_color', '#ffffff'),
                'text_color' => $this->request->post('text_color', '#333333'),
                'brightness' => $this->request->post('brightness', 100),
                'font_family' => $this->request->post('font_family', 'system'),
            ];

            $this->saveReadingSettings($settings);

            return $this->success('设置已保存');
        }

        return $this->error('请求方法错误');
    }

    protected function getReadingSettings()
    {
        $default = [
            'font_size' => 18,
            'line_height' => 1.8,
            'theme' => 'default',
            'bg_color' => '#ffffff',
            'text_color' => '#333333',
            'brightness' => 100,
            'font_family' => 'system',
        ];

        if ($this->isLogin()) {
            $userId = $this->getUserId();
            $cookieKey = 'reading_settings_' . $userId;
            $settings = cookie($cookieKey);
            if ($settings) {
                return array_merge($default, json_decode($settings, true));
            }
        } else {
            $settings = cookie('reading_settings');
            if ($settings) {
                return array_merge($default, json_decode($settings, true));
            }
        }

        return $default;
    }

    protected function saveReadingSettings($settings)
    {
        $expire = 365 * 24 * 3600;

        if ($this->isLogin()) {
            $userId = $this->getUserId();
            cookie('reading_settings_' . $userId, json_encode($settings), $expire);
        } else {
            cookie('reading_settings', json_encode($settings), $expire);
        }
    }
}
