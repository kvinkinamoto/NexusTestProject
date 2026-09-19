<?php

use App\Models\User;

/**
 * Regression test: User::$fillable/#[Field] already declared birthday and
 * gender (see User.php), but the base users migration never created either
 * column — any logged-in visit to /account/profile threw
 * MissingAttributeException reading $user->birthday. Same bug shape as the
 * earlier last_name/phone gap, fixed by
 * add_birthday_and_gender_to_users_table.
 */
it('renders the account profile page for a logged-in user', function () {
    $this->actingAs(User::factory()->create())
        ->get('/account/profile')
        ->assertOk();
});
