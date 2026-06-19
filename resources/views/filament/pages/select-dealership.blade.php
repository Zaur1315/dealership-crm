<x-filament-panels::page>
    <div class="mx-auto w-full max-w-5xl space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-start gap-4">
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                    <x-heroicon-o-building-storefront class="h-6 w-6"/>
                </div>

                <div>
                    <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
                        Select Dealership
                    </h2>

                    <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400">
                        Choose the dealership you want to work with. All leads, tasks, emails, and reports will be shown
                        for the selected dealership.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($dealerships as $dealership)
                <button
                    type="button"
                    wire:click="selectDealership({{ $dealership->id }})"
                    class="group rounded-2xl border border-gray-200 bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-primary-500 hover:shadow-lg dark:border-gray-800 dark:bg-gray-900 dark:hover:border-primary-500"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-sm font-bold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                {{ mb_strtoupper(mb_substr($dealership->name, 0, 1)) }}
                            </div>

                            <div class="min-w-0">
                                <div class="truncate text-base font-semibold text-gray-950 dark:text-white">
                                    {{ $dealership->name }}
                                </div>

                                <div class="mt-1 truncate text-sm text-gray-500 dark:text-gray-400">
                                    {{ $dealership->email ?: 'No email configured' }}
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-1 text-gray-400 transition group-hover:translate-x-1 group-hover:text-primary-500">
                            →
                        </div>
                    </div>

                    <div
                        class="mt-5 border-t border-gray-100 pt-4 text-xs font-medium uppercase tracking-wide text-gray-400 dark:border-gray-800">
                        Open workspace
                    </div>
                </button>
            @empty
                <div
                    class="rounded-2xl border border-dashed border-gray-300 bg-white p-6 text-center dark:border-gray-700 dark:bg-gray-900">
                    <div
                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <x-heroicon-o-building-storefront class="h-6 w-6"/>
                    </div>

                    <p class="mt-4 text-sm font-medium text-gray-950 dark:text-white">
                        No dealerships assigned
                    </p>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        No dealerships are assigned to your account.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
