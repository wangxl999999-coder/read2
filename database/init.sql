-- 小说阅读网站数据库设计 (MySQL 5.5 兼容版)
-- 创建数据库
CREATE DATABASE IF NOT EXISTS novel_reading DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE novel_reading;

-- 用户表
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL COMMENT '用户名',
    `password` VARCHAR(255) NOT NULL COMMENT '密码',
    `nickname` VARCHAR(100) DEFAULT NULL COMMENT '昵称',
    `avatar` VARCHAR(255) DEFAULT NULL COMMENT '头像',
    `email` VARCHAR(100) DEFAULT NULL COMMENT '邮箱',
    `phone` VARCHAR(20) DEFAULT NULL COMMENT '手机号',
    `status` TINYINT DEFAULT 1 COMMENT '状态：0禁用 1正常',
    `last_login_time` DATETIME DEFAULT NULL COMMENT '最后登录时间',
    `last_login_ip` VARCHAR(50) DEFAULT NULL COMMENT '最后登录IP',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- 小说分类表
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL COMMENT '分类名称',
    `icon` VARCHAR(255) DEFAULT NULL COMMENT '分类图标',
    `sort` INT DEFAULT 0 COMMENT '排序',
    `status` TINYINT DEFAULT 1 COMMENT '状态：0禁用 1启用',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='小说分类表';

-- 小说表
CREATE TABLE IF NOT EXISTS `novels` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL COMMENT '小说名称',
    `author` VARCHAR(100) NOT NULL COMMENT '作者',
    `cover` VARCHAR(255) DEFAULT NULL COMMENT '封面图片',
    `category_id` INT UNSIGNED NOT NULL COMMENT '分类ID',
    `description` TEXT COMMENT '简介',
    `status` TINYINT DEFAULT 1 COMMENT '状态：0完结 1连载中',
    `word_count` BIGINT UNSIGNED DEFAULT 0 COMMENT '字数',
    `chapter_count` INT UNSIGNED DEFAULT 0 COMMENT '章节数',
    `view_count` INT UNSIGNED DEFAULT 0 COMMENT '阅读次数',
    `favorite_count` INT UNSIGNED DEFAULT 0 COMMENT '收藏次数',
    `is_recommend` TINYINT DEFAULT 0 COMMENT '是否推荐',
    `is_top` TINYINT DEFAULT 0 COMMENT '是否置顶',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category_id`),
    INDEX `idx_author` (`author`),
    INDEX `idx_status` (`status`),
    INDEX `idx_title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='小说表';

-- 小说章节表
CREATE TABLE IF NOT EXISTS `chapters` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `novel_id` INT UNSIGNED NOT NULL COMMENT '小说ID',
    `title` VARCHAR(200) NOT NULL COMMENT '章节标题',
    `content` LONGTEXT COMMENT '章节内容',
    `word_count` INT UNSIGNED DEFAULT 0 COMMENT '字数',
    `sort` INT UNSIGNED DEFAULT 0 COMMENT '章节排序',
    `is_vip` TINYINT DEFAULT 0 COMMENT '是否VIP章节',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_novel` (`novel_id`),
    INDEX `idx_sort` (`novel_id`, `sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='章节表';

-- 书架表
CREATE TABLE IF NOT EXISTS `bookshelves` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
    `novel_id` INT UNSIGNED NOT NULL COMMENT '小说ID',
    `last_read_chapter_id` INT UNSIGNED DEFAULT NULL COMMENT '最后阅读章节ID',
    `last_read_time` DATETIME DEFAULT NULL COMMENT '最后阅读时间',
    `read_progress` INT DEFAULT 0 COMMENT '阅读进度(百分比)',
    `sort` INT UNSIGNED DEFAULT 0 COMMENT '排序',
    `is_offline` TINYINT DEFAULT 0 COMMENT '是否已离线下载',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_user_novel` (`user_id`, `novel_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_sort` (`user_id`, `sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='书架表';

-- 收藏表
CREATE TABLE IF NOT EXISTS `favorites` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
    `novel_id` INT UNSIGNED NOT NULL COMMENT '小说ID',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_user_novel` (`user_id`, `novel_id`),
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='收藏表';

-- 阅读记录表
CREATE TABLE IF NOT EXISTS `reading_records` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
    `novel_id` INT UNSIGNED NOT NULL COMMENT '小说ID',
    `chapter_id` INT UNSIGNED NOT NULL COMMENT '章节ID',
    `start_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '开始阅读时间',
    `end_time` DATETIME DEFAULT NULL COMMENT '结束阅读时间',
    `duration_seconds` INT UNSIGNED DEFAULT 0 COMMENT '阅读时长(秒)',
    `words_read` INT UNSIGNED DEFAULT 0 COMMENT '阅读字数',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_novel` (`novel_id`),
    INDEX `idx_time` (`start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='阅读记录表';

-- 书友圈子帖子表
CREATE TABLE IF NOT EXISTS `posts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
    `novel_id` INT UNSIGNED DEFAULT NULL COMMENT '关联小说ID',
    `title` VARCHAR(200) NOT NULL COMMENT '帖子标题',
    `content` TEXT NOT NULL COMMENT '帖子内容',
    `images` TEXT DEFAULT NULL COMMENT '图片列表(JSON格式字符串)',
    `view_count` INT UNSIGNED DEFAULT 0 COMMENT '浏览次数',
    `like_count` INT UNSIGNED DEFAULT 0 COMMENT '点赞数',
    `comment_count` INT UNSIGNED DEFAULT 0 COMMENT '评论数',
    `status` TINYINT DEFAULT 1 COMMENT '状态：0隐藏 1正常',
    `is_top` TINYINT DEFAULT 0 COMMENT '是否置顶',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_novel` (`novel_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_time` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='帖子表';

-- 评论表
CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT UNSIGNED NOT NULL COMMENT '帖子ID',
    `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
    `parent_id` INT UNSIGNED DEFAULT NULL COMMENT '父评论ID',
    `reply_to_user_id` INT UNSIGNED DEFAULT NULL COMMENT '回复的用户ID',
    `content` TEXT NOT NULL COMMENT '评论内容',
    `like_count` INT UNSIGNED DEFAULT 0 COMMENT '点赞数',
    `status` TINYINT DEFAULT 1 COMMENT '状态：0隐藏 1正常',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_post` (`post_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_parent` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='评论表';

-- 点赞表
CREATE TABLE IF NOT EXISTS `likes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
    `target_type` ENUM('post', 'comment') NOT NULL COMMENT '点赞类型',
    `target_id` INT UNSIGNED NOT NULL COMMENT '目标ID',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_user_target` (`user_id`, `target_type`, `target_id`),
    INDEX `idx_target` (`target_type`, `target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='点赞表';

-- 管理员表
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE COMMENT '用户名',
    `password` VARCHAR(255) NOT NULL COMMENT '密码',
    `nickname` VARCHAR(100) DEFAULT NULL COMMENT '昵称',
    `role` TINYINT DEFAULT 1 COMMENT '角色：1普通管理员 2超级管理员',
    `status` TINYINT DEFAULT 1 COMMENT '状态：0禁用 1正常',
    `last_login_time` DATETIME DEFAULT NULL COMMENT '最后登录时间',
    `last_login_ip` VARCHAR(50) DEFAULT NULL COMMENT '最后登录IP',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员表';

-- 插入初始分类数据
INSERT INTO `categories` (`name`, `icon`, `sort`, `status`) VALUES
('玄幻奇幻', '✨', 1, 1),
('武侠仙侠', '⚔️', 2, 1),
('都市言情', '❤️', 3, 1),
('历史军事', '🏰', 4, 1),
('游戏竞技', '🎮', 5, 1),
('科幻灵异', '👻', 6, 1),
('校园青春', '📚', 7, 1),
('轻小说', '🎨', 8, 1);

-- 插入默认管理员账号 (密码: admin123, 使用SHA256哈希)
INSERT INTO `admins` (`username`, `password`, `nickname`, `role`, `status`) VALUES
('admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a', '超级管理员', 2, 1);
