<?php

return [
    'confirmfixturenote' => 'confirmfixturenote',
    'ConfirmFixtureNote' => 'ConfirmFixtureNote',
    'index_title' => 'ConfirmFixtureNotes',
    'body' => 'body',
    'module_name' => 'ConfirmFixtureNote',
    'edit' => 'Edit',
    'delete' => 'Delete',

    // Labels for the field/column this module attaches onto ConfirmFixture
    // via #[AttachField]/#[AttachColumn] (see
    // app/Nexus/Plugins/ConfirmFixtureNote/ConfirmFixtureNotePlugin.php) —
    // kept here, in the attaching module's own lang file, rather than in
    // ConfirmFixture's, so ConfirmFixture never needs editing for this to
    // read as a real label instead of a raw translation key.
    'notes' => 'Notes',
];
