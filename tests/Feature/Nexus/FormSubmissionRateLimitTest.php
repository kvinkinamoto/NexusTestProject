<?php

use App\Nexus\Modules\Form\Models\Form;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Form'])->run();
    $this->artisan('nexus:module:install', ['name' => 'FormSubmission'])->run();

    // Isolate this test's throttle bucket from anything a previous test in
    // the same run may have hit — Laravel's default throttle key is
    // sha1(route domain + IP), not scoped to this route alone.
    Cache::flush();

    $this->form = Form::create([
        'name' => 'Contact',
        'title' => 'Contact us',
        'slug' => 'rate-limit-contact',
        'is_active' => true,
    ]);
    $this->form->fields()->create(['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => false, 'order' => 0]);
});

it('throttles submissions past 10 per minute', function () {
    for ($i = 0; $i < 10; $i++) {
        $this->post(route('nexus.form.submit', $this->form->slug), ['email' => "test{$i}@example.test"])
            ->assertStatus(302);
    }

    $this->post(route('nexus.form.submit', $this->form->slug), ['email' => 'eleventh@example.test'])
        ->assertStatus(429);
});
