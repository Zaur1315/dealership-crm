<x-filament-panels::page>
    <div class="space-y-4">
        <div>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Choose the dealership you want to work with.
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3" style="margin-top: 30px;">
            @forelse ($dealerships as $dealership)
                <x-filament::button
                    type="button"
                    wire:click="selectDealership({{ $dealership->id }})"
                    size="lg"
                    color="primary"
                    class="w-full justify-start"
                >
                    <div class="flex w-full items-center justify-between gap-4">
                        <div class="text-left">
                            <div class="font-semibold">
                                {{ $dealership->name }}
                            </div>

                            <div class="text-xs opacity-80">
                                {{ $dealership->email ?: 'No email configured' }}
                            </div>
                        </div>
                    </div>
                </x-filament::button>
            @empty
                <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        No dealerships are assigned to your account.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
