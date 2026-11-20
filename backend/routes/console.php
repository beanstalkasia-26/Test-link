<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('content:publish-scheduled')->everyMinute()->withoutOverlapping();
Schedule::command('queue:prune-failed --hours=720')->daily();
Schedule::command('auth:clear-resets')->hourly();

Schedule::command('queue:work --queue=mail,media,default --stop-when-empty --max-time=50 --tries=3')
    ->everyMinute()->withoutOverlapping()->runInBackground()
    ->when(fn (): bool => config('queue.default') === 'database');
