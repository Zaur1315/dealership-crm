@php use App\Filament\Resources\CrmNotifications\CrmNotificationResource; @endphp
<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Latest Notifications
        </x-slot>

        <div class="space-y-3">
            @forelse ($this->getNotifications() as $notification)
                <div
                    class="flex items-start justify-between gap-4 rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                    <div>
                        <div class="font-medium">
                            {{ $notification->title }}
                        </div>

                        @if ($notification->body)
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $notification->body }}
                            </div>
                        @endif

                        <div class="mt-1 text-xs text-gray-400">
                            {{ $notification->created_at?->diffForHumans() }}
                        </div>
                    </div>

                    @if ($notification->target_url)
                        <a
                            href="{{ route('crm-notifications.open', $notification) }}"
                            class="text-sm font-medium text-primary-600 hover:underline"
                        >
                            Open
                        </a>
                    @endif
                </div>
            @empty
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    No unread notifications.
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            <a
                href="{{ CrmNotificationResource::getUrl('index') }}"
                class="text-sm font-medium text-primary-600 hover:underline"
            >
                View all notifications
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
