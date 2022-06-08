<?php

namespace App\Http\Controllers\Api\Processes;

use App\Http\Controllers\Api\ApiController;
use App\Model\DismissalModel;
use App\Model\DismissalSocialModel;
use App\Model\DismissialResultModel;
use App\Model\EmployeeModel;
use App\Model\PerformanceWeightModel;
use Illuminate\Http\Request;

class DismissalController extends ApiController
{
    public function dismissalReason(Request $request){

        $allReason = DismissalModel::All();
        return response([
            'status' => true,
            'data' => $allReason
        ],200);

    }
    public function socialDismissalReason(Request $request){

        $socialReasons = DismissalSocialModel::All();
        return response([
            'status' => true,
            'data' => $socialReasons
        ],200);
    }
    public function saveDismissal(Request $request){
       $dismissial = new DismissialResultModel();

        $dismissial->EmployeeID = $request->EmployeeID?? '';
        $dismissial->ExitDate = $request->ExitDate ?? '';
        $dismissial->ReasonID = $request -> ReasonID ?? '';
        $dismissial->SocialReasonID = $request -> SocialReasonID ?? '';
        $dismissial->Description = $request->Description ?? '';
        $dismissial->FileID = $request->FileID ?? '';
        $dismissial->save();
        $emp = $request->EmployeeID;
        $empP = EmployeeModel::where(["Id"=>$emp])->first();
        $empP->Active =0;
        $empResult = $empP->save();

        return response()->json([
            'success' => true,
            'message' => 'Çalışan işten çıkartıldı.'
        ]);

    }
}
