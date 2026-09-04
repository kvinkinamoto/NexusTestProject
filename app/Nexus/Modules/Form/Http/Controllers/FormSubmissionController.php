<?php

namespace App\Nexus\Modules\Form\Http\Controllers;

use App\Nexus\Modules\Form\Models\Form;
use App\Nexus\Modules\Form\Models\FormField;
use App\Nexus\Modules\Form\Notifications\FormSubmittedNotification;
use App\Nexus\Modules\FormSubmission\Models\FormSubmission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Notification;

class FormSubmissionController extends Controller
{
    /**
     * Hidden input name checked for the honeypot. A human never fills a
     * field they can't see; a bot filling every input does. Filled ->
     * pretend success without validating or storing anything, same
     * silent-drop behavior most spam middlewares use.
     */
    private const HONEYPOT_FIELD = '_hp';

    public function store(Request $request, string $slug): RedirectResponse
    {
        $form = Form::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();

        if (filled($request->input(self::HONEYPOT_FIELD))) {
            return $this->redirectWithSuccess($form);
        }

        $fields = $form->fields;

        $data = $request->validate($this->buildRules($fields));

        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'data' => $data,
            'ip_address' => $request->ip(),
        ]);

        if ($form->notify_email) {
            Notification::route('mail', $form->notify_email)
                ->notify(new FormSubmittedNotification($form, $submission));
        }

        nexus_action('nexus.form.submitted', $form, $submission);

        return $this->redirectWithSuccess($form);
    }

    /**
     * @param  Collection<int, FormField>  $fields
     * @return array<string, array<int, string>>
     */
    private function buildRules($fields): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $fieldRules = [$field->required ? 'required' : 'nullable'];

            $fieldRules[] = match ($field->type) {
                'email' => 'email',
                'number' => 'numeric',
                'date' => 'date',
                'select', 'radio' => 'in:'.implode(',', $field->optionsList()),
                'checkbox' => 'array',
                default => 'string',
            };

            $rules[$field->key] = $fieldRules;

            if ($field->type === 'checkbox') {
                $rules[$field->key.'.*'] = ['in:'.implode(',', $field->optionsList())];
            }
        }

        return $rules;
    }

    private function redirectWithSuccess(Form $form): RedirectResponse
    {
        return redirect()->back()->with(
            'nexus_form_success',
            $form->success_message ?: __('form::translate.submission.default_success'),
        );
    }
}
