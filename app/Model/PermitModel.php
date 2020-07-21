<?php

namespace App\Model;

use Cassandra\Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use DateTime;

class PermitModel extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'Permits';
    public $timestamps = false;

    public static function createPermit($req)
    {

        $user = UserModel::find(UserTokensModel::where('user_token', $req['token'])->first()->user_id);

        $newPermit = new PermitModel();

        $newPermit->EmployeeID = $user->EmployeeID;
        $newPermit->kind = $req['kind'];
        $newPermit->description = $req['description'];
        $newPermit->start_date = $req['startDate'];
        $newPermit->end_date = $req['endDate'];
        $newPermit->total_day = $req['totalDay'];
        $newPermit->duty_transferee = $req['dutyTransferee'];
        $newPermit->transfer_date = $req['transferDate'];
        return $newPermit->save() ? true : false;
    }

    public static function getRemainingDaysYearlyPermit($req)
    {
        $user = UserModel::find($req->userId);
        //Yıllık İzin için bu kontrolü yapıyoruz ileride diğer izin tipleri için de bu tarz kontroller yapılabilir.
        $permitCounts = self::select(DB::raw('SUM(total_day) as total_days,SUM(total_hours) as total_hours'))
            ->where('EmployeeID', $user->EmployeeID)
            ->where('kind', 12)->first();

        if ($permitCounts->total_hours % 8 > 0) {
            $leftOverDays = floor($permitCounts->total_hours / 8);
        }


        $data['hoursUsed'] = $permitCounts->total_hours % 8;
        $data['daysUsed'] = $permitCounts->total_days + $leftOverDays;
        $data['daysLeft'] = PermitKindModel::where('id', 12)->first()
            ->dayLimitPerYear ?
            PermitKindModel::find(12)->dayLimitPerYear - $data['daysUsed'] :
            PermitKindModel::find(12)->dayLimitPerRequest - $data['daysUsed'];

        if ($data['daysLeft'] != 0)
            $data['hoursLeft'] = 8 - $data['hoursUsed'];
        else
            $data['hoursLeft'] = 0;
        if ($data['hoursLeft'] > 0 && $data['daysLeft'] == 1)
            $data['daysLeft'] = 0;

        return $data;


    }

    public static function calculateTotalDayHourCount($startDateTimeParam, $endDateTimeParam)
    {
        //$interval->format('%y years %m months %a days %h hours %i minutes %s seconds');
        $data=[];
        $clearHourCount = 0;
        $clearDayCount = 0;
        $remainingPermitHours = 0;
        $weekendDays = 0;
        $publicHolidaysCount = 0;
        $publicHolidays = [];
        $dayCount = 0;


        $startDateTime = new DateTime(  $startDateTimeParam);
        $endDateTime = new DateTime($endDateTimeParam);
        $interval = $endDateTime->diff($startDateTime);


        /*
         *
         * ÖNCELİKLE BAYRAM GÜNLERİNİ VE HAFTA SONLARINI HARİÇ TOPLAM GÜN SAYISINI ($clearDayCount) arttırıyorum.
         * SONRA İZNİN BAŞLANGIÇ SAATİ VEYA BİTİŞ SAATİ 09:00 dan SONRA MI BUNU KONTROL EDİYORUM EĞER BÖYLE BİR DURUM VARSA $clearDayCount u azaltıyorum. çünkü her iki durumda da saat devretme durumu oluşacak
         * En sonda da saat devretme işlemlerini yaparak işlemleri tamamlıyorum.
         *
         *
         * */

        while( (int) $endDateTime->diff($startDateTime)->format('%a') > 0) // Haftasonu ve bayram tatili kontrolü.
        {
            $clearDayCount++;
            if ($startDateTime->format('D') == "Sun")
            {
                $weekendDays++;
                $clearDayCount--;
            }

            else if (count(self::isPermitAtPublicHoliday($startDateTime->format('Y-m-d H:i:s'))) != 0){
                $publicHolidaysCount++;
                $clearDayCount--;
                foreach (self::isPermitAtPublicHoliday( $startDateTime->format('Y-m-d H:i:s') ) as $key => $item)
                {
                    if ( count(self::isPermitAtPublicHoliday($startDateTime->format('Y-m-d H:i:s'))) > 1 )
                        array_push($publicHolidays,self::isPermitAtPublicHoliday( $startDateTime->format('Y-m-d H:i:s') )[0]);
                    else
                        array_push($publicHolidays,$item);
                }


            }
            $startDateTime->modify("+1 day");
        }


        $startDateTime = new DateTime(  $startDateTimeParam);
        $endDateTime = new DateTime($endDateTimeParam);


        if ((int) $startDateTime->format('H') > 9 && (int) $startDateTime->format('H') < 18)
        {
            $remainingPermitHours += (int) $startDateTime->format('H') - 9 ;
            if ((int) $startDateTime->format('H') >= 12 || (int) $startDateTime->format('H') >= 13) // Öğle arasından sonra izni başlıyor ise öğle arasında geçen zamanı çalışıyor saymıyorum.
                $remainingPermitHours--;

        }

        if ((int) $endDateTime->format('H') < 18 && (int) $endDateTime->format('H') > 9)
        {
            $remainingPermitHours +=  18 - (int) $endDateTime->format('H') ;
            if ((int) $endDateTime->format('H') <= 12) //Öğle arasından önce izni bitiyor ise öğle arasında olan saati çıkarıyorum.
                $remainingPermitHours--;

        }

        ((int) $startDateTime->format('H') > 9 && (int) $startDateTime->format('H') < 18) || ((int) $endDateTime->format('H') < 18 && (int) $endDateTime->format('H') > 9)
        ? $clearDayCount-- : '';


        $publicHolidays = array_unique($publicHolidays);

        foreach ($publicHolidays as $item)//Bu döngüyü arife günü için kuruyorum arife günü veya yarım gün çalışmalar için çalışılan saati izinden düşmek gerek.
        {
            $itemStartDate = new DateTime($item->start_date);
            $itemEndDate = new DateTime($item->end_date);
            if ((int) $itemStartDate->format('H') > 9)
            {
                //$publicHolidaysCount--;
                if ((int) $itemStartDate->format('H') >= 13)
                    $remainingPermitHours +=((int) $itemStartDate->format('H') - 9) - 1; // Öğle arasını çıkarıyorum.
                else
                    $remainingPermitHours += (int) $itemStartDate->format('H') - 9; // Öğle arasından önce ise -1 saat öğle arasını çıkarmama gerek yok.
            }
            if ((int) $itemEndDate->format('H') > 9)
            {
                //$publicHolidaysCount--;
                if ((int) $itemEndDate->format('H') >= 13)
                    $remainingPermitHours +=((int) $itemEndDate->format('H') - 9) - 1; // Öğle arasını çıkarıyorum.
                else
                    $remainingPermitHours += (int) $itemEndDate->format('H') - 9; // Öğle arasından önce ise -1 saat öğle arasını çıkarmama gerek yok.
            }
        }


        $clearHourCount = $remainingPermitHours >= 8 ? $remainingPermitHours - 8 : $remainingPermitHours;
        $remainingPermitHours > 8 ? $clearDayCount += floor($remainingPermitHours / 8) :'';
        $remainingPermitHours == 8 ? $clearDayCount++ : '';

        $data['clearDayCount'] = $clearDayCount;
        $data['clearHourCount'] = $clearHourCount;
        $data['publicHolidaysCount'] = $publicHolidaysCount;
        $data['weekendDays'] = $weekendDays;
        $data['remainingHours'] = $remainingPermitHours;
        return $data;

    }

    public static function isPermitAtPublicHoliday($permitDate)
    {
            $holidays = PublicHolidayModel::where('start_date','<=',$permitDate)->where('end_date','>=',$permitDate)->orderBy('start_date','asc')->get();
            return $holidays;
    }





}