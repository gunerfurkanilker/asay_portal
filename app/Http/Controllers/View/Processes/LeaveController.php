<?php

namespace App\Http\Controllers\View\Processes;

use App\Http\Controllers\AsayController;
use Illuminate\Http\Request;

class LeaveController extends AsayController
{
    public function list()
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "leave_list";
        $data["Title"] = "İzin Talepleri";
        return view("processes.leave.user.lists",$data);
    }
    public function approval_list($type="all")
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "leave_approval_list";
        $data["Title"] = "Onay Bekleyen İzin Talepleri";
        $data["type"] = $type;
        return view("processes.leave.approval.lists",$data);
    }

    public function leave_add()
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "leave_list";
        $data["Title"] = "İzin Talebi";
        return view("processes.leave.user.leave_add",$data);
    }
    public function leave_view($leave_id="")
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "leave_list";
        $data["Title"] = "İzin Talebi";
        return view("processes.leave.user.leave_view",$data);
    }
    public function leave_approval_view($leave_id="")
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "leave_approval_list";
        $data["Title"] = "İzin Talebi";
        return view("processes.leave.approval.leave_view",$data);
    }
    public function leave_approval_edit($leave_id="")
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "leave_approval_list";
        $data["Title"] = "İzin Talebi";
        return view("processes.leave.approval.leave_edit",$data);
    }
    public function leave_logs($leave_id="")
    {
        $data["menu"] = "processes";
        $data["SubheaderMenu"] = "";
        $data["Title"] = "İzin Kayıt Günlüğü";
        return view("processes.leave.leave_logs",$data);
    }
}
