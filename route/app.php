<?php

use think\facade\Route;

Route::get('/', 'index/index');
Route::get('category/:id', 'index/category');
Route::get('search', 'index/search');

Route::get('user/login', 'user/login');
Route::post('user/login', 'user/doLogin');
Route::get('user/register', 'user/register');
Route::post('user/register', 'user/doRegister');
Route::get('user/logout', 'user/logout');
Route::get('user/profile', 'user/profile');
Route::get('user/stats', 'user/stats');

Route::get('novel/detail/:id', 'novel/detail');
Route::get('novel/chapters/:id', 'novel/chapters');

Route::get('reader/:novel_id/:chapter_id', 'reader/index');

Route::get('bookshelf', 'bookshelf/index');
Route::post('bookshelf/add', 'bookshelf/add');
Route::post('bookshelf/remove', 'bookshelf/remove');
Route::post('bookshelf/updateReading', 'bookshelf/updateReading');

Route::get('favorite', 'favorite/index');
Route::post('favorite/add', 'favorite/add');
Route::post('favorite/remove', 'favorite/remove');

Route::get('circle', 'circle/index');
Route::get('circle/post', 'circle/post');
Route::post('circle/post', 'circle/doPost');
Route::get('circle/detail/:id', 'circle/detail');
Route::post('circle/comment', 'circle/comment');
Route::post('circle/like', 'circle/like');
Route::post('circle/unlike', 'circle/unlike');

Route::group('admin', function () {
    Route::get('login', 'admin.login/index');
    Route::post('login', 'admin.login/doLogin');
    Route::get('logout', 'admin.login/logout');

    Route::get('/', 'admin.index/index');
    Route::get('index', 'admin.index/index');

    Route::get('novel/index', 'admin.novel/index');
    Route::get('novel/create', 'admin.novel/create');
    Route::post('novel/create', 'admin.novel/doCreate');
    Route::get('novel/edit', 'admin.novel/edit');
    Route::post('novel/edit', 'admin.novel/doEdit');
    Route::post('novel/delete', 'admin.novel/delete');
    Route::get('novel/chapters', 'admin.novel/chapters');
    Route::get('novel/chapterCreate', 'admin.novel/chapterCreate');
    Route::post('novel/chapterCreate', 'admin.novel/doChapterCreate');
    Route::get('novel/chapterEdit', 'admin.novel/chapterEdit');
    Route::post('novel/chapterEdit', 'admin.novel/doChapterEdit');
    Route::post('novel/chapterDelete', 'admin.novel/chapterDelete');

    Route::get('category/index', 'admin.category/index');
    Route::get('category/create', 'admin.category/create');
    Route::post('category/create', 'admin.category/doCreate');
    Route::get('category/edit', 'admin.category/edit');
    Route::post('category/edit', 'admin.category/doEdit');
    Route::post('category/delete', 'admin.category/delete');
    Route::post('category/reorder', 'admin.category/reorder');

    Route::get('user/index', 'admin.user/index');
    Route::get('user/detail', 'admin.user/detail');
    Route::get('user/edit', 'admin.user/edit');
    Route::post('user/edit', 'admin.user/doEdit');
    Route::post('user/toggleStatus', 'admin.user/toggleStatus');
    Route::post('user/resetPassword', 'admin.user/resetPassword');

    Route::get('post/index', 'admin.post/index');
    Route::get('post/comments', 'admin.post/comments');
    Route::post('post/toggleStatus', 'admin.post/toggleStatus');
    Route::post('post/delete', 'admin.post/delete');
    Route::post('post/toggleCommentStatus', 'admin.post/toggleCommentStatus');
    Route::post('post/deleteComment', 'admin.post/deleteComment');
    Route::get('post/getComment', 'admin.post/getComment');
});
