<?php

namespace App\Http\Controllers\Ik;

use App\Http\Controllers\AsayController;
use App\Model\Ik\Employee\City;
use App\Model\Ik\Employee\Country;
use App\Model\Ik\Employee\EducationLevel;
use App\Model\Ik\Employee\EducationStatus;
use App\Model\Ik\Employee\Gender;
use App\Model\Ik\Employee\Nationality;
use Illuminate\Http\Request;

class DefaultController extends AsayController
{
    public function index(Request $request)
    {
        $data["menu"] = "home";
        return view("ik.main",$data);
    }

    public function employee_list()
    {
        $data["menu"] = "employee_list";
        return view("ik.employee.employee_list",$data);
    }
    public function calendar()
    {
        $data["menu"] = "calendar";
        return view("ik.calendar",$data);
    }
    public function settings()
    {
        $data["menu"] = "settings";
        return view("ik.settings",$data);
    }
    public function processes()
    {
        $data["menu"] = "processes";
        return view("processes.processes",$data);
    }
    public function projects()
    {
        $data["menu"] = "projects";
        return view("ik.projects",$data);
    }
    public function structure()
    {
        $data["menu"] = "structure";
        return view("ik.structure",$data);
    }
    public function employee_add()
    {
        $data["menu"] = "employee_list";
        return view("ik.employee.employee_add",$data);
    }
    public function employee_edit($id)
    {
        $data["menu"] = "employee_list";
        return view("ik.employee.employee_edit",$data);
    }
    public function employee_edit_career($id)
    {
        $data["menu"] = "employee_list";
        return view("ik.employee.employee_edit_career",$data);
    }
    public function employee_edit_personal_info($id)
    {
        $data["menu"] = "employee_list";
        return view("ik.employee.employee_edit_personal_info",$data);
    }
    public function employee_edit_more_info($id)
    {
        $data["menu"] = "employee_list";
        return view("ik.employee.employee_edit_more_info",$data);
    }
    public function employee_edit_trainings($id)
    {
        $data["menu"] = "employee_list";
        return view("ik.employee.employee_edit_trainings",$data);
    }
    public function employee_edit_assets($id)
    {
        $data["menu"] = "employee_list";
        return view("ik.employee.employee_edit_assets",$data);
    }
    public function employee_edit_user_groups($id)
    {
        $data["menu"] = "employee_list";
        return view("ik.employee.employee_edit_user_groups",$data);
    }

}
