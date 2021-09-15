<?php

namespace App\Console;

use App\Library\Asay;
use App\Model\EmployeeTrainingModel;
use App\Model\OvertimeModel;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\PermitSenNetsis'
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
       // $schedule->command('permit:sendNetsis')->hourly();

        //$schedule->call('App\Http\Controllers\Api\Processes\TrainingController@sendExpiredTrainingsMailToIsgEmployees')->everyMinute();
        //$schedule->call('App\Http\Controllers\Api\Processes\TrainingController@sendExpiredTrainingsMailToIsgEmployees2')->everyMinute();


    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
