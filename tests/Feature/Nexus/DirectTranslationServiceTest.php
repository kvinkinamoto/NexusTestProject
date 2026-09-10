<?php

use App\Nexus\Modules\Language\Models\Language;
use Illuminate\Support\Facades\File;
use Nodex\Nexus\Services\DirectTranslationService;

/**
 * Exercises Nodex\Nexus\Services\DirectTranslationService — this already
 * existed in the core package (singleton-bound, already reading the
 * Language model) before the Translations module's admin screens were
 * built on top of it, so this is real, previously-untested framework code,
 * not something new this phase wrote from scratch.
 *
 * Deliberately never writes into a real module's own resources/lang (would
 * risk corrupting real shipped translations) — the write-path tests target
 * a throwaway 'zzzTestFixture' namespace under lang/vendor/, cleaned up in
 * afterEach() regardless of pass/fail.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Language'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Backup'])->run();
});

afterEach(function () {
    File::deleteDirectory(lang_path('vendor/zzzTestFixture'));
});

it('lists a real installed module\'s translate.php as a group with a completion percentage', function () {
    Language::create(['language' => 'English', 'code' => 'en', 'publish' => true]);
    Language::create(['language' => 'Ukrainian', 'code' => 'uk', 'publish' => true]);

    $groups = app(DirectTranslationService::class)->getGroups();

    expect($groups)->toHaveKey('Backup::translate');
    expect($groups['Backup::translate']['namespace'])->toBe('Backup');
    expect($groups['Backup::translate']['total_keys'])->toBeGreaterThan(0);
    expect($groups['Backup::translate']['percentage'])->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
});

it('flattens nested keys with dot notation via getKeys()', function () {
    Language::create(['language' => 'English', 'code' => 'en', 'publish' => true]);

    $keys = app(DirectTranslationService::class)->getKeys('Backup::translate');

    expect($keys)->toHaveKey('docs.title');
    expect($keys['docs.title']['en'])->toBeString()->not->toBeEmpty();
});

it('falls back to en/uk active locales when no language is published', function () {
    $keys = app(DirectTranslationService::class)->getKeys('Backup::translate');

    expect($keys['docs.title'])->toHaveKeys(['en', 'uk']);
});

it('writes a new key under lang/vendor/{namespace} for a namespace with no real module, without touching existing keys', function () {
    Language::create(['language' => 'English', 'code' => 'en', 'publish' => true]);

    app(DirectTranslationService::class)->saveKey('zzzTestFixture::translate', 'greeting', ['en' => 'Hello']);
    app(DirectTranslationService::class)->saveKey('zzzTestFixture::translate', 'farewell', ['en' => 'Bye']);

    $path = lang_path('vendor/zzzTestFixture/en/translate.php');
    expect(File::exists($path))->toBeTrue();

    $data = File::getRequire($path);
    expect($data)->toBe(['greeting' => 'Hello', 'farewell' => 'Bye']);
});

it('skips an empty submitted value instead of overwriting an existing translation', function () {
    Language::create(['language' => 'English', 'code' => 'en', 'publish' => true]);

    app(DirectTranslationService::class)->saveKey('zzzTestFixture::translate', 'greeting', ['en' => 'Hello']);
    app(DirectTranslationService::class)->saveKey('zzzTestFixture::translate', 'greeting', ['en' => '']);

    $data = File::getRequire(lang_path('vendor/zzzTestFixture/en/translate.php'));
    expect($data['greeting'])->toBe('Hello');
});

it('writes a nested dot-notation key as a nested array', function () {
    Language::create(['language' => 'English', 'code' => 'en', 'publish' => true]);

    app(DirectTranslationService::class)->saveKey('zzzTestFixture::translate', 'docs.title', ['en' => 'Fixture module']);

    $data = File::getRequire(lang_path('vendor/zzzTestFixture/en/translate.php'));
    expect($data)->toBe(['docs' => ['title' => 'Fixture module']]);
});
