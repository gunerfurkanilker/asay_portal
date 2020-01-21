<?php

namespace App\Http\Controllers\Processes;

use App\Http\Controllers\AsayController;
use Illuminate\Http\Request;

class OvertimeController extends AsayController
{
    public function list()
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "overtime_list";
        $data["Title"] = "Fazla Mesai Talepleri";
        return view("processes.overtime.user.lists",$data);
    }
    public function approval_list($type="all")
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "overtime_approval_list";
        $data["Title"] = "Onay Bekleyen Talepler";
        $data["type"] = $type;
        return view("processes.overtime.approval.lists",$data);
    }

    public function overtime_add()
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "overtime_list";
        $data["Title"] = "Fazla Mesai Talebi";
        return view("processes.overtime.user.overtime_add",$data);
    }
    public function overtime_view($overtime_id="")
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "overtime_list";
        $data["Title"] = "Fazla Mesai Talebi";
        return view("processes.overtime.user.overtime_view",$data);
    }
    public function overtime_approval_view($overtime_id="")
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "overtime_approval_list";
        $data["Title"] = "Fazla Mesai Talebi";
        return view("processes.overtime.approval.overtime_view",$data);
    }
    public function overtime_approval_edit($overtime_id="")
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "overtime_approval_list";
        $data["Title"] = "Fazla Mesai Talebi";
        return view("processes.overtime.approval.overtime_edit",$data);
    }
    public function overtime_logs($overtime_id="")
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "";
        $data["Title"] = "Fazla Mesai Kayıt Günlüğü";
        return view("processes.overtime.overtime_logs",$data);
    }
}
