import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.notificationBell = ({ endpoints, csrfToken, userId }) => ({
    open: false,
    loading: false,
    notifications: [],
    unreadCount: 0,
    currentPage: 1,
    lastPage: 1,
    perPage: 10,
    endpoints,
    csrfToken,
    userId,

    get hasMore() {
        return this.currentPage < this.lastPage;
    },

    get badgeText() {
        return this.unreadCount > 99 ? '99+' : String(this.unreadCount);
    },

    async init() {
        await this.fetchUnreadCount();
        this.subscribeToRealtime();
    },

    async toggleDropdown() {
        this.open = !this.open;

        if (this.open && this.notifications.length === 0) {
            await this.fetchNotifications(1);
        }
    },

    async fetchUnreadCount() {
        const response = await fetch(this.endpoints.unreadCount, {
            headers: {
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        const payload = await response.json();
        this.unreadCount = Number(payload.unread_count ?? 0);
    },

    async fetchNotifications(page = 1) {
        this.loading = true;

        try {
            const url = `${this.endpoints.list}?page=${page}&per_page=${this.perPage}`;
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            const incoming = Array.isArray(payload.data) ? payload.data : [];

            if (page === 1) {
                this.notifications = incoming;
            } else {
                this.notifications = [...this.notifications, ...incoming];
            }

            const meta = payload.meta ?? {};
            this.currentPage = Number(meta.current_page ?? page);
            this.lastPage = Number(meta.last_page ?? page);
            this.unreadCount = Number(meta.unread_count ?? this.unreadCount);
        } finally {
            this.loading = false;
        }
    },

    async loadMore() {
        if (!this.hasMore || this.loading) {
            return;
        }

        await this.fetchNotifications(this.currentPage + 1);
    },

    async markAsRead(notification) {
        if (notification.is_read) {
            return true;
        }

        const response = await fetch(`${this.endpoints.markAsRead}/${notification.id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.csrfToken,
                Accept: 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return false;
        }

        const payload = await response.json();
        notification.is_read = true;
        this.unreadCount = Number(payload.unread_count ?? this.unreadCount);

        return true;
    },

    async markAllAsRead() {
        if (this.loading || this.notifications.length === 0) {
            return;
        }

        const response = await fetch(this.endpoints.markAllAsRead, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.csrfToken,
                Accept: 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        this.notifications = this.notifications.map((item) => ({ ...item, is_read: true }));
        this.unreadCount = 0;
    },

    async openNotification(notification) {
        const marked = await this.markAsRead(notification);

        if (!marked) {
            return;
        }

        if (notification.action_url) {
            window.location.href = notification.action_url;
        }
    },

    iconFor(type) {
        if (type === 'success') return 'S';
        if (type === 'error') return '!';
        if (type === 'warning') return 'W';

        return 'i';
    },

    iconColorClass(type) {
        if (type === 'success') return 'bg-emerald-100 text-emerald-700';
        if (type === 'error') return 'bg-rose-100 text-rose-700';
        if (type === 'warning') return 'bg-amber-100 text-amber-700';

        return 'bg-sky-100 text-sky-700';
    },

    subscribeToRealtime() {
        if (!window.Echo || !this.userId) {
            return;
        }

        window.Echo.private(`App.Models.User.${this.userId}`)
            .notification((notification) => {
                const item = {
                    id: notification.id ?? `live-${Date.now()}`,
                    title: notification.title ?? 'Notification',
                    message: notification.message ?? '',
                    type: notification.type ?? 'info',
                    action_url: notification.action_url ?? null,
                    is_read: false,
                    read_at: null,
                    created_at: new Date().toISOString(),
                    time_human: 'just now',
                };

                this.notifications = [item, ...this.notifications].slice(0, 20);
                this.unreadCount += 1;
            });
    },
});

Alpine.start();
