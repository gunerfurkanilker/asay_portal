<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TicketModel extends Model
{
    protected $table = "Ticket";
    CONST CREATED_AT = "CreateDate";
    CONST UPDATED_AT = "LastUpdateDate";
    protected $appends = [
        "AreaValue",
        "StatusValue",
        "CarPlateValue"
    ];

    public static function newTicket($req){

        $newTicket = new self();

        $employeePosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $req->Employee])->first();

        $propertyValues = $req->PropertyValues;

        $newTicket->Flag1 = $req->Flag1;// MOBİL 1 SABİT 0 GELECEK
        $newTicket->Flag2 = $req->Flag2;// İŞ EMRİ ZORUNLULUK KOLONU (Araç var zorunlu, araç yok zorunlu değil)
        $newTicket->TicketTypeID = $req->TicketTypeID;
        $newTicket->Category = $req->Category;
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

        if ($propertyValues)
        {
            foreach ($propertyValues as $key => $propertyValue)
            {
                TicketPropertyValuesModel::setPropertyValue($newTicket->id,$key,$propertyValue);
            }
        }


        return $newTicket;

    }


    public static function updateTicket($req){

        $ticket = self::find($req->TicketID);

        if (!$ticket)
            return response([
                'status' => false,
                'message' => $req->TicketID. ' id nolu Ticket bulunamadı'
            ],200);

        $ticket->Flag1 = $req->Flag1;// MOBİL 1 SABİT 0 GELECEK
        $ticket->Flag2 = $req->Flag2;// İŞ EMRİ ZORUNLULUK KOLONU (Araç var zorunlu, araç yok zorunlu değil)
        $ticket->TicketTypeID = $req->TicketTypeID;
        $ticket->Category = $req->Category;
        $ticket->Name = $req->Name;
        $ticket->Description = $req->Description;
        $ticket->Location = $req->Location;
        $ticket->Project = $req->Project;
        $ticket->ExternalTicketId = $req->ExternalTicketId;
        $ticket->LastAssigneeUpdate = date("Y-m-d H:i:s");

        $ticket->save();

        return $ticket;

    }

    public static function updateTicketStatus($ticket,$employee,$status,$logText = ""){

        $ticket->Status = $status->id;
        $ticket->LastStatusUpdate = date("Y-m-d H:i:s");
        $result = $ticket->save();
        $setLog = TicketLogModel::setLog($ticket->id,"ST",$employee,$status->Code,$logText);

        return $setLog && $result;


    }

    public function getAreaValueAttribute(){

        $areaValue = $this->hasOne(RegionModel::class,"id","Area");
        if ($areaValue)
        {
            $areaValue = $areaValue->where(['Active' => 1])->first();
            return $areaValue ? $areaValue->Name : 'Bölge aktif değil';
        }
        else
            return "Bölge tanımı bulunamadı";
    }

    public function getStatusValueAttribute(){

        $statusValue = $this->hasOne(StatusModel::class,"id","Status");
        if ($statusValue)
        {
            $statusValue = $statusValue->where(['Active' => 1])->first();
            return $statusValue ? $statusValue->Name : 'Statü tanımlı değil';
        }
        else
            return 'Statü tanımlı değil';
    }

    public function getCarPlateValueAttribute(){

        $ticketProperty = $this->hasOne(TicketPropertyValuesModel::class,"TicketID","id");
        if ($ticketProperty)
        {
            $ticketProperty = $ticketProperty->where(['Active' => 1, 'PropertyCode' => 'CarPlate'])->first();
            return $ticketProperty ? $ticketProperty->PropertyValue : '';
        }
        else
            return '';
    }




}
