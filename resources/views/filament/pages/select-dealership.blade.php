<div class="mx-auto w-full max-w-6xl space-y-8">
    <div
        class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="relative px-6 py-8 sm:px-8">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-500/10 via-transparent to-cyan-500/10"></div>

            <div class="relative flex flex-col gap-3">
                <div
                    class="inline-flex w-fit items-center rounded-full border border-primary-500/20 bg-primary-500/10 px-3 py-1 text-xs font-semibold text-primary-700 dark:text-primary-300">
                    Workspace selection
                </div>

                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                        Select Dealership
                    </h2>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">
                        Choose the dealership you want to work with. Your leads, tasks, emails and reports will be shown
                        for the selected dealership.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($dealerships as $dealership)
            <button
                type="button"
                wire:click="selectDealership({{ $dealership->id }})"
                class="group relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-6 text-left shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-primary-500/60 hover:shadow-xl dark:border-gray-800 dark:bg-gray-950 dark:hover:border-primary-400/60"
            >
                <div
                    class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary-500 to-cyan-400 opacity-0 transition group-hover:opacity-100"></div>

                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary-500/10 text-lg font-bold text-primary-700 ring-1 ring-primary-500/20 dark:text-primary-300">
                            {{ mb_substr($dealership->name, 0, 1) }}
                        </div>

                        <div>
                            <div class="text-base font-semibold text-gray-950 dark:text-white">
                                {{ $dealership->name }}
                            </div>

                            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ $dealership->email_address ?? 'No email configured' }}
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        Open
                    </div>
                </div>

                <div
                    class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4 text-sm dark:border-gray-800">
                    <span class="text-gray-500 dark:text-gray-400">
                        Continue to dashboard
                    </span>

                    <span class="text-primary-600 transition group-hover:translate-x-1 dark:text-primary-400">
                        →
                    </span>
                </div>
            </button>
        @endforeach
    </div>
</div>
