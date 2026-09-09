<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// The old ZentaraStartProject reference module never actually wired this up
// (flagged as a gap in its own docs) — fixed while porting it to Nexus.
Schedule::command('nexus:sitemap:generate')->daily();
