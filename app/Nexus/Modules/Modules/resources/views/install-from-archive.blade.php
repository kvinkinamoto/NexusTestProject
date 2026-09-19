@extends('nexus::' . config('nexus.template') . '.layouts.adminpanel')

@section('contentPageCaption')
    @lang('modules::translate.install_from_archive.title')
@endsection

@section('mainContent')
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
            @lang('modules::translate.install_from_archive.title')
        </h2>
        <a href="{{ route('nexus.module.action', ['module' => $module->name, 'action' => 'index']) }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-white/5">
            @lang('modules::translate.install_from_archive.back')
        </a>
    </div>

    <form method="POST" action="{{ route('nexus.module.action', ['module' => $module->name, 'action' => 'installFromArchive']) }}"
        enctype="multipart/form-data"
        class="max-w-xl rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.02]">
        @csrf

        <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">
            @lang('modules::translate.install_from_archive.help')
        </p>

        <div class="flex flex-col gap-5">
            <div>
                <label for="type" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    @lang('modules::translate.install_from_archive.type')
                </label>
                <select name="type" id="type"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-white/5 dark:text-white/90">
                    <option value="module" @selected(old('type', 'module') === 'module')>@lang('modules::translate.install_from_archive.type_module')</option>
                    <option value="plugin" @selected(old('type') === 'plugin')>@lang('modules::translate.install_from_archive.type_plugin')</option>
                    <option value="widget" @selected(old('type') === 'widget')>@lang('modules::translate.install_from_archive.type_widget')</option>
                </select>
                @error('type')
                    <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="archive" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    @lang('modules::translate.install_from_archive.archive')
                </label>
                <input type="file" name="archive" id="archive" accept=".zip"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-white/5 dark:text-white/90">
                @error('archive')
                    <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                @lang('modules::translate.install_from_archive.submit')
            </button>
        </div>
    </form>
@endsection
