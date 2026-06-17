<x-filament-panels::page>
    {{ $this->form }}

    @php
        $stats = $this->getStatistics();
        $summary = $stats['summary'];
        $pipeline = $stats['pipeline'];
        $tasks = $stats['tasks'];
    @endphp

    <div class="grid gap-4 md:grid-cols-5">
        <x-filament::section>
            <div class="text-sm text-gray-500">Total Leads</div>
            <div class="mt-2 text-3xl font-semibold">{{ $summary['total_leads'] }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">Won Deals</div>
            <div class="mt-2 text-3xl font-semibold">{{ $summary['won_deals'] }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">Conversion</div>
            <div class="mt-2 text-3xl font-semibold">{{ $summary['conversion_rate'] }}%</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">Revenue</div>
            <div class="mt-2 text-3xl font-semibold">${{ number_format($summary['revenue'], 2) }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">Expired Tasks</div>
            <div class="mt-2 text-3xl font-semibold">{{ $summary['expired_tasks'] }}</div>
        </x-filament::section>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">
                Pipeline Breakdown
            </x-slot>

            <div class="space-y-3">
                @foreach ($pipeline as $stage => $count)
                    <div
                        class="flex items-center justify-between border-b border-gray-200 pb-2 text-sm dark:border-gray-700">
                        <span>{{ $stage }}</span>
                        <span class="font-semibold">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Task Breakdown
            </x-slot>

            <div class="space-y-3">
                @foreach ($tasks as $status => $count)
                    <div
                        class="flex items-center justify-between border-b border-gray-200 pb-2 text-sm dark:border-gray-700">
                        <span>{{ $status }}</span>
                        <span class="font-semibold">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
