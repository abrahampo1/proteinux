<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('federation:sync')->everyFifteenMinutes();
