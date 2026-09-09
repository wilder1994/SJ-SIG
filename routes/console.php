<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function (): void {
    $this->comment('SJ-SIG');
})->purpose('Display an inspiring quote');
