<?php


namespace App\Http\Controllers\Api\Processes;


use App\Http\Controllers\Api\ApiController;
use App\Model\PermitKindModel;
use App\Model\PermitLeftOverHoursModel;
use App\Model\PermitModel;
use App\Model\PublicHolidayModel;
use Illuminate\Http\Request;
use DateTime;

class PermitController extends ApiController
{
    public function createPermit(Request $request)
    {

        $status = PermitModel::createPermit($request->all());
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

        $datetime1 = new DateTime($request->endDate);
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
            ], 200);




        return response([
            'status' => true,
            'message' => "İşlem Başarılı",
            'data' => $remainingDays,
            'elapsed' => $elapsed,
            'requestedPermitDays' => $requestedPermitDays,
            'requestedPermitHours' => $requestedPermitHours
        ], 200);







    }



}
