@extends('nexus::' . config('nexus.template') . '.layouts.adminpanel')

@section('contentPageCaption')
    @lang('productRelease::translate.upload_release.title')
@endsection

@section('mainContent')
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
            @lang('productRelease::translate.upload_release.title')
        </h2>
        <a href="{{ route('nexus.module.action', ['module' => $module->name, 'action' => 'index']) }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-white/5">
            @lang('productRelease::translate.upload_release.back')
        </a>
    </div>

    <form method="POST" action="{{ route('nexus.module.action', ['module' => $module->name, 'action' => 'uploadRelease']) }}"
        enctype="multipart/form-data"
        class="max-w-xl rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.02]">
        @csrf

        <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">
            @lang('productRelease::translate.upload_release.help')
        </p>

        <div class="flex flex-col gap-5">
            <div>
                <label for="product_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    @lang('productRelease::translate.upload_release.product')
                </label>
                <select name="product_id" id="product_id"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-white/5 dark:text-white/90">
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected((int) old('product_id') === $product->id)>{{ $product->name }}</option>
                    @endforeach
                </select>
                @error('product_id')
                    <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="version" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    @lang('productRelease::translate.upload_release.version')
                </label>
                <input type="text" name="version" id="version" value="{{ old('version') }}" placeholder="1.0.0"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-white/5 dark:text-white/90">
                @error('version')
                    <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="min_core_version" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        @lang('productRelease::translate.upload_release.min_core_version')
                    </label>
                    <input type="text" name="min_core_version" id="min_core_version" value="{{ old('min_core_version') }}" placeholder="2.0"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-white/5 dark:text-white/90">
                    @error('min_core_version')
                        <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="max_core_version" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        @lang('productRelease::translate.upload_release.max_core_version')
                    </label>
                    <input type="text" name="max_core_version" id="max_core_version" value="{{ old('max_core_version') }}" placeholder="3.0"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-white/5 dark:text-white/90">
                    @error('max_core_version')
                        <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="changelog" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    @lang('productRelease::translate.upload_release.changelog')
                </label>
                <textarea name="changelog" id="changelog" rows="4"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-white/5 dark:text-white/90">{{ old('changelog') }}</textarea>
                @error('changelog')
                    <p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="archive" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    @lang('productRelease::translate.upload_release.archive')
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
                @lang('productRelease::translate.upload_release.submit')
            </button>
        </div>
    </form>
@endsection
