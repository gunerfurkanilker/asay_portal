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
    public static function getPublicHolidays()
    {

    }


}
