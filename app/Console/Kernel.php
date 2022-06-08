<?php

namespace App\Console;

use App\Http\Controllers\Api\Processes\HESCodeController;
use App\Library\Asay;
use App\Model\EmployeeModel;
use App\Model\EmployeeTrainingModel;
use App\Model\HESCodeModel;
use App\Model\OvertimeModel;
use App\Model\OvertimePermissionModel;
use Carbon\Carbon;
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
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
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
        $schedule->call(function () {
            $ctl = new HESCodeController();
            foreach (HESCodeModel::all() as $hesCode)
                $ctl->checkHesCode($hesCode);
        })->daily();


        $schedule->call(function () {
            $ctl = new HESCodeController();
            foreach (HESCodeModel::where('positiveDate','<',Carbon::now()->subMinutes())->get() as $hesCode){
                $hesCode->update([
                   'positiveStatus'=>0
                ]);
                $ctl->checkHesCode($hesCode);
            }
        })->weekly();

        $schedule->call(function () {
            foreach (HESCodeModel::all() as $hesCode){

                $expireDate=Carbon::parse($hesCode->ExpireDate);
                if($expireDate->diffInDays(Carbon::now())==3){
                                Asay::sendMail($hesCode->employee->JobEmail,"","Uyarı",'Sayın '. $hesCode->employee->FirstName.' '.$hesCode->employee->LastName.',
asaY Connect Platformunda bulunan HES Kodunuzun geçerlilik tarihinin bitmesine 2 gün kalmış olup, Toplum Sağlığı ve İş Sağlığı ve Güvenliği nedeni ile HES Kodunuzun sistemde en kısa sürede güncellenmesi gerekmektedir.

asaY Connect',"aSAY Group","","","");
                }
            }
        })->dailyAt('10:00');



        $schedule->call(function () {

            foreach (EmployeeModel::doesntHave('hescode')->get() as $employee)
                Asay::sendMail($employee->JobEmail,"","asaY Connect Uyarı",'Sayın '.($employee->FirstName ?? '').($employee->LastName ?? '').',
asaY Connect Platformunda bulunan HES Kodunuz tarafınızca eklenmemiş olup Toplum Sağlığı ve İş Sağlığı ve Güvenliği nedeni ile HES Kodunuzun sisteme eklenmesi gerekmektedir.
',"asaY Connect","","","");

        })->dailyAt('09:00');


        $schedule->call(function () {
            \App\Model\EmployeeTrainingModel::sendExpiredTrainingsMailToIsgEmployees();
            \App\Model\EmployeeTrainingModel::sendExpiredTrainingsMailToIsgEmployees2();
        })->dailyAt('11:00');

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
