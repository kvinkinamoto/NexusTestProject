@extends('nexus::' . config('nexus.template') . '.layouts.adminpanel')

@section('contentPageCaption')
    @lang('translations::translate.index_title')
@endsection

@section('mainContent')
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
            @lang('translations::translate.index_title')
        </h2>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.02]">
        <table class="w-full text-left">
            <thead class="border-b border-gray-100 dark:border-white/5">
                <tr>
                    <th class="px-4 py-3 text-xs font-medium uppercase text-gray-400">@lang('translations::translate.group')</th>
                    <th class="px-4 py-3 text-xs font-medium uppercase text-gray-400">@lang('translations::translate.keys')</th>
                    <th class="px-4 py-3 text-xs font-medium uppercase text-gray-400">@lang('translations::translate.complete')</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($groups as $group)
                    <tr class="border-b border-gray-100 last:border-b-0 dark:border-white/5">
                        <td class="px-4 py-3">
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $group['name'] }}</p>
                            <p class="text-xs text-gray-400">{{ $group['namespace'] ? $group['namespace'] . '::' : __('translations::translate.global') }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $group['total_keys'] }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                    <div class="h-full rounded-full {{ $group['percentage'] >= 100 ? 'bg-success-500' : 'bg-brand-500' }}" style="width: {{ $group['percentage'] }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $group['percentage'] }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('nexus.module.action', ['module' => $module->name, 'action' => 'phrases', 'id' => $group['id']]) }}"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-white/5">
                                <i class="{{ nexus_icon('eye') }}"></i>
                                @lang('translations::translate.browse')
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-6 text-center text-sm text-gray-400">
                            @lang('translations::translate.no groups found')
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
