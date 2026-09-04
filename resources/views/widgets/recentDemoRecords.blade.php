<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.02]">
    <h3 class="mb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Recent Demo Records</h3>

    @if($records->isEmpty())
        <p class="text-sm text-gray-400">No records yet.</p>
    @else
        <ul class="divide-y divide-gray-100 dark:divide-white/5">
            @foreach($records as $record)
                <li class="flex items-center justify-between py-2 text-sm">
                    <span class="text-gray-700 dark:text-gray-300">{{ $record->title }}</span>
                    @php
                        $statusClass = match ($record->status?->value ?? $record->status) {
                            'published' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400',
                            'archived' => 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400',
                            default => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400',
                        };
                    @endphp
                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClass }}">
                        {{ $record->status?->value ?? $record->status }}
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
