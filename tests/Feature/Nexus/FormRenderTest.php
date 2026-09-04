<?php

use App\Nexus\Modules\Form\Models\Form;
use Illuminate\Support\Facades\Blade;

/**
 * Coverage for the @nexusForm() Blade directive (NexusServiceProvider.php,
 * next to @position()) — proves a Form + its FormField repeater rows render
 * as real HTML inputs without going through any page route.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Form'])->run();
    $this->artisan('nexus:module:install', ['name' => 'FormSubmission'])->run();
});

it('renders a form\'s fields through @nexusForm', function () {
    $form = Form::create([
        'name' => 'Contact',
        'title' => 'Contact us',
        'slug' => 'contact',
        'is_active' => true,
    ]);
    $form->fields()->create(['key' => 'email', 'label' => 'Your email', 'type' => 'email', 'required' => true, 'order' => 0]);
    $form->fields()->create(['key' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => false, 'order' => 1]);

    $html = Blade::render("@nexusForm('contact')");

    expect($html)->toContain('Your email')
        ->and($html)->toContain('name="email"')
        ->and($html)->toContain('name="message"')
        ->and($html)->toContain('action="'.route('nexus.form.submit', 'contact').'"');
});

it('renders nothing for an unknown or inactive slug', function () {
    expect(trim(Blade::render("@nexusForm('does-not-exist')")))->toBe('');

    Form::create(['name' => 'Draft', 'title' => 'Draft form', 'slug' => 'draft', 'is_active' => false]);

    expect(trim(Blade::render("@nexusForm('draft')")))->toBe('');
});
