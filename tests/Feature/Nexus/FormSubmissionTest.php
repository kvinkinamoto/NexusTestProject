<?php

use App\Nexus\Modules\Form\Models\Form;
use App\Nexus\Modules\Form\Notifications\FormSubmittedNotification;
use App\Nexus\Modules\FormSubmission\Models\FormSubmission;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Form'])->run();
    $this->artisan('nexus:module:install', ['name' => 'FormSubmission'])->run();

    $this->form = Form::create([
        'name' => 'Contact',
        'title' => 'Contact us',
        'slug' => 'contact',
        'is_active' => true,
        'notify_email' => 'owner@example.test',
        'success_message' => 'Thanks, we got it.',
    ]);
    $this->form->fields()->create(['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'order' => 0]);
    $this->form->fields()->create(['key' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => false, 'order' => 1]);
});

it('stores a valid submission and notifies the configured email', function () {
    Notification::fake();

    $response = $this->post(route('nexus.form.submit', $this->form->slug), [
        'email' => 'visitor@example.test',
        'message' => 'Hello there',
    ]);

    $response->assertRedirect()->assertSessionHas('nexus_form_success', 'Thanks, we got it.');

    $submission = FormSubmission::where('form_id', $this->form->id)->firstOrFail();
    expect($submission->data)->toMatchArray(['email' => 'visitor@example.test', 'message' => 'Hello there']);

    Notification::assertSentOnDemand(FormSubmittedNotification::class);
});

it('rejects a submission missing a required field and stores nothing', function () {
    $response = $this->post(route('nexus.form.submit', $this->form->slug), [
        'message' => 'no email given',
    ]);

    $response->assertSessionHasErrors('email');
    expect(FormSubmission::where('form_id', $this->form->id)->count())->toBe(0);
});

it('silently drops a honeypot-filled submission without storing it', function () {
    $response = $this->post(route('nexus.form.submit', $this->form->slug), [
        'email' => 'bot@example.test',
        '_hp' => 'i-am-a-bot',
    ]);

    $response->assertRedirect();
    expect(FormSubmission::where('form_id', $this->form->id)->count())->toBe(0);
});

it('returns 404 for an inactive form\'s submit route', function () {
    $this->form->update(['is_active' => false]);

    $response = $this->post(route('nexus.form.submit', $this->form->slug), [
        'email' => 'visitor@example.test',
    ]);

    $response->assertNotFound();
});
