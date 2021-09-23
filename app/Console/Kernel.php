<?php

namespace App\Console;

use App\Library\Asay;
use App\Model\EmployeeModel;
use App\Model\EmployeeTrainingModel;
use App\Model\OvertimeModel;
use App\Model\OvertimePermissionModel;
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
        /*$schedule->call(function () {
            $textString = "";
            $blackListEmployees = EmployeeModel::where(['Active' => 1])
                ->whereIn("StaffID",[
                    8011846,
                    8011847,
                    8011763,
                    8011946,
                    8012003,
                    8012022
                ])
                ->get();
            foreach ($blackListEmployees as $employee)
            {

                $textString .= $employee->Id . ",\n";
            }
            echo $textString;

        })->everyMinute();
        $schedule->call(function () {
            $textString = "";
            $permission = OvertimePermissionModel::where(['PermissionTypeID' => 2])
                ->first();
            $permission->EmployeeIDs =  $string = str_replace(array("\n", "\r"), '', $permission->EmployeeIDs);
            $permission->save();
        })->everyMinute();*/

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
