<?php

namespace App\Model;

use Carbon\Carbon;
use Cassandra\Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use DateTime;

class PermitModel extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'Permits';
    public $timestamps = false;

    protected $appends = [
        'PermitKind',
        'TransferEmployee'
    ];

    public static function createPermit($req)
    {
        $totalPermitDayHour = self::calculatePermit($req->startDate,$req->endDate);
        if($req->permitId!==null){
            $EmployeeID = PermitModel::find($req->permitId)->EmployeeID;
            $newPermit = PermitModel::find($req->permitId);
        }
        else{
            $EmployeeID = $req->Employee;
            $newPermit = new PermitModel();
        }



        $newPermit->EmployeeID  = $EmployeeID;
        $newPermit->kind        = $req->kind;
        $newPermit->description = $req->description;
        $newPermit->start_date  = $req->startDate;
        $newPermit->end_date    = $req->endDate;
        $newPermit->transfer_id = $req->transfer_id;
        $newPermit->used_day    = $totalPermitDayHour['UsedDay'];
        $newPermit->over_hour   = $totalPermitDayHour['OverHour'];
        $newPermit->holiday     = $totalPermitDayHour['Holidays'];
        $newPermit->weekend     = $totalPermitDayHour['Weekend'];
        return $newPermit->save() ? $newPermit->fresh() : false;
    }

    /*public static function getRemainingDaysYearlyPermit($req)
    {
        $user = UserModel::find($req->userId);
        //Yıllık İzin için bu kontrolü yapıyoruz ileride diğer izin tipleri için de bu tarz kontroller yapılabilir.
        $permitCounts = self::select(DB::raw('SUM(total_day) as total_days,SUM(total_hours) as total_hours'))
            ->where('EmployeeID', $user->EmployeeID)
            ->where('kind', $req->kind)->first();

        if ($permitCounts->total_hours % 8 > 0) {
            $leftOverDays = floor($permitCounts->total_hours / 8);
        }


        $data['hoursUsed'] = $permitCounts->total_hours % 8;
        $data['daysUsed'] = $permitCounts->total_days + $leftOverDays;
        $data['daysLeft'] = PermitKindModel::where('id', $req->kind)->first()
            ->dayLimitPerYear ?
            PermitKindModel::find($req->kind)->dayLimitPerYear - $data['daysUsed'] :
            PermitKindModel::find($req->kind)->dayLimitPerRequest - $data['daysUsed'];

        if ($data['daysLeft'] != 0)
            $data['hoursLeft'] = 8 - $data['hoursUsed'];
        else
            $data['hoursLeft'] = 0;
        if ($data['hoursLeft'] > 0 && $data['daysLeft'] == 1)
            $data['daysLeft'] = 0;

        return $data;


    }*/

    public static function calculatePermit($startDate,$endDate)
    {
        $holidays = PublicHolidayModel::where(["active"=>1])->get();

        foreach ($holidays as $holiday) {
            $ResmiTatil[] = $holiday;
        }

        $st = strtotime($startDate);
        $saat       = 0;
        $hssaat     = 0;
        $rssaat     = 0;
        while($st < strtotime($endDate) ){
            $st += 3600;
            $dd = strtotime("1970-01-01 ".date("H:i:s",$st));
            $resmi = 0;
            foreach($ResmiTatil as $key => $value)
            {
                if($st>strtotime($value["start_date"]) && $st<strtotime($value["end_date"]))
                {
                    $resmi=1;
                    if($dd>21600 && $dd<=54000)
                        $rssaat++;
                    continue;
                }
            }
            if($resmi==1) continue;
            if(date("l",$st)=="Sunday")
            {
                if($dd>21600 && $dd<=54000)
                    $hssaat++;

                continue;
            }

            //echo date("H:i:s",$st)."-".$dd."<br>";

            if($dd>21600 && $dd<=54000)
            {
                if($dd>32400 && $dd<=36000){
                    continue;
                }
                else{
                    $saat++;
                }
            }
        }

        $devirSaat 		= $saat % 8;
        $aktarilacakGun = floor($saat / 8);

        $haftasonu 	= floor($hssaat/8);
        $resmigun 	= floor($rssaat/8);

        return [
            "UsedDay" => $aktarilacakGun,
            "OverHour" => $devirSaat,
            "Weekend" => $haftasonu,
            "Holidays" => $resmigun
        ];
    }

    public static function calculateTotalDayHourCount2($startDateTimeParam, $endDateTimeParam)
    {
        exit;
        //$interval->format('%y years %m months %a days %h hours %i minutes %s seconds');
        $data=[];
        $clearHourCount = 0;
        $clearDayCount = 0;
        $usedPermitHours = 0;
        $weekendDays = 0;
        $publicHolidaysCount = 0;
        $publicHolidays = [];
        $dayCount = 0;


        $startDateTime = new DateTime($startDateTimeParam);
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
            $usedPermitHours += 18 - (int) $startDateTime->format('H')  ;
            if ((int) $startDateTime->format('H') <= 12 || (int) $startDateTime->format('H') < 13)
                $usedPermitHours--;

        }

        if ((int) $endDateTime->format('H') < 18 && (int) $endDateTime->format('H') > 9)
        {
            $usedPermitHours += (int) $endDateTime->format('H') -9 ;
            if ((int) $endDateTime->format('H') >= 12) //Öğle arasından önce izni bitiyor ise öğle arasında olan saati çıkarıyorum.
                $usedPermitHours--;

        }

        if((int) $endDateTime->format('H') == 9) //Bittiği gün saati 9 ise o gün hiç izin kullanılmamış demektir.
        {
            $clearDayCount--;
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
                    $usedPermitHours +=((int) $itemStartDate->format('H') - 9) - 1; // Öğle arasını çıkarıyorum.
                else
                    $usedPermitHours += (int) $itemStartDate->format('H') - 9; // Öğle arasından önce ise -1 saat öğle arasını çıkarmama gerek yok.
            }
            if ((int) $itemEndDate->format('H') > 9)
            {
                //$publicHolidaysCount--;
                if ((int) $itemEndDate->format('H') >= 13)
                    $usedPermitHours +=((int) $itemEndDate->format('H') - 9) - 1; // Öğle arasını çıkarıyorum.
                else
                    $usedPermitHours += (int) $itemEndDate->format('H') - 9; // Öğle arasından önce ise -1 saat öğle arasını çıkarmama gerek yok.
            }
        }


        $clearHourCount = $usedPermitHours >= 8 ? $usedPermitHours - 8 : $usedPermitHours;
        $usedPermitHours > 8 ? $clearDayCount += floor($usedPermitHours / 8) :'';
        $usedPermitHours == 8 ? $clearDayCount++ : '';

        $data['usedDays'] = $clearDayCount;
        $data['inheritHours'] = $clearHourCount;
        $data['weekendsHolidays'] = $weekendDays + $publicHolidaysCount;

        return $data;

    }

    public static function isPermitAtPublicHoliday($permitDate)
    {
        exit;
        $holidays = PublicHolidayModel::where('start_date','<=',$permitDate)->where('end_date','>=',$permitDate)->orderBy('start_date','asc')->get();
        return $holidays;
    }



    public static function netsisRemainingPermit($employeeId="")
    {
        $employee = EmployeeModel::where(["Id"=>$employeeId,"Active"=>1])->first();
        $company = CompanyModel::find($employee->EmployeePosition->CompanyID);


        if($company->Sym=="aSAY Elektronik"){
            $companyCode = "ASAYELEK";
        }
        elseif($company->Sym=="aSAY Energy"){
            $companyCode = "ASAYENER";
        }
        elseif($company->Sym=="aSAY Comm"){
            $companyCode = "ASAYILET";
        }
        elseif($company->Sym=="aSAY VAD"){
            $companyCode = "YASAYVAD";
        }
        //izin gün sayısı
        $wsdl    = 'http://netsis.asay.corp/CrmNetsisEntegrasyonServis/Service.svc?wsdl';

        ini_set('soap.wsdl_cache_enabled', 0);
        ini_set('soap.wsdl_cache_ttl', 900);
        ini_set('default_socket_timeout', 15);

        $options = array(
            'uri'               =>'http://schemas.xmlsoap.org/wsdl/soap/',
            'style'             =>SOAP_RPC,
            'use'               =>SOAP_ENCODED,
            'soap_version'      =>SOAP_1_1,
            'cache_wsdl'        =>WSDL_CACHE_NONE,
            'connection_timeout'=>15,
            'trace'             =>true,
            'encoding'          =>'UTF-8',
            'exceptions'        =>true,
            "location" => "http://netsis.asay.corp/CrmNetsisEntegrasyonServis/Service.svc?singleWsdl",
        );

        $izin["_Isyeri"]    = $companyCode;
        $izin["_SicilNo"]   = $employee->StaffID;
        try
        {
            $soap = new \SoapClient($wsdl, $options);
            $data = $soap->PersonelIzinSorgula($izin);
        }
        catch(Exception $e)
        {
            return false;
            //die($e->getMessage());
        }
        $saat = 0;

        $PermitLeftOverHours = PermitLeftOverHoursModel::where(["EmployeeID"=>$employeeId,"active"=>1]);
        foreach ($PermitLeftOverHours as $permitLeftOverHour) {
            $saat += $permitLeftOverHour->LeftOverHour;
        }
        $gun = intval($data->PersonelIzinSorgulaResult->Aciklama);
        $kalansaat = 0;
        if($saat<>0)
        {
            if($gun<>0)
            {
                $gun = intval($data->PersonelIzinSorgulaResult->Aciklama)-1;
                $kalansaat = 8-$saat;
            }
            else
            {
                $kalansaat = -(8-$saat);
            }
        }

        return ["daysLeft"=>$gun,"hoursLeft"=>$kalansaat];
    }


    public function getPermitKindAttribute()
    {
        $permitKind = $this->hasOne(PermitKindModel::class, "id", "kind");
        return $permitKind->where("active", 1)->first()->toArray();
    }

    public function getTransferEmployeeAttribute()
    {
        $transferEmployee = $this->hasOne(EmployeeModel::class, "Id", "transfer_id");
        if($transferEmployee)
            return $transferEmployee->where("Active", 1)->first();
        else
            return null;
    }




}
