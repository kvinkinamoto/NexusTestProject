@extends('nexus::' . config('nexus.template') . '.layouts.adminpanel')

@section('contentPageCaption')
    @lang('settings::translate.index_title')
@endsection

@section('mainContent')
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
            @lang('settings::translate.index_title')
        </h2>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.02]">
        @forelse($modulesWithSettings as $entry)
            <div class="flex items-center justify-between gap-4 border-b border-gray-100 p-4 last:border-b-0 dark:border-white/5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-white/5">
                        <i class="{{ nexus_icon($entry->config->menu->icon ?? null, $entry->name, 'default_icon') }} text-lg text-gray-600 dark:text-gray-300"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $entry->config->menu->label ?? $entry->name }}</p>
                        <p class="text-xs text-gray-400">{{ count($entry->config->settings) }} {{ __('settings::translate.settings count suffix') }}</p>
                    </div>
                </div>
                <a href="{{ route('nexus.module.action', ['module' => $entry->name, 'action' => 'settings']) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-white/5">
                    <i class="{{ nexus_icon('edit') }}"></i>
                    @lang('settings::translate.manage')
                </a>
            </div>
        @empty
            <div class="p-6 text-center text-sm text-gray-400">
                @lang('settings::translate.no settings registered')
            </div>
        @endforelse
    </div>
@endsection
