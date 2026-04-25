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
    },

    confirm(message) {
        return new Promise((resolve) => {
            resolve(window.confirm(message));
        });
    }
};

const Sidebar = {
    init() {
        this.bindEvents();
        this.restoreState();
    },

    bindEvents() {
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('adminSidebar');
        const main = document.querySelector('.admin-main');

        if (toggleBtn && sidebar && main) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                main.classList.toggle('expanded');
                
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });
        }

        const navToggles = document.querySelectorAll('.nav-toggle');
        navToggles.forEach(toggle => {
            toggle.addEventListener('click', () => {
                const navItem = toggle.closest('.nav-item');
                if (navItem) {
                    navItem.classList.toggle('expanded');
                }
            });
        });

        const mobileToggle = document.getElementById('mobileMenuToggle');
        const overlay = document.getElementById('sidebarOverlay');

        if (mobileToggle && sidebar) {
            mobileToggle.addEventListener('click', () => {
                sidebar.classList.toggle('mobile-active');
                if (overlay) {
                    overlay.classList.toggle('active');
                }
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('mobile-active');
                overlay.classList.remove('active');
            });
        }
    },

    restoreState() {
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        const sidebar = document.getElementById('adminSidebar');
        const main = document.querySelector('.admin-main');

        if (isCollapsed && sidebar && main) {
            sidebar.classList.add('collapsed');
            main.classList.add('expanded');
        }
    }
};

const TableActions = {
    init() {
        this.bindDeleteEvents();
        this.bindToggleStatusEvents();
        this.bindSortEvents();
    },

    bindDeleteEvents() {
        const deleteBtns = document.querySelectorAll('.delete-btn');
        
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                
                const id = btn.dataset.id;
                const type = btn.dataset.type || 'item';
                
                const confirmed = await Utils.confirm(`确定要删除这个${type}吗？删除后无法恢复！`);
                if (!confirmed) return;

                const url = btn.dataset.url || this.getDeleteUrl(type);
                
                const result = await Utils.request(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + id
                });

                if (result.code === 200) {
                    Toast.success(result.msg);
                    const row = btn.closest('tr');
                    if (row) {
                        row.style.transition = 'all 0.3s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(-20px)';
                        setTimeout(() => {
                            row.remove();
                        }, 300);
                    }
                } else {
                    Toast.error(result.msg);
                }
            });
        });
    },

    getDeleteUrl(type) {
        const map = {
            'novel': '/admin/novel/delete',
            'chapter': '/admin/novel/chapterDelete',
            'category': '/admin/category/delete',
            'user': '/admin/user/delete',
            'post': '/admin/post/delete',
            'comment': '/admin/post/deleteComment'
        };
        return map[type] || '/admin/novel/delete';
    },

    bindToggleStatusEvents() {
        const toggleBtns = document.querySelectorAll('.toggle-status-btn');
        
        toggleBtns.forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.dataset.id;
                const type = btn.dataset.type || 'status';
                const url = btn.dataset.url || this.getToggleUrl(type);

                const result = await Utils.request(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + id
                });

                if (result.code === 200) {
                    Toast.success(result.msg);
                    const statusTag = btn.querySelector('.status-tag');
                    if (statusTag) {
                        statusTag.classList.toggle('active');
                        statusTag.classList.toggle('inactive');
                        statusTag.textContent = statusTag.classList.contains('active') ? '正常' : '禁用';
                    }
                } else {
                    Toast.error(result.msg);
                }
            });
        });
    },

    getToggleUrl(type) {
        const map = {
            'user': '/admin/user/toggleStatus',
            'post': '/admin/post/toggleStatus',
            'comment': '/admin/post/toggleCommentStatus'
        };
        return map[type] || '/admin/user/toggleStatus';
    },

    bindSortEvents() {
        const sortInputs = document.querySelectorAll('.sort-input');
        
        sortInputs.forEach(input => {
            let originalValue = input.value;
            
            input.addEventListener('focus', () => {
                originalValue = input.value;
            });

            input.addEventListener('blur', async () => {
                if (input.value !== originalValue) {
                    const id = input.dataset.id;
                    const newSort = input.value;
                    const url = input.dataset.url || '/admin/category/reorder';

                    const result = await Utils.request(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `orders[0][id]=${id}&orders[0][sort]=${newSort}`
                    });

                    if (result.code === 200) {
                        Toast.success(result.msg);
                        originalValue = newSort;
                    } else {
                        Toast.error(result.msg);
                        input.value = originalValue;
                    }
                }
            });

            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    input.blur();
                }
            });
        });
    }
};

const FormHelper = {
    init() {
        this.bindFormEvents();
        this.bindAjaxForms();
    },

    bindFormEvents() {
        const selects = document.querySelectorAll('.form-select');
        selects.forEach(select => {
            select.addEventListener('change', () => {
                select.style.borderColor = '#667eea';
            });
            select.addEventListener('blur', () => {
                select.style.borderColor = '';
            });
        });

        const inputs = document.querySelectorAll('.form-input, .form-textarea');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.style.borderColor = '#667eea';
            });
            input.addEventListener('blur', () => {
                input.style.borderColor = '';
            });
        });
    },

    bindAjaxForms() {
        const ajaxForms = document.querySelectorAll('[data-ajax]');
        
        ajaxForms.forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const formData = new FormData(form);
                const url = form.action;
                const method = form.method.toUpperCase() || 'POST';

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn ? submitBtn.innerHTML : null;
                
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="loading-spinner"></span> 处理中...';
                }

                const result = await Utils.request(url, {
                    method: method,
                    body: formData
                });

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }

                if (result.code === 200) {
                    Toast.success(result.msg);
                    if (result.url) {
                        setTimeout(() => {
                            window.location.href = result.url;
                        }, 500);
                    }
                } else {
                    Toast.error(result.msg);
                }
            });
        });
    }
};

const Modal = {
    init() {
        this.bindEvents();
    },

    bindEvents() {
        const modals = document.querySelectorAll('.modal');
        
        modals.forEach(modal => {
            const closeBtn = modal.querySelector('.modal-close');
            const cancelBtn = modal.querySelector('[data-action="close"]');
            
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    this.close(modal);
                });
            }

            if (cancelBtn) {
                cancelBtn.addEventListener('click', () => {
                    this.close(modal);
                });
            }

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.close(modal);
                }
            });
        });

        const openBtns = document.querySelectorAll('[data-modal]');
        openBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const modalId = btn.dataset.modal;
                const modal = document.getElementById(modalId);
                if (modal) {
                    this.open(modal);
                }
            });
        });
    },

    open(modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    },

    close(modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
};

const BulkActions = {
    init() {
        this.bindEvents();
    },

    bindEvents() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.row-checkbox');

        if (selectAll) {
            selectAll.addEventListener('change', () => {
                checkboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
                this.updateCount();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                this.updateCount();
            });
        });

        const bulkActions = document.querySelectorAll('[data-bulk]');
        bulkActions.forEach(btn => {
            btn.addEventListener('click', async () => {
                const action = btn.dataset.bulk;
                const selected = this.getSelected();
                
                if (selected.length === 0) {
                    Toast.warning('请先选择要操作的项目');
                    return;
                }

                const confirmed = await Utils.confirm(`确定要对选中的 ${selected.length} 个项目执行此操作吗？`);
                if (!confirmed) return;

                const url = this.getBulkUrl(action);
                const result = await Utils.request(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'ids=' + selected.join(',')
                });

                if (result.code === 200) {
                    Toast.success(result.msg);
                    window.location.reload();
                } else {
                    Toast.error(result.msg);
                }
            });
        });
    },

    getSelected() {
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    },

    updateCount() {
        const selected = this.getSelected();
        const countEl = document.getElementById('selectedCount');
        if (countEl) {
            countEl.textContent = selected.length;
        }
    },

    getBulkUrl(action) {
        const map = {
            'delete': '/admin/novel/bulkDelete',
            'enable': '/admin/novel/bulkEnable',
            'disable': '/admin/novel/bulkDisable'
        };
        return map[action] || '/admin/novel/bulkDelete';
    }
};

const DatePicker = {
    init() {
        this.initDateInputs();
    },

    initDateInputs() {
        const dateInputs = document.querySelectorAll('[type="date"]');
        dateInputs.forEach(input => {
            input.addEventListener('change', () => {
                input.style.borderColor = '#667eea';
            });
        });

        const dateRangeInputs = document.querySelectorAll('[data-date-range]');
        dateRangeInputs.forEach(input => {
            if (!input.value) {
                const today = new Date().toISOString().split('T')[0];
                input.placeholder = today;
            }
        });
    }
};

const Editor = {
    init() {
        this.bindEvents();
    },

    bindEvents() {
        const chapterContent = document.getElementById('chapterContent');
        const wordCount = document.getElementById('wordCount');

        if (chapterContent && wordCount) {
            chapterContent.addEventListener('input', () => {
                const text = chapterContent.value.replace(/\s/g, '');
                wordCount.textContent = text.length;
            });
        }

        const contentInputs = document.querySelectorAll('[data-word-count]');
        contentInputs.forEach(input => {
            const targetId = input.dataset.wordCount;
            const target = document.getElementById(targetId);
            
            if (target) {
                input.addEventListener('input', () => {
                    const text = input.value.replace(/\s/g, '');
                    target.textContent = text.length;
                });
            }
        });
    }
};

const ChartHelper = {
    init() {
        this.initCharts();
    },

    initCharts() {
        const chartCanvas = document.getElementById('readingChart');
        if (chartCanvas) {
            this.initReadingChart(chartCanvas);
        }
    },

    initReadingChart(canvas) {
        const ctx = canvas.getContext('2d');
        const dailyStats = window.readingStats || {};
        
        const dates = [];
        const durations = [];
        
        const now = new Date();
        for (let i = 13; i >= 0; i--) {
            const date = new Date(now);
            date.setDate(date.getDate() - i);
            const dateStr = date.toISOString().split('T')[0];
            dates.push(dateStr.slice(5));
            durations.push(Math.round((dailyStats[dateStr] || 0) / 60));
        }

        const maxVal = Math.max(...durations, 60);
        const padding = { top: 20, right: 40, bottom: 50, left: 60 };
        const chartWidth = canvas.width - padding.left - padding.right;
        const chartHeight = canvas.height - padding.top - padding.bottom;

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        ctx.strokeStyle = '#e5e7eb';
        ctx.lineWidth = 1;
        for (let i = 0; i <= 4; i++) {
            const y = padding.top + chartHeight * (i / 4);
            ctx.beginPath();
            ctx.moveTo(padding.left, y);
            ctx.lineTo(canvas.width - padding.right, y);
            ctx.stroke();

            ctx.fillStyle = '#6b7280';
            ctx.font = '12px system-ui';
            ctx.textAlign = 'right';
            const label = Math.round(maxVal * (1 - i / 4));
            ctx.fillText(label + '分', padding.left - 10, y + 4);
        }

        const barWidth = chartWidth / dates.length;
        const barGap = 8;

        durations.forEach((value, index) => {
            const x = padding.left + barWidth * index + barGap;
            const barWidthActual = barWidth - barGap * 2;
            const barHeight = (value / maxVal) * chartHeight;
            const y = padding.top + chartHeight - barHeight;

            const gradient = ctx.createLinearGradient(0, y, 0, padding.top + chartHeight);
            gradient.addColorStop(0, '#667eea');
            gradient.addColorStop(1, '#a78bfa');

            ctx.fillStyle = gradient;
            ctx.beginPath();
            ctx.roundRect(x, y, barWidthActual, barHeight, [4, 4, 0, 0]);
            ctx.fill();

            ctx.fillStyle = '#6b7280';
            ctx.font = '11px system-ui';
            ctx.textAlign = 'center';
            ctx.fillText(dates[index], x + barWidthActual / 2, canvas.height - 20);

            if (value > 0) {
                ctx.fillStyle = '#374151';
                ctx.fillText(value + '分', x + barWidthActual / 2, y - 8);
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', () => {
    Sidebar.init();
    TableActions.init();
    FormHelper.init();
    Modal.init();
    BulkActions.init();
    DatePicker.init();
    Editor.init();
    ChartHelper.init();
});
