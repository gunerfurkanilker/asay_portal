<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TicketModel extends Model
{
    protected $table = "Ticket";
    CONST CREATED_AT = "CreateDate";
    CONST UPDATED_AT = "LastUpdateDate";

    public static function newTicket($req){

        $newTicket = new self();

        $employeePosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $req->Employee])->first();

        $newTicket->Flag1 = $req->Flag1;
        $newTicket->Flag2 = $req->Flag2;
        $newTicket->TicketTypeID = $req->TicketTypeID;
        $newTicket->Category = $req->Flag1;
        $newTicket->Name = $req->Name;
        $newTicket->Description = $req->Description;
        $newTicket->Creator = $req->Employee;
        $newTicket->User = $req->Employee;
        $newTicket->Status = 1; // Yeni statüsü
        $newTicket->Location = $req->Location;
        $newTicket->Project = $req->Project;
        $newTicket->ExternalTicketId = $req->ExternalTicketId;
        $newTicket->Area = $employeePosition ? $employeePosition->RegionID : null;
        $newTicket->Priority = $req->Priority;//TODO null kalacak
        $newTicket->LastAssigneeUpdate = date("Y-m-d H:i:s");

        $newTicket->save();

        return $newTicket;

    }


    public static function updateTicket($req){

        $ticket = self::find($req->TicketID);

        if (!$ticket)
            return response([
                'status' => false,
                'message' => $req->TicketID. ' id nolu Ticket bulunamadı'
            ],200);

        $ticket->Flag1 = $req->Flag1;
        $ticket->Flag2 = $req->Flag2;
        $ticket->TicketTypeID = $req->TicketTypeID;
        $ticket->Category = $req->Flag1;
        $ticket->Name = $req->Name;
        $ticket->Description = $req->Description;
        $ticket->LastUpdateBy = $req->Employee;
        $ticket->Location = $req->Location;
        $ticket->Project = $req->Project;
        $ticket->ExternalTicketId = $req->ExternalTicketId;
        $ticket->Area = $req->Area;
        $ticket->Priority = $req->Priority;
        $ticket->LastUpdateBy = $req->Employee;


        $ticket->save();

        return $ticket;

    }

    public static function updateTicketStatus($ticket,$employee,$status){

        $ticket->Status = $status->id;
        $ticket->LastStatusUpdate = date("Y-m-d H:i:s");
        $result = $ticket->save();
        $setLog = TicketLogModel::setLog($ticket->id,"ST",$employee,$status->Code);

        return $setLog && $result;


    }


}
