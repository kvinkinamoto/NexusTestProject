<?php

use App\Models\User;

/**
 * Coverage for the `App\Models\User` hardcode fix in
 * packages/nodex/nexus/src/commands/IssueApiToken.php (task #7 open-core
 * split) — it now resolves the user model via
 * config('auth.providers.users.model') instead, and the issued token is
 * labeled 'api' (generic — shared by REST and any installed API plugin,
 * e.g. GraphQL) rather than the old GraphQL-specific 'graphql' label.
 */
it('issues a Sanctum token for an existing user', function () {
    $user = User::factory()->create(['email' => 'token-holder@example.test']);

    $this->artisan('nexus:api-token:issue', ['email' => 'token-holder@example.test'])
        ->expectsOutputToContain('Token issued:')
        ->assertSuccessful();

    expect($user->tokens()->count())->toBe(1)
        ->and($user->tokens()->first()->name)->toBe('api');
});

it('fails cleanly for an unknown email', function () {
    $this->artisan('nexus:api-token:issue', ['email' => 'nobody@example.test'])
        ->expectsOutputToContain('No user found')
        ->assertFailed();
});
