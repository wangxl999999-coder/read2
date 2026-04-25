const Toast = {
    show(message, type = 'info', duration = 3000) {
        const existing = document.querySelector('.toast.show');
        if (existing) {
            existing.remove();
        }

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, duration);
    },

    success(message) {
        this.show(message, 'success');
    },

    error(message) {
        this.show(message, 'error');
    },

    info(message) {
        this.show(message, 'info');
    },

    warning(message) {
        this.show(message, 'warning');
    }
};

const Utils = {
    formatDuration(seconds) {
        if (seconds < 60) return seconds + '秒';
        if (seconds < 3600) return Math.floor(seconds / 60) + '分钟';
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        return hours + '小时' + minutes + '分钟';
    },

    formatNumber(num) {
        if (num >= 10000) {
            return (num / 10000).toFixed(1) + '万';
        }
        if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'k';
        }
        return num.toString();
    },

    formatDate(date) {
        const d = new Date(date);
        const now = new Date();
        const diff = now - d;
        
        if (diff < 60000) return '刚刚';
        if (diff < 3600000) return Math.floor(diff / 60000) + '分钟前';
        if (diff < 86400000) return Math.floor(diff / 3600000) + '小时前';
        if (diff < 604800000) return Math.floor(diff / 86400000) + '天前';
        
        return d.toLocaleDateString('zh-CN');
    },

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },

    storage: {
        set(key, value, expires = 7) {
            const item = {
                value,
                expiry: Date.now() + expires * 24 * 60 * 60 * 1000
            };
            localStorage.setItem(key, JSON.stringify(item));
        },

        get(key) {
            const itemStr = localStorage.getItem(key);
            if (!itemStr) return null;
            
            try {
                const item = JSON.parse(itemStr);
                if (Date.now() > item.expiry) {
                    localStorage.removeItem(key);
                    return null;
                }
                return item.value;
            } catch {
                localStorage.removeItem(key);
                return null;
            }
        },

        remove(key) {
            localStorage.removeItem(key);
        },

        clear() {
            localStorage.clear();
        }
    },

    async request(url, options = {}) {
        const defaultOptions = {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        };

        const config = { ...defaultOptions, ...options };
        
        try {
            const response = await fetch(url, config);
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Request error:', error);
            return { code: 500, msg: '网络错误，请稍后重试' };
        }
    }
};

const Reader = {
    settings: {
        fontSize: 18,
        lineHeight: 2,
        theme: 'default',
        brightness: 100
    },

    init() {
        this.loadSettings();
        this.bindEvents();
        this.applySettings();
    },

    loadSettings() {
        const saved = Utils.storage.get('readerSettings');
        if (saved) {
            this.settings = { ...this.settings, ...saved };
        }
    },

    saveSettings() {
        Utils.storage.set('readerSettings', this.settings, 30);
    },

    applySettings() {
        const content = document.querySelector('.reader-content');
        if (!content) return;

        content.classList.remove('theme-default', 'theme-sepia', 'theme-green', 'theme-dark');
        content.classList.add(`theme-${this.settings.theme}`);

        const readerText = content.querySelector('.reader-text');
        if (readerText) {
            readerText.style.fontSize = this.settings.fontSize + 'px';
            readerText.style.lineHeight = this.settings.lineHeight;
        }

        content.style.filter = `brightness(${this.settings.brightness}%)`;

        const fontSizeDisplay = document.getElementById('fontSizeDisplay');
        if (fontSizeDisplay) {
            fontSizeDisplay.textContent = this.settings.fontSize + 'px';
        }

        const brightnessSlider = document.getElementById('brightnessSlider');
        if (brightnessSlider) {
            brightnessSlider.value = this.settings.brightness;
        }

        const themeOptions = document.querySelectorAll('.theme-option');
        themeOptions.forEach(option => {
            option.classList.toggle('active', option.dataset.theme === this.settings.theme);
        });
    },

    setFontSize(size) {
        this.settings.fontSize = Math.max(12, Math.min(28, size));
        this.applySettings();
        this.saveSettings();
    },

    increaseFontSize() {
        this.setFontSize(this.settings.fontSize + 2);
    },

    decreaseFontSize() {
        this.setFontSize(this.settings.fontSize - 2);
    },

    setTheme(theme) {
        this.settings.theme = theme;
        this.applySettings();
        this.saveSettings();
    },

    setBrightness(brightness) {
        this.settings.brightness = Math.max(20, Math.min(100, brightness));
        this.applySettings();
        this.saveSettings();
    },

    togglePanel(panel) {
        const panels = ['settings', 'chapters', 'toc'];
        panels.forEach(p => {
            const el = document.getElementById(p + 'Panel');
            if (el) {
                el.classList.toggle('active', p === panel);
            }
        });

        const overlay = document.getElementById('panelOverlay');
        if (overlay) {
            overlay.classList.toggle('active', panels.includes(panel));
        }
    },

    closeAllPanels() {
        ['settings', 'chapters', 'toc'].forEach(p => {
            const el = document.getElementById(p + 'Panel');
            if (el) {
                el.classList.remove('active');
            }
        });

        const overlay = document.getElementById('panelOverlay');
        if (overlay) {
            overlay.classList.remove('active');
        }
    },

    bindEvents() {
        const content = document.querySelector('.reader-content');
        if (!content) return;

        let lastTap = 0;
        content.addEventListener('click', (e) => {
            const now = Date.now();
            if (now - lastTap < 300) {
                e.preventDefault();
                this.toggleToolbar();
            }
            lastTap = now;
        });

        const overlay = document.getElementById('panelOverlay');
        if (overlay) {
            overlay.addEventListener('click', () => {
                this.closeAllPanels();
            });
        }

        const fontSizeUp = document.getElementById('fontSizeUp');
        const fontSizeDown = document.getElementById('fontSizeDown');

        if (fontSizeUp) {
            fontSizeUp.addEventListener('click', () => this.increaseFontSize());
        }
        if (fontSizeDown) {
            fontSizeDown.addEventListener('click', () => this.decreaseFontSize());
        }

        const themeOptions = document.querySelectorAll('.theme-option');
        themeOptions.forEach(option => {
            option.addEventListener('click', () => {
                const theme = option.dataset.theme;
                if (theme) {
                    this.setTheme(theme);
                }
            });
        });

        const brightnessSlider = document.getElementById('brightnessSlider');
        if (brightnessSlider) {
            brightnessSlider.addEventListener('input', (e) => {
                this.setBrightness(parseInt(e.target.value));
            });
        }
    },

    toggleToolbar() {
        const header = document.querySelector('.reader-header');
        const footer = document.querySelector('.reader-footer');

        if (header) {
            header.classList.toggle('hidden');
        }
        if (footer) {
            footer.classList.toggle('hidden');
        }
    }
};

const Search = {
    init() {
        this.bindEvents();
    },

    bindEvents() {
        const searchForm = document.getElementById('searchForm');
        if (searchForm) {
            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.doSearch();
            });
        }

        const searchInput = document.querySelector('[name="keyword"]');
        if (searchInput) {
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.doSearch();
                }
            });
        }
    },

    doSearch() {
        const form = document.getElementById('searchForm');
        if (!form) return;

        const formData = new FormData(form);
        const keyword = formData.get('keyword');

        if (!keyword || keyword.trim() === '') {
            Toast.warning('请输入搜索关键词');
            return;
        }

        window.location.href = '/search?keyword=' + encodeURIComponent(keyword.trim());
    }
};

const Bookshelf = {
    init() {
        this.bindEvents();
    },

    bindEvents() {
        const sortSelect = document.getElementById('sortSelect');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.sortBooks(e.target.value);
            });
        }

        const addBookshelfBtns = document.querySelectorAll('.add-bookshelf-btn');
        addBookshelfBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const novelId = btn.dataset.novelId;
                if (novelId) {
                    this.addToBookshelf(novelId, btn);
                }
            });
        });

        const removeBookshelfBtns = document.querySelectorAll('.remove-bookshelf-btn');
        removeBookshelfBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const novelId = btn.dataset.novelId;
                if (novelId) {
                    this.removeFromBookshelf(novelId, btn);
                }
            });
        });
    },

    async addToBookshelf(novelId, btn) {
        const result = await Utils.request('/bookshelf/add', {
            method: 'POST',
            body: 'novel_id=' + novelId,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (result.code === 200) {
            Toast.success(result.msg);
            if (btn) {
                btn.classList.add('in-bookshelf');
                btn.innerHTML = '<span>📚</span> 已在书架';
            }
        } else if (result.code === 401) {
            Toast.warning('请先登录');
            setTimeout(() => {
                window.location.href = '/user/login';
            }, 1500);
        } else {
            Toast.error(result.msg);
        }
    },

    async removeFromBookshelf(novelId, btn) {
        if (!confirm('确定要从书架中移除这本书吗？')) {
            return;
        }

        const result = await Utils.request('/bookshelf/remove', {
            method: 'POST',
            body: 'novel_id=' + novelId,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (result.code === 200) {
            Toast.success(result.msg);
            const item = btn.closest('.bookshelf-item');
            if (item) {
                item.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => {
                    item.remove();
                    this.checkEmpty();
                }, 300);
            }
        } else {
            Toast.error(result.msg);
        }
    },

    checkEmpty() {
        const items = document.querySelectorAll('.bookshelf-item');
        if (items.length === 0) {
            window.location.reload();
        }
    },

    sortBooks(sortType) {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('sort', sortType);
        window.location.href = currentUrl.toString();
    }
};

const Favorite = {
    init() {
        this.bindEvents();
    },

    bindEvents() {
        const favoriteBtns = document.querySelectorAll('.favorite-btn');
        favoriteBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const novelId = btn.dataset.novelId;
                if (novelId) {
                    this.toggleFavorite(novelId, btn);
                }
            });
        });
    },

    async toggleFavorite(novelId, btn) {
        const isFavorite = btn.classList.contains('is-favorite');
        const url = isFavorite ? '/favorite/remove' : '/favorite/add';

        const result = await Utils.request(url, {
            method: 'POST',
            body: 'novel_id=' + novelId,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (result.code === 200) {
            Toast.success(result.msg);
            btn.classList.toggle('is-favorite', !isFavorite);
            
            const icon = btn.querySelector('.btn-icon') || btn;
            icon.textContent = isFavorite ? '🤍' : '❤️';
        } else if (result.code === 401) {
            Toast.warning('请先登录');
            setTimeout(() => {
                window.location.href = '/user/login';
            }, 1500);
        } else {
            Toast.error(result.msg);
        }
    }
};

const Circle = {
    init() {
        this.bindEvents();
    },

    bindEvents() {
        const postForm = document.getElementById('postForm');
        if (postForm) {
            postForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitPost(postForm);
            });
        }

        const commentForms = document.querySelectorAll('.comment-form');
        commentForms.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitComment(form);
            });
        });

        const likeBtns = document.querySelectorAll('.like-btn');
        likeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const postId = btn.dataset.postId;
                if (postId) {
                    this.toggleLike(postId, btn);
                }
            });
        });

        const replyBtns = document.querySelectorAll('.reply-btn');
        replyBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const commentId = btn.dataset.commentId;
                const username = btn.dataset.username;
                if (commentId) {
                    this.showReplyForm(commentId, username);
                }
            });
        });
    },

    async submitPost(form) {
        const formData = new FormData(form);
        const title = formData.get('title');
        const content = formData.get('content');

        if (!title || title.trim() === '') {
            Toast.warning('请输入帖子标题');
            return;
        }

        if (!content || content.trim() === '') {
            Toast.warning('请输入帖子内容');
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="loading-spinner"></span> 发布中...';

        const result = await Utils.request('/circle/post', {
            method: 'POST',
            body: formData
        });

        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;

        if (result.code === 200) {
            Toast.success(result.msg);
            if (result.url) {
                window.location.href = result.url;
            }
        } else if (result.code === 401) {
            Toast.warning('请先登录');
            setTimeout(() => {
                window.location.href = '/user/login';
            }, 1500);
        } else {
            Toast.error(result.msg);
        }
    },

    async submitComment(form) {
        const formData = new FormData(form);
        const content = formData.get('content');

        if (!content || content.trim() === '') {
            Toast.warning('请输入评论内容');
            return;
        }

        const result = await Utils.request('/circle/comment', {
            method: 'POST',
            body: formData
        });

        if (result.code === 200) {
            Toast.success(result.msg);
            window.location.reload();
        } else if (result.code === 401) {
            Toast.warning('请先登录');
            setTimeout(() => {
                window.location.href = '/user/login';
            }, 1500);
        } else {
            Toast.error(result.msg);
        }
    },

    async toggleLike(postId, btn) {
        const isLiked = btn.classList.contains('is-liked');
        const url = isLiked ? '/circle/unlike' : '/circle/like';

        const result = await Utils.request(url, {
            method: 'POST',
            body: 'post_id=' + postId,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (result.code === 200) {
            btn.classList.toggle('is-liked', !isLiked);
            
            const countEl = btn.querySelector('.like-count');
            if (countEl) {
                let count = parseInt(countEl.textContent);
                count = isLiked ? count - 1 : count + 1;
                countEl.textContent = count;
            }
        } else if (result.code === 401) {
            Toast.warning('请先登录');
            setTimeout(() => {
                window.location.href = '/user/login';
            }, 1500);
        } else {
            Toast.error(result.msg);
        }
    },

    showReplyForm(commentId, username) {
        const replyInput = document.getElementById('replyToInput');
        const replyDisplay = document.getElementById('replyToDisplay');
        const cancelReply = document.getElementById('cancelReply');

        if (replyInput) {
            replyInput.value = commentId;
        }

        if (replyDisplay) {
            replyDisplay.innerHTML = `回复 <span class="reply-user">@${username}</span>`;
            replyDisplay.style.display = 'block';
        }

        if (cancelReply) {
            cancelReply.style.display = 'inline-block';
            cancelReply.onclick = () => {
                this.hideReplyForm();
            };
        }

        const commentInput = document.querySelector('[name="content"]');
        if (commentInput) {
            commentInput.focus();
            commentInput.placeholder = `回复 @${username}...`;
        }
    },

    hideReplyForm() {
        const replyInput = document.getElementById('replyToInput');
        const replyDisplay = document.getElementById('replyToDisplay');
        const cancelReply = document.getElementById('cancelReply');

        if (replyInput) {
            replyInput.value = '';
        }

        if (replyDisplay) {
            replyDisplay.style.display = 'none';
        }

        if (cancelReply) {
            cancelReply.style.display = 'none';
        }

        const commentInput = document.querySelector('[name="content"]');
        if (commentInput) {
            commentInput.placeholder = '写下你的评论...';
        }
    }
};

const MobileNav = {
    init() {
        this.bindEvents();
    },

    bindEvents() {
        const toggle = document.getElementById('mobileMenuToggle');
        const nav = document.getElementById('mobileNav');
        const overlay = document.getElementById('mobileNavOverlay');

        if (toggle && nav) {
            toggle.addEventListener('click', () => {
                const isActive = nav.classList.contains('active');
                nav.classList.toggle('active', !isActive);
                if (overlay) {
                    overlay.classList.toggle('active', !isActive);
                }
                toggle.classList.toggle('active', !isActive);
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                this.close();
            });
        }
    },

    close() {
        const nav = document.getElementById('mobileNav');
        const overlay = document.getElementById('mobileNavOverlay');
        const toggle = document.getElementById('mobileMenuToggle');

        if (nav) nav.classList.remove('active');
        if (overlay) overlay.classList.remove('active');
        if (toggle) toggle.classList.remove('active');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    Search.init();
    MobileNav.init();
    Bookshelf.init();
    Favorite.init();
    Circle.init();

    if (document.querySelector('.reader-container')) {
        Reader.init();
    }
});
