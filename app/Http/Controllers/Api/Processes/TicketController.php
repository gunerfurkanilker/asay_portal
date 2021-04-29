<?php


namespace App\Http\Controllers\Api\Processes;


use App\Http\Controllers\Api\ApiController;
use App\Model\StatusModel;
use App\Model\TicketLogModel;
use App\Model\TicketModel;
use App\Model\TicketPropertyValuesModel;
use App\Model\TicketTypeModel;
use Illuminate\Http\Request;

class TicketController extends ApiController
{
    public function createTicket(Request $request)
    {

        $newTicket = TicketModel::newTicket($request);

        if (!$newTicket)
            return response([
                'status' => false,
                'message' => 'Kayıt sırasında hata oluştu'
            ],200);
        else{

            $setLog = TicketLogModel::setLog($newTicket->id,"ST",$request->Employee,"N");

            if (!$setLog)
                return response([
                    'status' => true,
                    'message' => 'Kayıt başarılı, fakat loglama sırasında hata oluştu'
                ],200);

            return response([
                'status' => true,
                'message' => 'Kayıt Başarılı'
            ],200);
        }


    }

    public function updateTicket(Request $request){

        $updateTicketResult = TicketModel::updateTicket($request);

        if (!$updateTicketResult)
            return response([
                'status' => false,
                'message' => 'Güncelleme sırasında hata oluştu'
            ],200);
        else
        {
            $setLog = TicketLogModel::setLog($updateTicketResult->id,$request->LogType,$request->Employee,$request->LogValue,$request->LogText);

            if (!$setLog)
                return response([
                    'status' => true,
                    'message' => 'Güncelleme başarılı, fakat loglama sırasında hata oluştu'
                ],200);

            return response([
                'status' => true,
                'message' => 'Güncelleme Başarılı'
            ],200);
        }



    }


    public function listTicket(Request $request){

        //TODO Sayfalama / Pagination yapılabilir

        $ticketTypeID = $request->TicketTypeID;
        $category = $request->Category;
        $name = $request->Name;
        $creator = $request->Creator;
        $user = $request->User;
        $area = $request->Area;

        $ticketListQ = TicketModel::where(['Active' => 1]);

        if ($ticketTypeID !== null)
            $ticketListQ->where("TicketTypeID", $ticketTypeID);
        if ($category !== null)
            $ticketListQ->where("Category", $category);
        if ($name !== null)
            $ticketListQ->where("Name", "LIKE" ,'%'.$name.'%');
        if ($creator !== null)
            $ticketListQ->where("Creator", $creator);
        if ($user !== null)
            $ticketListQ->where("User", $user);
        else
            $ticketListQ->where("User", $request->Employee);
        if ($area !== null)
            $ticketListQ->whereIn("Area", explode(",",$area));

        $ticketList = $ticketListQ->get();

        return response([
            'status' => true,
            'data' => $ticketList
        ],200);


    }

    public function setTicketProperty(Request $request){

        $ticket = TicketModel::find($request->TicketID);

        if (!$ticket)
            return response([
                'status' => false,
                'message' => 'Ticket bulunamadı'
            ],200);

        $setPropertyResult = TicketPropertyValuesModel::setPropertyValue($ticket->id,$request->PropertyCode,$request->PropertyValue);

        if (!$setPropertyResult)
            return response([
                'status' => false,
                'message' => 'Kayıt sırasında hata oluştu'
            ],200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem başarılı'
            ],200);
    }




    public function ticketTypes(){
        $ticketTypes = TicketTypeModel::where(['Active' => 1])->get();

        return response([
            'status' => true,
            'data' => $ticketTypes
        ],200);
    }

    public function updateTicketStatus(Request $request){


        if (!$request->StatusCode || $request->StatusCode == "")
            return response([
                'status' => false,
                'message' => 'StatusCode boş olamaz'
            ],200);

        $status = StatusModel::where(['Active' => 1, 'Code' => $request->StatusCode])->first();

        if (!$status)
            return response([
                'status' => false,
                'message' => 'StatusCode bulunamadı'
            ],200);


        if (!$request->TicketID || $request->TicketID == "")
            return response([
                'status' => false,
                'message' => 'TicketID boş olamaz'
            ],200);

        $ticket = TicketModel::find($request->TicketID);

        if (!$ticket)
            return response([
                'status' => false,
                'message' => 'Ticket bulunamadı'
            ],200);


        $updateStatus = TicketModel::updateTicketStatus($ticket,$request->Employee,$status);

        if (!$updateStatus)
            return response([
                'status' => false,
                'message' => 'Statü güncelleme başarısız'
            ],200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı'
            ],200);

    }

    public function ticketStatusList(){
        $list = StatusModel::where(['Active' => 1])->get();
        return response([
            'status' => true,
            'data' => $list
        ],200);

    }

}
