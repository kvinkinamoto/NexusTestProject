<?php

/**
 * Coverage for task #8's security-hardening pass: neither /login nor
 * /api/login had any brute-force protection before this (confirmed via
 * audit — only 'web' middleware, no throttle). Both now carry throttle:5,1,
 * matching the limiter style already used for verification-email resend
 * (app/Nexus/Modules/Auth/routes/web.php).
 */
it('throttles repeated /login attempts after 5 per minute', function () {
    for ($i = 1; $i <= 5; $i++) {
        $response = $this->post('/login', ['email' => 'nobody@example.test', 'password' => 'wrong']);
        expect($response->status())->not->toBe(429);
    }

    $response = $this->post('/login', ['email' => 'nobody@example.test', 'password' => 'wrong']);

    $response->assertStatus(429);
});

it('throttles repeated /api/login attempts after 5 per minute', function () {
    for ($i = 1; $i <= 5; $i++) {
        $response = $this->postJson('/api/login', ['email' => 'nobody@example.test', 'password' => 'wrong']);
        expect($response->status())->not->toBe(429);
    }

    $response = $this->postJson('/api/login', ['email' => 'nobody@example.test', 'password' => 'wrong']);

    $response->assertStatus(429);
});
