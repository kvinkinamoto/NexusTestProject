<?php

return [
    'form' => 'form',
    'Form' => 'Form',
    'index_title' => 'Forms',
    'module_name' => 'Form',

    'name' => 'Internal name',
    'title' => 'Title',
    'slug' => 'Slug',
    'success_message' => 'Success message',
    'notify_email' => 'Notify email',
    'is_active' => 'Active',
    'main' => 'Main',
    'fields_section' => 'Fields',
    'submissions_section' => 'Submissions',

    'key' => 'Key',
    'label' => 'Label',
    'type' => 'Type',
    'required' => 'Required',
    'options' => 'Options (one per line)',

    'submission' => [
        'mail_subject' => 'New submission — :title',
        'mail_greeting' => 'You have a new submission on ":title"',
        'default_success' => 'Thank you — your submission was received.',
        'submit_label' => 'Submit',
    ],

    'docs' => [
        'title' => 'Form Builder',
        'subtitle' => 'Define public-facing forms, embed them with @nexusForm(), review submissions.',
        'usage_title' => 'Embedding a form',
        'usage_desc' => 'Create a Form, add its fields as rows in the Fields repeater, then embed it anywhere in a Blade view with @nexusForm(\'your-slug\'). Submissions land in the Form Submissions module and, if a notify email is set, in your inbox.',
        'tech_title' => 'Technical Details',
        'admin_part' => 'Admin Part',
        'api_part' => 'Public Part',
        'endpoints' => 'Endpoints',
        'fields' => 'Fields',
    ],
];
