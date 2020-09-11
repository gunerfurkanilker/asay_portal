<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EmployeePositionModel extends Model
{
    protected $primaryKey = 'Id';
    protected $table = 'EmployeePosition';
    public $timestamps = false;
    protected $guarded = [];
    protected $appends = [
      'Title',
      'Company',
      'WorkingType',
      'Department',
      'City',
      'District',
      'Manager',
      'Organization'
    ];

    public static function getPositionFields()
    {
        $data = [];
        $data['WorkingFields']      = WorkingFieldModel::all();
        $data['Offices']            = OfficeModel::all();
        $data['Organizations']      = OrganizationModel::all();
        $data['Regions']            = RegionModel::all();
        $data['Companies']          = CompanyModel::all();
        $data['Cities']             = CityModel::all();
        $data['Districts']          = DistrictModel::where('CityId',35)->get();
        $data['Departments']        = DepartmentModel::all();
        $data['Titles']             = TitleModel::all();
        $data['Managers']           = EmployeeModel::where(['Active' => 1])->get();
        $data['WorkingTypes']       = WorkingTypeModel::where('Active',1)->get();

        return $data;

    }

    public static function addJobPosition($request)
    {
        try{
            $position = self::create([
                'OrganizationID'        => $request->OrganizationID,
                'SubDepartment'         => $request->SubDepartment,
                'Unit'                  => $request->Unit,
                'ServiceCode'           => $request->ServiceCode,
                'RegionID'              => $request->RegionID,
                'OfficeID'              => $request->OfficeID,
                'WorkingFieldID'        => $request->WorkingFieldID,
                'UnitSupervisorID'      => $request->UnitSupervisorID,
                'EmployeeID'            => $request->EmployeeID,
                'CompanyID'             => $request->CompanyID,
                'CityID'                => $request->CityID,
                'DistrictID'            => $request->DistrictID,
                'DepartmentID'          => $request->DepartmentID,
                'TitleID'               => $request->TitleID,
                'ManagerID'             => isset($request->ManagerID) ? $request->ManagerID : null,
                'WorkingTypeID'         => $request->WorkingTypeID,
                'StartDate'             => $request->StartDate,
                'EndDate'               => isset($request->EndDate) ? $request->EndDate : null,
                'Active'                => $request->ActivePosition ? 1 : 0
            ]);

            if ($request->ActualPosition)
            {
                $actualPosition = self::checkActualPositionExists($request->EmployeeID);

                if ($actualPosition)
                {
                    $actualPosition->Active = 1 ;
                    $actualPosition->save();
                }

                $position->Active = 2;
            }


            $position->save();

            return $position->fresh();
        }
        catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public static function editJobPosition($position,$request)
    {
        $position->OrganizationID       = $request->OrganizationID;
        $position->SubDepartment        = $request->SubDepartment;
        $position->Unit                 = $request->Unit;
        $position->ServiceCode          = $request->ServiceCode;
        $position->RegionID             = $request->RegionID;
        $position->OfficeID             = $request->OfficeID;
        $position->WorkingFieldID       = $request->WorkingFieldID;
        $position->UnitSupervisorID     = $request->UnitSupervisorID;
        $position->EmployeeID           = $request->EmployeeID;
        $position->CompanyID            = $request->CompanyID;
        $position->CityID               = $request->CityID;
        $position->DistrictID           = $request->DistrictID;
        $position->DepartmentID         = $request->DepartmentID;
        $position->TitleID              = $request->TitleID;
        $position->ManagerID            = $request->ManagerID;
        $position->WorkingTypeID        = $request->WorkingTypeID;
        $position->StartDate            = $request->StartDate;
        $position->EndDate              = $request->EndDate;
        $position->Active               = $request->ActivePosition ? 1:0;

        if ($request->ActualPosition)
        {
            $actualPosition = self::checkActualPositionExists($request->EmployeeID);

            if ($actualPosition && $actualPosition->Id != $position->Id)
            {
                $actualPosition->Active = 1 ;
                $actualPosition->save();
            }

            $position->Active = 2;
        }

        if ($position->save())
            return $position->fresh();
        else
            return false;

    }

    public static function deleteJobPosition($id)
    {
        $position = EmployeePositionModel::find($id);
        try
        {
            $position->delete();
            return true;
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }

    }

    public static function checkActualPositionExists($employeeID)
    {
        $position = self::where("EmployeeID",$employeeID)->where("Active",2)->first();

        return $position ? $position : false;

    }


    public function getTitleAttribute(){
        $title = $this->hasOne(TitleModel::class,'Id','TitleID');
        return $title->first();
    }

    public function getCompanyAttribute(){
        $company = $this->hasOne(CompanyModel::class,'Id','CompanyID');
        return $company->first();
    }

    public function getWorkingTypeAttribute(){
        $workingType = $this->hasOne(WorkingTypeModel::class,'Id','WorkingTypeID');
        return $workingType->first();
    }

    public function getDepartmentAttribute(){
        $department = $this->hasOne(DepartmentModel::class,'Id','DepartmentID');
        return $department->first();
    }

    public function getCityAttribute(){
        $city = $this->hasOne(CityModel::class,'Id','CityID');
        return $city->first();
    }

    public function getDistrictAttribute(){
        $district = $this->hasOne(DistrictModel::class,'Id','DistrictID');
        return $district->first();
    }

    public function getManagerAttribute(){
        $manager = $this->hasOne(EmployeeModel::class,'Id','ManagerID');
        return $manager->first();
    }

    public function getOrganizationAttribute(){
        $manager = $this->hasOne(OrganizationModel::class,'id','OrganizationID');
        return $manager->first();
    }

 }
