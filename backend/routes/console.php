<?php
use Illuminate\Support\Facades\Schedule;

Schedule::command('customers:suspend-expired')->dailyAt('00:00');
Schedule::command('customers:expiry-reminders')->dailyAt('09:00');
Schedule::command('invoices:generate-monthly')->dailyAt('10:00');
Schedule::command('olt:poll')->everyFiveMinutes();
Schedule::command('olt:alert')->everyFiveMinutes();
Schedule::command('uplink:monitor')->everyMinute();
