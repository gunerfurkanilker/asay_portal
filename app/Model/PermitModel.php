<?php

namespace App\Model;

use App\Library\Asay;
use Carbon\Carbon;
use Cassandra\Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use DateTime;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\TemplateProcessor;

class PermitModel extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'Permits';
    public $timestamps = false;

    protected $appends = [
        'PermitKind',
        'TransferEmployee',
        'Employee'
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

        $permit = $req->RequestFromHR ? PermitModel::where($req->EmployeeID)  : null;
        $employe = !is_null($req->EmployeeID) ? EmployeeModel::find($req->EmployeeID) : null;

        $newPermit->EmployeeID      = $req->RequestFromHR ? $req->EmployeeID : $EmployeeID;
        $req->RequestFromHR ? $newPermit->status = 1 : '';

        $newPermit->kind            = $req->kind;
        $newPermit->description     = $req->description;
        $newPermit->start_date      = $req->startDate;
        $newPermit->end_date        = $req->endDate;
        $newPermit->transfer_id     = $req->transfer_id;
        $newPermit->permit_address  = $req->permitAddress;
        $newPermit->used_day        = $totalPermitDayHour['UsedDay'];
        $newPermit->over_hour       = $totalPermitDayHour['OverHour'];
        $newPermit->over_minute     = $totalPermitDayHour['OverMinute'];
        $newPermit->holiday         = $totalPermitDayHour['Holidays'];
        $newPermit->weekend         = $totalPermitDayHour['Weekend'];
        $result = $newPermit->save();
        $freshPermit = $newPermit->fresh();
        if($result)
        {
            $req->RequestFromHR ? $employeePosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $req->EmployeeID])->first() : $employeePosition = null;
            $req->RequestFromHR ? NotificationsModel::saveNotification($employeePosition->ManagerID,3,$freshPermit->id,$freshPermit->PermitKind->Name,$freshPermit->PermitKind->Name." talebi, onayınızı bekliyor","permits/".$freshPermit->id) : '';
        }
        return $result ? $newPermit->fresh() : false;
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
        $minute = 0;
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

        $minute = abs(60-(abs(strtotime($endDate) - $st) / 60)) ;
        $minute != 0 ? $saat-- : '';
        if ($minute == 60)
        {
            $minute = 0;
            $saat++;
        }
        $devirSaat 		= $saat % 8;
        $aktarilacakGun = floor($saat / 8);

        $haftasonu 	= floor($hssaat/8);
        $resmigun 	= floor($rssaat/8);

        return [
            "UsedDay" => $aktarilacakGun,
            "OverHour" => $devirSaat,
            "Weekend" => $haftasonu,
            "Holidays" => $resmigun,
            'OverMinute' => $minute
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

    public static function createPermitDocumentAndSendMailToEmployee($request)
    {

        $permit = PermitModel::find($request->permitId);
        $employee = EmployeeModel::find($permit->EmployeeID);
        $hrPerson = EmployeeModel::find($request->Employee);

        $file = null;

        if ($permit->kind == 12)
            $file = Storage::disk("connect")->path('template/permit_yearly.docx');
        else if($permit->kind == 14)
            $file = Storage::disk("connect")->path('template/permit_rest.docx');
        else
            $file = Storage::disk("connect")->path('template/permit_social.docx');

        $filePath = Storage::disk("connect")->path("permitDocs"."/"."izin_formu".date("Ymd_Hms").".docx");

        $employeeFullName = $employee->UsageName . ' ' . $employee->LastName;
        $employeeDepartment = $employee->EmployeePosition->Department->Sym;
        $employeeTitle = $employee->EmployeePosition->Title->Sym;
        $employeeRegistry = $employee->StaffID;
        $permitKind = $permit->PermitKind['name'];
        $permitStartDate = date("d.m.Y H:i:s", strtotime($permit->start_date));
        $permitEndDate = date("d.m.Y H:i:s", strtotime($permit->end_date));
        $permitCount = $permit->used_day. ' gün, ' . $permit->over_hour . ' saat';
        $permitAddress = $permit->permit_address;
        $employeeManagerFullName = $employee->EmployeePosition->Manager->UsageName . ' ' . $employee->EmployeePosition->Manager->LastName;
        $employeePositionStartDate =  date("d.m.Y", strtotime($employee->EmployeePosition->StartDate));
        $hrPerson = $hrPerson->UsageName . ' ' . $hrPerson->LastName;
        $yearlyPermitEarnDate = $employee->EmployeePosition->StartDate;

        $d = date("d");
        $m = date("m");
        $y = date("Y");




        try {
            $templateProcessor = new TemplateProcessor($file);
            if($permit->kind != 12 && $permit->kind != 14)
            {
                $templateProcessor->setValue('d',$d);
                $templateProcessor->setValue('m',$m);
                $templateProcessor->setValue('y',$y);
                $templateProcessor->setValue('employeeFullName',$employeeFullName);
                $templateProcessor->setValue('employeeDepartment',$employeeDepartment);
                $templateProcessor->setValue('employeeTitle',$employeeTitle);
                $templateProcessor->setValue('employeeRegistry',$employeeRegistry);
                $templateProcessor->setValue('permitKind',$permitKind );//Social
                $templateProcessor->setValue('permitStartDate',$permitStartDate);
                $templateProcessor->setValue('permitEndDate',$permitEndDate);
                $templateProcessor->setValue('permitCount',$permitCount);
                $templateProcessor->setValue('permitAddress',$permitAddress);
                $templateProcessor->setValue('employeeManagerFullName', $employeeManagerFullName);
                $templateProcessor->setValue('employeePositionStartDate', $employeePositionStartDate);
                $templateProcessor->setValue('hrPerson', $hrPerson);
                $templateProcessor->saveAs($filePath);

                Asay::sendMail($employee->JobEmail, "", "İzin Evrak Formu","Sayın " .$employee->UsageName . ' ' . $employee->LastName. ' izin evrak formu ektedir.' ,
                    "İzin Evrak Formu",$filePath,"Izin_Evrak_Formu.docx","application/vnd.openxmlformats-officedocument.wordprocessingml.document");

            }
            else if ($permit->kind == 12)
            {
                $yearlyPermitRemainingVal = self::netsisRemainingPermit($employee->Id);
                $yearlyPermitRemaining = $yearlyPermitRemainingVal['daysLeft'].' gün, ' . $yearlyPermitRemainingVal['hoursLeft'] . ' saat';
                $yearlyPermitYear = date("Y",strtotime($permitStartDate));

                $templateProcessor->setValue('d',$d);
                $templateProcessor->setValue('m',$m);
                $templateProcessor->setValue('y',$y);
                $templateProcessor->setValue('employeeFullName',$employeeFullName);
                $templateProcessor->setValue('employeeDepartment',$employeeDepartment);
                $templateProcessor->setValue('employeeTitle',$employeeTitle);
                $templateProcessor->setValue('employeeRegistry',$employeeRegistry);
                $templateProcessor->setValue('permitStartDate',$permitStartDate);
                $templateProcessor->setValue('permitEndDate',$permitEndDate);
                $templateProcessor->setValue('permitCount',$permitCount);
                $templateProcessor->setValue('permitAddress',$permitAddress);
                $templateProcessor->setValue('employeeManagerFullName', $employeeManagerFullName);
                $templateProcessor->setValue('employeePositionStartDate', $employeePositionStartDate);
                $templateProcessor->setValue('yearlyPermitRemaining', $yearlyPermitRemaining);
                $templateProcessor->setValue('hrPerson', $hrPerson);
                $templateProcessor->setValue('yearlyPermitStartDate',$permitStartDate);
                $templateProcessor->setValue('yearlyPermitEndDate',$permitEndDate);
                $templateProcessor->setValue('yearlyPermitEarnDate',$yearlyPermitEarnDate);
                $templateProcessor->setValue('yearlyPermitYear',$yearlyPermitYear);
                $templateProcessor->setValue('hrPerson', $hrPerson);
                $templateProcessor->saveAs($filePath);

                Asay::sendMail($employee->JobEmail, "", "İzin Evrak Formu","Sayın " .$employee->UsageName . ' ' . $employee->LastName. ' izin evrak formu ektedir.' ,
                    "İzin Evrak Formu",$filePath,"Izin_Evrak_Formu.docx","application/vnd.openxmlformats-officedocument.wordprocessingml.document");

            }
            else if($permit->kind == 14)
            {

                $employeeOvertimeRestTotalHour = OvertimeRestModel::selectRaw("SUM(Hour) as total_hour,Sum(Minute) as total_minute")->where(['EmployeeID' =>$permit->EmployeeID, 'Active' => 1])
                    ->whereYear("Date", "=", date('Y'))->first();
                $totalRestPermits = PermitModel::selectRaw("SUM(used_day) as total_day,SUM(over_hour) as over_hour,SUM(over_minute) as total_minute")->where(['Active' => 1, 'EmployeeID' => $permit->EmployeeID, 'kind' => 14])
                    ->whereYear("start_date", "=", date('Y'))->first();

                $employeeOvertimeRestTotalMinute = ($employeeOvertimeRestTotalHour->total_hour * 60) + $employeeOvertimeRestTotalHour->total_minute;
                $totalRestPermitsMinute = ($totalRestPermits->total_day*8*60) + ($totalRestPermits->over_hour*60) + $totalRestPermits->total_minute;

                $remainingRestPermitTotalMinute = $employeeOvertimeRestTotalMinute - $totalRestPermitsMinute;

                $remainingRestPermitDay = (int) (((int) $remainingRestPermitTotalMinute / 60) / 8);
                $remainingRestPermitMinute = $remainingRestPermitTotalMinute % 60;
                $remainingRestPermitHour = (int) (((int) $remainingRestPermitTotalMinute / 60) % 8);


                $restPermitRemainingYear = $remainingRestPermitDay . ' gün, ' . $remainingRestPermitHour . ' saat, ' . ($remainingRestPermitMinute) . 'dakika';

                $lastEarnedPermit = OvertimeRestModel::where(['EmployeeID' => $permit->EmployeeID, 'Active' => 1])->orderBy('Date', 'desc')->first();
                $restPermitEarnDate = date('d.m.Y',strtotime($lastEarnedPermit->Date));
                $restPermitYear = date("Y",strtotime($permitStartDate));


                $templateProcessor->setValue('d',$d);
                $templateProcessor->setValue('m',$m);
                $templateProcessor->setValue('y',$y);
                $templateProcessor->setValue('employeeFullName',$employeeFullName);
                $templateProcessor->setValue('employeeDepartment',$employeeDepartment);
                $templateProcessor->setValue('employeeTitle',$employeeTitle);
                $templateProcessor->setValue('employeeRegistry',$employeeRegistry);
                $templateProcessor->setValue('permitStartDate',$permitStartDate);
                $templateProcessor->setValue('permitEndDate',$permitEndDate);
                $templateProcessor->setValue('permitCount',$permitCount);
                $templateProcessor->setValue('permitAddress',$permitAddress);
                $templateProcessor->setValue('employeeManagerFullName', $employeeManagerFullName);
                $templateProcessor->setValue('employeePositionStartDate', $employeePositionStartDate);
                $templateProcessor->setValue('restPermitEarnDate', $restPermitEarnDate);
                $templateProcessor->setValue('restPermitYear', $restPermitYear);
                $templateProcessor->setValue('restPermitRemainingYear', $restPermitRemainingYear);
                $templateProcessor->setValue('hrPerson', $hrPerson);
                $templateProcessor->setValue('restPermitStartDate', $permitStartDate);
                $templateProcessor->setValue('restPermitEndDate', $permitEndDate);
                $templateProcessor->saveAs($filePath);

                Asay::sendMail($employee->JobEmail, "", "İzin Evrak Formu","Sayın " .$employee->UsageName . ' ' . $employee->LastName. ' izin evrak formu ektedir.' ,
                    "İzin Evrak Formu",$filePath,"Izin_Evrak_Formu.docx","application/vnd.openxmlformats-officedocument.wordprocessingml.document");

            }


        } catch (CopyFileException $e) {
            return $e->getMessage();
        } catch (CreateTemporaryFileException $e) {
            return $e->getMessage();
        } catch (Exception $e) {
        }


    }

    public function getPermitKindAttribute()
    {
        $permitKind = $this->hasOne(PermitKindModel::class, "id", "kind");
        return $permitKind->where("active", 1)->first()->toArray();
    }

    public function getTransferEmployeeAttribute()
    {
        if($this->attributes['transfer_id'])
            return DB::table("Employee")->where(['Id' => $this->attributes['transfer_id']])->first();
        else
            return null;
    }

    public function getEmployeeAttribute()
    {
        if($this->attributes['EmployeeID'])
            return DB::table("Employee")->where(['Id' => $this->attributes['EmployeeID']])->first();
        else
            return null;
    }

}
