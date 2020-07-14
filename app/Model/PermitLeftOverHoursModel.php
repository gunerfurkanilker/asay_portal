<?php

namespace App\Model;

use App\Model\PublicHolidayModel;
use Illuminate\Database\Eloquent\Model;
use DateTime;

class PermitLeftOverHoursModel extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'PermitLeftOverHours';
    public const CREATED_AT = 'create_date';
    public const UPDATED_AT = 'update_date';


    public static function getLeftOverHours($req)
    {

        $permitStartDate = new DateTime($req->startDate);
        $permitEndDate = new DateTime($req->endDate);

        $daysOfHolidaysNotAtWeekend =  PublicHolidayModel::getPublicHolidays($req->startDate,$req->endDate);

        $totalDayCount =(int) $permitStartDate->diff($permitEndDate)->format('%a');

        $totalHourCount = (int) $permitStartDate->diff($permitEndDate)->format('%h');

        $plainDayCount = $totalDayCount - $daysOfHolidaysNotAtWeekend;

        return $plainDayCount;



    }

}
