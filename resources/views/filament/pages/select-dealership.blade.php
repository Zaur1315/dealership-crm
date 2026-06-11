<x-filament-panels::page>
    <div class="space-y-4">
        <div>
            <h2 class="text-xl font-bold tracking-tight">
                Select Dealership
            </h2>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Choose the dealership you want to work with.
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($dealerships as $dealership)
                <button
                    type="button"
                    wire:click="selectDealership({{ $dealership->id }})"
                    class="rounded-xl border border-gray-200 bg-white p-5 text-left shadow-sm transition hover:border-primary-500 hover:shadow-md dark:border-gray-700 dark:bg-gray-900"
                >
                    <div class="text-base font-semibold">
                        {{ $dealership->name }}
                    </div>

                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $dealership->email ?: 'No email configured' }}
                    </div>
                </button>
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
