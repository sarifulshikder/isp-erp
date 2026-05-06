<?php
use Illuminate\Support\Facades\Schedule;

// Daily midnight: suspend expired customers
Schedule::command('customers:suspend-expired')->dailyAt('00:00');

// Daily 9 AM: send expiry reminders
Schedule::command('customers:expiry-reminders')->dailyAt('09:00');
