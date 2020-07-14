<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DateTime;
use DateInterval;

class PublicHolidayModel extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'PublicHolidays';

    /* Hafta sonuna denk gelmeyen resmi tatilleri çekmek için.*/
    public static function getPublicHolidays($startDate,$endDate)
    {

        $daysOfHolidaysNotAtWeekend = 0;
        $sDate = new DateTime($startDate);
        $eDate = new DateTime($endDate);
        while($eDate->diff($sDate)->format('%a') != '0')//Başlangıç ve bitiş tarihleri eşitlenene kadar tatil günlerini kontrol ediyorum...
        {
           $holiday = self::where('start_date','>=',$sDate->format('Y-m-d'))->where('end_date','<=',$eDate->format('Y-m-d'))->get();
           $isPermitAtWeekend = self::isHolidayAtWeekend(date('w',strtotime($sDate->format('Y-m-d'))));
           $isPermitAtWeekend ? '' : $daysOfHolidaysNotAtWeekend++;
           //Üstteki satırda eğer resmi tatil hafta sonuna denk gelmiyor ise ilgili resmi tatili izin gününden düşüyorum.
           $sDate = $sDate->modify('+1 day');
        }
        return $daysOfHolidaysNotAtWeekend;
    }

    public static function isHolidayAtWeekend($day)
    {
        if($day == 6 || $day == 0) // 6 Cumartesi, 0 Pazarı temsil ediyor.
            return true;
        else
            return false;
    }

    public static function checkHolidayStartTime($holiday)
    {

    }

}
