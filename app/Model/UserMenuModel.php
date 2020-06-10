<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserMenuModel extends Model {

    protected $table = "user_menu";
    public $timestamps = false;
    public $appends = [];


    public static function UserMenus($UserGroup)
    {
        foreach ($UserGroup as $key=>$item) {
            $userGroupIds[] = $key;
        }
        $TemplatesQ = UserGroupHasTemplateModel::whereIn("group_id",$userGroupIds);
        $Menus      = [];
        if($TemplatesQ->count()>0) {
            $Templates = $TemplatesQ->get();
            $UserTemplate = [];
            foreach ($Templates as $template) {
                $UserTemplate[] = $template->template_id;
            }
            $UserMenusQ = UserTemplateHasMenuModel::leftjoin('user_menu','user_menu.id', '=', 'user_template_has_menu.menu_id')
            ->whereIn("user_template_has_menu.template_id" , $UserTemplate)->orderBy("user_menu.Order")->groupBy("user_template_has_menu.menu_id");
            if ($UserMenusQ->count() > 0) {
                $userMenus = $UserMenusQ->get();
                foreach ($userMenus as $item) {
                    $Menu = UserMenuModel::select("id","ObjectTypeId", "Parent", "Name","Alias")->where(["id" => $item->menu_id, "Active" => 1])->first();
                    $NMenu = [
                        "id"=>$Menu->id,
                        "title"=>$Menu->Name,
                        "href"=>$Menu->Alias,
                        "icon"=>$Menu->Parent===0 ? "pe-7s-check" : "",
                        "object_type_id"=>$Menu->ObjectTypeId
                    ];
                    $Menus[$Menu->Parent][] = $NMenu;
                }
            }
        }
        return self::CreateMenu($Menus,0);
    }

    public static function CreateMenu($Menus=[],$key)
    {
        $x = 0 ;
        if(count($Menus)>0)
        {
            foreach ($Menus[$key] as $menu) {
                $MenuList[$x] = $menu;
                if(array_key_exists($menu["id"],$Menus))
                {
                    //unset($MenuList[$x]["href"]);
                    if($MenuList[$x]["href"]===null) unset($MenuList[$x]["href"]);
                    $MenuList[$x]["child"] = self::CreateMenu($Menus,$menu["id"]);
                }
                $x++;
            }
            return $MenuList;
        }
        else
            return [];

    }
}
