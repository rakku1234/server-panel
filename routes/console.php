<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\ServerStatus;

Schedule::job(new ServerStatus)->everyThreeMinutes();
