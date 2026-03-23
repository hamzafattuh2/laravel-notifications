@auth
    <div
        x-data="notificationBell({
            endpoints: {
                list: '{{ route('notifications.index') }}',
                unreadCount: '{{ route('notifications.unread-count') }}',
                markAsRead: '{{ url('/notifications/mark-as-read') }}',
                markAllAsRead: '{{ route('notifications.mark-all-as-read') }}'
            },
            csrfToken: '{{ csrf_token() }}',
            userId: {{ auth()->id() }},
        })"
        x-init="init()"
        class="relative"
        @click.outside="open = false"
    >
        <button
            type="button"
            @click="toggleDropdown"
            class="relative rounded-full bg-slate-100 p-2 text-slate-700 transition hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-cyan-500"
            aria-label="Notifications"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M14.5 18a2.5 2.5 0 0 1-5 0" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M18 9a6 6 0 1 0-12 0v3.75L4 15v1h16v-1l-2-2.25V9Z" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span
                x-show="unreadCount > 0"
                x-cloak
                x-text="badgeText"
                class="absolute -right-1 -top-1 min-w-5 rounded-full bg-rose-500 px-1.5 py-0.5 text-center text-[10px] font-bold text-white"
            ></span>
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
            class="absolute right-0 z-50 mt-3 w-[22rem] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
        >
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <h3 class="text-sm font-semibold text-slate-800">Notifications</h3>
                <button
                    type="button"
                    @click="markAllAsRead"
                    class="text-xs font-medium text-cyan-700 transition hover:text-cyan-900"
                >
                    Mark all as read
                </button>
            </div>

            <div class="max-h-96 overflow-y-auto">
                <template x-if="loading && notifications.length === 0">
                    <div class="px-4 py-6 text-center text-sm text-slate-500">Loading...</div>
                </template>

                <template x-if="!loading && notifications.length === 0">
                    <div class="px-4 py-8 text-center text-sm text-slate-500">No notifications yet.</div>
                </template>

                <template x-for="notification in notifications" :key="notification.id">
                    <a
                        href="#"
                        @click.prevent="openNotification(notification)"
                        class="block border-b border-slate-100 px-4 py-3 transition hover:bg-slate-50"
                        :class="notification.is_read ? 'bg-white' : 'bg-cyan-50/60'"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                                :class="iconColorClass(notification.type)"
                            >
                                <span x-text="iconFor(notification.type)"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p
                                    class="truncate text-sm text-slate-800"
                                    :class="notification.is_read ? 'font-medium opacity-80' : 'font-semibold'"
                                    x-text="notification.title"
                                ></p>
                                <p
                                    class="mt-0.5 line-clamp-2 text-xs text-slate-600"
                                    x-text="notification.message"
                                ></p>
                                <p class="mt-1 text-[11px] text-slate-400" x-text="notification.time_human"></p>
                            </div>
                        </div>
                    </a>
                </template>
            </div>

            <div class="border-t border-slate-100 px-4 py-3">
                <button
                    type="button"
                    @click="loadMore"
                    :disabled="!hasMore || loading"
                    class="w-full rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-40"
                >
                    <span x-show="hasMore && !loading">Load more</span>
                    <span x-show="!hasMore">You're all caught up</span>
                    <span x-show="loading">Loading...</span>
                </button>
            </div>
        </div>
    </div>
@endauth
