<?php

use App\Nexus\Modules\Demo\FieldTypes\GeneratorRegressionCheck;
use App\Nexus\Modules\Demo\Filters\ModuleFilterHandler;
use App\Nexus\Modules\Demo\Models\Demo;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ViewErrorBag;
use Nodex\Nexus\Contracts\FieldTypeRenderer;
use Nodex\Nexus\Dto\FieldRenderContext;
use Nodex\Nexus\Dto\ModuleDtos\FieldConfigDto;
use Nodex\Nexus\Filters\FilterHandler;

/**
 * Coverage for task #6's two generator commands: nexus:make:field and
 * nexus:make:filter. Both scaffold into a real module's directory (Demo,
 * already installed below) and are cleaned up in a finally block, mirroring
 * WidgetSystemTest's "nexus:make:widget generates a class that actually
 * implements WidgetInterface" test.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();
});

it('nexus:make:field generates a class implementing FieldTypeRenderer with a working companion view', function () {
    $fieldTypesDir = app_path('Nexus/Modules/Demo/FieldTypes');
    $classFile = "{$fieldTypesDir}/GeneratorRegressionCheck.php";
    $viewFile = "{$fieldTypesDir}/generatorRegressionCheck.blade.php";

    if (File::exists($fieldTypesDir)) {
        File::deleteDirectory($fieldTypesDir);
    }

    try {
        $this->artisan('nexus:make:field', ['name' => 'generatorRegressionCheck', '--module' => 'Demo'])
            ->assertSuccessful();

        expect(File::exists($classFile))->toBeTrue()
            ->and(File::exists($viewFile))->toBeTrue();

        require $classFile;
        $class = GeneratorRegressionCheck::class;

        expect(is_subclass_of($class, FieldTypeRenderer::class))->toBeTrue();

        // $errors is normally shared by the 'web' middleware group
        // (ShareErrorsFromSession) on every real admin-form request; share
        // it manually here since this test renders the view directly,
        // outside of an HTTP request.
        view()->share('errors', new ViewErrorBag);

        $context = new FieldRenderContext(
            field: new FieldConfigDto(name: 'title', type: 'generatorRegressionCheck', label: 'Title'),
            model: null,
            module: new stdClass,
            action: 'create',
            tabLang: null,
            formData: ['title' => 'Hello world'],
            modelSchema: [],
        );

        $rendered = (string) (new $class)->render($context);

        expect($rendered)->toContain('name="title"')
            ->and($rendered)->toContain('Hello world');
    } finally {
        File::deleteDirectory($fieldTypesDir);
    }
});

it('nexus:make:field fails when --module is omitted', function () {
    $this->artisan('nexus:make:field', ['name' => 'x'])->assertFailed();
});

it('nexus:make:field fails when --module points at a non-existent module', function () {
    $this->artisan('nexus:make:field', ['name' => 'x', '--module' => 'NonexistentModule'])
        ->assertFailed();
});

it('nexus:make:field does not overwrite an existing field type', function () {
    $fieldTypesDir = app_path('Nexus/Modules/Demo/FieldTypes');
    $classFile = "{$fieldTypesDir}/GeneratorRegressionCheck.php";

    if (File::exists($fieldTypesDir)) {
        File::deleteDirectory($fieldTypesDir);
    }

    try {
        $this->artisan('nexus:make:field', ['name' => 'generatorRegressionCheck', '--module' => 'Demo'])
            ->assertSuccessful();

        File::put($classFile, '<?php // sentinel');

        $this->artisan('nexus:make:field', ['name' => 'generatorRegressionCheck', '--module' => 'Demo'])
            ->assertFailed();

        expect(File::get($classFile))->toBe('<?php // sentinel');
    } finally {
        File::deleteDirectory($fieldTypesDir);
    }
});

it('nexus:make:filter generates a ModuleFilterHandler that still applies the inherited search filter', function () {
    $filtersDir = app_path('Nexus/Modules/Demo/Filters');
    $classFile = "{$filtersDir}/ModuleFilterHandler.php";

    if (File::exists($filtersDir)) {
        File::deleteDirectory($filtersDir);
    }

    try {
        $this->artisan('nexus:make:filter', ['module' => 'Demo'])->assertSuccessful();

        expect(File::exists($classFile))->toBeTrue();

        require $classFile;
        $class = ModuleFilterHandler::class;

        expect(is_subclass_of($class, FilterHandler::class))->toBeTrue();

        Demo::create(['title' => 'Findable filter target']);
        Demo::create(['title' => 'Unrelated record']);

        $results = (new $class)->filter(Demo::query(), 'search', 'Findable')->get();

        expect($results)->toHaveCount(1)
            ->and($results->first()->title)->toBe('Findable filter target');
    } finally {
        File::deleteDirectory($filtersDir);
    }
});

it('nexus:make:filter fails when the module does not exist', function () {
    $this->artisan('nexus:make:filter', ['module' => 'NonexistentModule'])->assertFailed();
});

it('nexus:make:filter does not overwrite an existing handler', function () {
    $filtersDir = app_path('Nexus/Modules/Demo/Filters');
    $classFile = "{$filtersDir}/ModuleFilterHandler.php";

    if (File::exists($filtersDir)) {
        File::deleteDirectory($filtersDir);
    }

    try {
        $this->artisan('nexus:make:filter', ['module' => 'Demo'])->assertSuccessful();

        File::put($classFile, '<?php // sentinel');

        $this->artisan('nexus:make:filter', ['module' => 'Demo'])->assertFailed();

        expect(File::get($classFile))->toBe('<?php // sentinel');
    } finally {
        File::deleteDirectory($filtersDir);
    }
});
