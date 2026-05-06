<?php
use Illuminate\Support\Facades\Schedule;

// Daily midnight: suspend expired customers
Schedule::command('customers:suspend-expired')->dailyAt('00:00');

// Daily 9 AM: send expiry reminders
Schedule::command('customers:expiry-reminders')->dailyAt('09:00');

// Daily 10 AM: auto generate invoices
Schedule::command('invoices:generate-monthly')->dailyAt('10:00');
