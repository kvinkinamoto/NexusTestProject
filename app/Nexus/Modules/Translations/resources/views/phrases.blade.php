@extends('nexus::' . config('nexus.template') . '.layouts.adminpanel')

@section('contentPageCaption')
    {{ $id }}
@endsection

@section('mainContent')
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $id }}</h2>
        <a href="{{ route('nexus.module.action', ['module' => $module->name, 'action' => 'index']) }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-white/5">
            @lang('translations::translate.back to groups')
        </a>
    </div>

    <form method="GET" action="{{ route('nexus.module.action', ['module' => $module->name, 'action' => 'phrases', 'id' => $id]) }}"
        class="mb-4 flex flex-wrap items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.02]">
        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">@lang('translations::translate.show locales'):</span>
        @foreach($allLocales as $locale)
            <label class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="locales[]" value="{{ $locale->code }}" {{ in_array($locale->code, $selectedCodes) ? 'checked' : '' }}
                    onchange="this.form.submit()">
                {{ $locale->language }}
            </label>
        @endforeach
    </form>

    <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.02]">
        <table class="w-full text-left">
            <thead class="border-b border-gray-100 dark:border-white/5">
                <tr>
                    <th class="px-4 py-3 text-xs font-medium uppercase text-gray-400">@lang('translations::translate.key')</th>
                    @foreach($locales as $locale)
                        <th class="px-4 py-3 text-xs font-medium uppercase text-gray-400">{{ $locale->code }}</th>
                    @endforeach
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($keys as $key => $values)
                    <tr class="border-b border-gray-100 last:border-b-0 dark:border-white/5">
                        <td class="px-4 py-3 font-mono text-sm text-gray-700 dark:text-gray-300">{{ $key }}</td>
                        @foreach($locales as $locale)
                            @php($value = $values[$locale->code] ?? null)
                            <td class="px-4 py-3 text-sm {{ empty($value) ? 'text-error-500' : 'text-gray-600 dark:text-gray-300' }}">
                                {{ $value !== null && $value !== '' ? Str::limit($value, 60) : __('translations::translate.missing') }}
                            </td>
                        @endforeach
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('nexus.module.action', ['module' => $module->name, 'action' => 'edit', 'id' => $id, 'key' => $key]) }}"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-white/5">
                                <i class="{{ nexus_icon('edit') }}"></i>
                                @lang('translations::translate.edit')
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $locales->count() + 2 }}" class="p-6 text-center text-sm text-gray-400">
                            @lang('translations::translate.no keys found')
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
