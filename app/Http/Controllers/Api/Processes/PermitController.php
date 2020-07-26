<?php


namespace App\Http\Controllers\Api\Processes;


use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeModel;
use App\Model\PermitKindModel;
use App\Model\PermitLeftOverHoursModel;
use App\Model\PermitModel;
use App\Model\PublicHolidayModel;
use App\Model\UserModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DateTime;
use Exception;
use SoapClient;

class PermitController extends ApiController
{
    public function getPermits(Request $request)
    {
        $employee = EmployeeModel::find( UserModel::find($request->userId)->EmployeeID );
        return response([
            'status' => true,
            'message' => "İşlem Başarılı",
            'data' => PermitModel::where('EmployeeID',$employee->Id)->get()
        ], 200);
    }

    public function permitTypes(Request $request)
    {
        return response([
            'status' => true,
            'message' => "İşlem Başarılı",
            'data' => PermitKindModel::getPermitKinds()
        ], 200);
    }

    public function savePermit(Request $request)
    {

        /*$datetime1 = new DateTime($request->endDate);
        $datetime2 = new DateTime($request->startDate);
        $interval = $datetime2->diff($datetime1);
        $elapsed = $interval->format('%y years %m months %a days %h hours %i minutes %s seconds');
        $remainingDays = PermitModel::getRemainingDaysYearlyPermit($request);

        $permitStartDate = new DateTime($request->endDate);
        $permitEndDate = new DateTime($request->startDate);
        $interval = $permitEndDate->diff($permitStartDate);

        $requestedPermitDays =(int) $interval->format('%a');
        $requestedPermitHours =(int) $interval->format('%h');

        if ($requestedPermitDays > $remainingDays['daysLeft'])
        {
            return response([
                'status' => false,
                'message' => "Yıllık izin hakkınızdan fazla bir izin talep ettiniz.\n Kullandığınız izin miktarı : "
                    .$remainingDays['daysUsed'].' gün, '.$remainingDays['hoursUsed'].'saat.'.'\n Kalan İzin Miktarı : ' .$remainingDays['daysLeft'].' gün, '.$remainingDays['hoursLeft'].' saat.',
            ], 200);
        }
        else if ($requestedPermitHours > $remainingDays['hoursLeft'])
            return response([
                'status' => false,
                'message' => "Yıllık izin hakkınızdan fazla bir izin talep ettiniz.\n Kullandığınız izin miktarı : "
                    .$remainingDays['daysUsed'].' gün, '.$remainingDays['hoursUsed'].'saat.'.'\n Kalan İzin Miktarı : ' .$remainingDays['daysLeft'].' gün, '.$remainingDays['hoursLeft'].' saat.',
            ], 200);*/

        $status = PermitModel::createPermit($request);
        if ($status)
            return response([
                'status' => true,
                'message' => "Kayıt Başarılı",
            ], 200);
        else
            return response([
                'status' => false,
                'message' => "Kayıt Başarısız",
            ], 200);

    }

    public function permitSendNetsis(Request $request)
    {
        $permit = PermitModel::find($request->permitId);
        $permitCounts = PermitModel::calculateTotalDayHourCount($permit->start_date,$permit->end_date);

        $izin["_Isyeri"] = $request->isYeri; // İş Yeri ne olacak ?
        $izin["_SicilNo"] =  EmployeeModel::where('Id',$permit->EmployeeID)->first()->StaffID;
        $izin["_BasTarih"] = new Carbon($permit->start_date);
        $izin["_BitTarih"] = new Carbon($permit->start_date);
        $izin["_IsGunuSayisi"] = $permitCounts['usedDays'];
        $izin["_HaftaSonuTatilSayisi"] = $permitCounts['weekendsHolidays'];
        $izin["_IzinTuru"] = "I";
        $izin["_NedenKodu"] = "";

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
        try
        {
            $soap = new SoapClient($wsdl, $options);
            $data = $soap->PersonelIzinGirisi($izin);
            return response([
                'status' => true,
                'message' => "Kayıt Başarılı",
                'resultMessage' => $data->PersonelIzinGirisiResult->Sonuc."-".$data->PersonelIzinGirisiResult->Aciklama
            ], 200);
        }
        catch(Exception $e)
        {
            return response([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }

    }



}
