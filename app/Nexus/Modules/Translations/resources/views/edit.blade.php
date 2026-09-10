@extends('nexus::' . config('nexus.template') . '.layouts.adminpanel')

@section('contentPageCaption')
    {{ $key }}
@endsection

@section('mainContent')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $key }}</h2>
            <p class="text-xs text-gray-400">{{ $id }}</p>
        </div>
        <a href="{{ route('nexus.module.action', ['module' => $module->name, 'action' => 'phrases', 'id' => $id]) }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-white/5">
            @lang('translations::translate.back to keys')
        </a>
    </div>

    <form method="POST" action="{{ route('nexus.module.action', ['module' => $module->name, 'action' => 'update', 'id' => $id]) }}"
        class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.02]">
        @csrf
        <input type="hidden" name="key" value="{{ $key }}">

        <div class="flex flex-col gap-5">
            @foreach($locales as $locale)
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ $locale->language }} ({{ $locale->code }})
                    </label>
                    <textarea name="translations[{{ $locale->code }}]" rows="3"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-white/5 dark:text-white/90"
                        dir="{{ $locale->is_rtl ? 'rtl' : 'ltr' }}">{{ $translations[$locale->code] ?? '' }}</textarea>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                @lang('translations::translate.save')
            </button>
        </div>
    </form>
@endsection
