<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TicketPropertyValuesModel extends Model
{
    protected $table = "TicketPropertyValues";
    CONST CREATED_AT = "CreateDate";
    CONST UPDATED_AT = "LastUpdateDate";
    protected $guarded = [];
    //

    public static function setPropertyValue($ticketID,$propertyCode,$propertyValue,$propertyType="Text"){

        $FlowPropertValues = self::firstOrNew(["TicketID" => $ticketID,"PropertyCode" => $propertyCode,"PropertyType" => $propertyType]);
        $FlowPropertValues->PropertyValue = $propertyValue;

        if($FlowPropertValues->save())
            return true;
        else
            return false;

    }
}
