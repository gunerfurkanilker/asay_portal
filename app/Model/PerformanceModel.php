<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Library\Asay;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class PerformanceModel extends Model
{
    protected $primaryKey = 'Id';
    protected $table = 'performance';

    protected $appends = [
        'ManagerID',
        'EmployeeID',
        'DepartmentID',
        'TitleID',
        'RegionID',
        'CityID',
        'UnitManagerID',
        'EvaluationPeriod',
        'StatusID',
        
    ];

   

}
