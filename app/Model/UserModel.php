<?php

namespace App\Model;

use http\Env\Response;
use phpDocumentor\Reflection\Types\Object_;
use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
    protected $table = "user";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [
        'password',
    ];
    protected $casts = [];
    protected $appends = ["user_property","user_group"];

    public function getUserPropertyAttribute()
    {
        $userPropertys = $this->hasMany(UserPropertyModel::class, "user_id", "id")->get();
        $property = (object) array();
        foreach ($userPropertys as $userProperty) {
            $field = UserFieldModel::where(["field_name"=>$userProperty->field])->first();
            if($field->field_type=="string") $field_type = "value_string";
            else if($field->field_type=="integer") $field_type = "value_float";
            else if($field->field_type=="datetime") $field_type = "value_datetime";
            $property->{$userProperty->field} = $userProperty->{$field_type};
        }
        return $property;
    }

    public function getUserGroupAttribute()
    {
        $groups = [];
        $userGroups = $this->hasMany(EmployeeHasGroupModel::class, "EmployeeID", "EmployeeID")->get();
        foreach ($userGroups as $userGroup) {
            $group = UserGroupModel::find($userGroup->group_id);
            $groups[$userGroup->group_id] = $group->name;
        }
        return $groups;
    }


    public static function tokenControl($token)
    {
        global $asayData;
        $tokenSearch = UserTokensModel::where("user_token", $token)->first();
        if (!$tokenSearch) {
            return false;
        } else {

            $date1 = strtotime($tokenSearch->updated_at);
            $date2 = strtotime(date("Y-m-d H:i:s"));
            $asayData["user_id"] = $tokenSearch->user_id;
            $hours = abs($date2-$date1)/(60*60);
            if ((int)$hours > 4) {
                return false;
            }
        }

        self::updateToken($token);
        return true;
    }

    public static function updateToken($token)
    {
        $now = date("Y-m-d H:i:s");
        $tokenSearch = UserTokensModel::where("user_token", $token);
        $tokenSearch->update(["updated_at" => $now]);
    }

    public static function createToken($data)
    {
        $tokenSearch = UserTokensModel::where("user_id", $data["user_id"]);
        $User = self::find($data["user_id"]);
        $token = "";
        if($User->multi_session==1)
        {
            if($tokenSearch->count()>0)
            {
                $tokenDetail = $tokenSearch->first();
                if (self::tokenControl($tokenDetail->user_token)) {
                    $token = $tokenDetail->user_token;
                }
            }
        }
        if($token=="")
        {
            $token = md5(bin2hex(openssl_random_pseudo_bytes(16)) . $data["email"]);
        }

        if ($tokenSearch->first()) {
            $tokenSearch->update(["user_token" => $token]);
        } else {
            $userToken = new UserTokensModel();
            $userToken->user_id = $data["user_id"];
            $userToken->user_token = $token;
            $userToken->save();
        }

        return $token;
    }

    public static function LdapUserCreate($search,$username)
    {
        $userDetail = $search->in('DC=asay,DC=corp')->findBy('samaccountname', $username);
        $UserFields = UserFieldModel::whereNotNull("ldap_attributes")->where(["table"=>"user"])->get();
        $userQ = UserModel::where(["email"=>$userDetail->mail]);
        if($userQ->count()==0)
            $lUser = new UserModel();
        else
            $lUser = $userQ->first();

        foreach ($UserFields as $userField) {
            if($userField->field_type=="active")
            {
                if($userDetail->{$userField->ldap_attributes}[0]==66048 || $userDetail->{$userField->ldap_attributes}[0]==66080  || $userDetail->{$userField->ldap_attributes}[0]==512)
                    $active = 1;
                else
                    $active = 0;
                $lUser->{$userField->field_name} = $active;
            }
            elseif($userField->field_type=="string")
            {
                $lUser->{$userField->field_name} = $userDetail->{$userField->ldap_attributes}[0];
            }
            elseif($userField->field_type=="integer")
            {
                $lUser->{$userField->field_name} = $userDetail->{$userField->ldap_attributes}[0];
            }
        }
        $lUser->save();

        if($userDetail->thumbnailphoto[0]<>"")
        {
            $filename = "uploads/user/" . $lUser->id. ".jpg";
            file_put_contents($filename, $userDetail->thumbnailphoto[0]);
            $lUser->photo = $filename;
            $lUser->save();
        }

        $userPropertyFields = UserFieldModel::whereNotNull("ldap_attributes")->where(["table"=>"user_property"])->get();
        foreach ($userPropertyFields as $userPropertyField) {
            $userProperty = UserPropertyModel::firstOrNew(["user_id"=>$lUser->id,"field"=>$userPropertyField->field_name]);
            if($userPropertyField->field_type=="string")
                $userProperty->value_string = $userDetail->{$userPropertyField->ldap_attributes}[0];
            elseif($userPropertyField->field_type=="integer")
                $userProperty->value_float = $userDetail->{$userPropertyField->ldap_attributes}[0];
            elseif($userPropertyField->field_type=="datetime")
                $userProperty->value_datetime = $userDetail->{$userPropertyField->ldap_attributes}[0];
            $userProperty->save();
        }


        //User Group
        /*
        foreach ($userDetail->memberof as $item) {
            $groupQ = UserGroupModel::where(["ldap_code"=>$item]);
            if($groupQ->count()>0)
            {
                $group = $groupQ->first();
            }
            else
            {
                preg_match('@^(?:CN=)?([^,]+)@i', $item,$groupName);
                $group = new UserGroupModel();
                $group->name = $groupName[1];
                $group->ldap_code = $item;
                $group->save();
            }
            $userGroup = UserHasGroupModel::firstOrNew(["user_id"=>$lUser->id,"group_id"=>$group->id]);
            $userGroup->save();
        }
        */
        return $lUser;
    }
}
