<div class="crm-notification-bell" style="margin-left: 20px;" wire:poll.30s>
    <div x-data="{ open: false }" class="crm-notification-bell__wrap" @click.outside="open = false">
        <button
            type="button"
            class="crm-notification-bell__button"
            x-on:click="open = ! open"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="crm-notification-bell__icon" fill="none" viewBox="0 0 24 24"
                 stroke-width="1.7" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022 23.848 23.848 0 0 0 5.455 1.31m5.714 0a3 3 0 0 1-5.714 0"/>
            </svg>

            @if ($unreadCount > 0)
                <span class="crm-notification-bell__badge">
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </span>
            @endif
        </button>

        <div
            x-cloak
            x-show="open"
            x-transition
            class="crm-notification-bell__dropdown"
        >
            <div class="crm-notification-bell__header">
                <div>
                    <div class="crm-notification-bell__title">Notifications</div>
                    <div class="crm-notification-bell__subtitle">
                        {{ $unreadCount }} unread
                    </div>
                </div>

                @if ($unreadCount > 0)
                    <button
                        type="button"
                        class="crm-notification-bell__mark-all"
                        wire:click="markAllAsRead"
                    >
                        Mark all as read
                    </button>
                @endif
            </div>

            <div class="crm-notification-bell__list">
                @forelse ($notifications as $notification)
                    <button
                        type="button"
                        class="crm-notification-bell__item {{ $notification->read_at === null ? 'is-unread' : '' }}"
                        wire:click="openNotification({{ $notification->id }})"
                    >
                        <div class="crm-notification-bell__item-main">
                            <div class="crm-notification-bell__item-title">
                                {{ $notification->title }}
                            </div>

                            @if ($notification->body !== null && $notification->body !== '')
                                <div class="crm-notification-bell__item-body">
                                    {{ $notification->body }}
                                </div>
                            @endif

                            <div class="crm-notification-bell__item-time">
                                {{ $notification->created_at?->diffForHumans() }}
                            </div>
                        </div>

                        @if ($notification->read_at === null)
                            <span class="crm-notification-bell__dot"></span>
                        @endif
                    </button>
                @empty
                    <div class="crm-notification-bell__empty">
                        No notifications yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
