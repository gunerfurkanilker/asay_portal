<?php

namespace App\Model;

use App\Library\Asay;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
      'Organization',
      'Region',
      'Office',
      'WorkingField',
      'UnitSupervisor',
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
        $data['Managers']           = DB::table("Employee")->where(['Active' => 1])->get();
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
                'Share'                 => $request->Share ? 1:0,
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
            $loggedUser = DB::table("Employee")->find($request->Employee);
            $employee = DB::table("Employee")->find($position->EmployeeID);
            LogsModel::setLog($request->Employee,$position->Id,15,36,"","",$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışana pozisyon bilgisi ekledi","","","","","");

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
        $position->Share                = $request->Share ? 1:0;

        $loggedUser = DB::table("Employee")->find($request->Employee);
        $employee = DB::table("Employee")->find($position->EmployeeID);
        $dirtyFields = $position->getDirty();
        foreach ($dirtyFields as $field => $newdata) {
            $olddata = $position->getOriginal($field);
            if ($olddata != $newdata) {
                LogsModel::setLog($request->Employee,$employee->Id,15,37,$olddata,$newdata,$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışanın pozisyon bilgisini düzenledi","","","",$field,"");
            }
        }

        if ($request->ActualPosition)
        {



            $actualPosition = self::checkActualPositionExists($request->EmployeeID);

            if ($actualPosition && $actualPosition->Id != $position->Id)
            {
                $actualPosition->Active = 1 ;
                $actualPosition->save();
            }

            $position->Active = 2;
           // Asay::sendMail($ITSpecialistEmployee->JobEmail,$ikEmployee->JobEmail,"Active Directory Kullanıcısı Oluşturma İsteği","Sayın " .$ITSpecialistEmployee->UsageName . ' ' . $ITSpecialistEmployee->LastName. ' ' . $employee->JobEmail . ' adında bir mail adresi oluşturmanız talep edilmektedir. Bu kullanıcıyı farklı bir mail adresi ile oluşturmanız durumunda lütfen bu maile dönüş yapınız.' );


        }

        if ($position->save())
        {
            return true;
        }

        else
            return false;

    }

    public static function deleteJobPosition($id)
    {
        $position = EmployeePositionModel::find($id);

            $position->Active = 0;
            $position->save();
            return true;

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
        if ($this->attributes['ManagerID'])
        {
            $manager = DB::table("Employee")->where(['Id' => $this->attributes['ManagerID']])->first();
            return $manager;
        }
        else
            return null;
    }

    public function getOrganizationAttribute(){
        $manager = $this->hasOne(OrganizationModel::class,'id','OrganizationID');
        return $manager->first();
    }
    public function getRegionAttribute(){
        $office = $this->hasOne(RegionModel::class,'id','RegionID');
        if ($office)
        {
            return $office->where("Active",1)->first();
        }
        else
            return null;
    }
    public function getOfficeAttribute(){
        $office = $this->hasOne(OfficeModel::class,'id','OfficeID');
        if ($office)
        {
            return $office->where("Active",1)->first();
        }
        else
            return null;
    }
    public function getWorkingFieldAttribute(){
        $workingField = $this->hasOne(WorkingFieldModel::class,'id','WorkingFieldID');
        if ($workingField)
        {
            return $workingField->where("Active",1)->first();
        }
        else
            return null;
    }

    public function supervisor()
    {
       return $this->hasOne(EmployeeModel::class,'Id','UnitSupervisorID');
//         return $this->belongsTo(EmployeeModel::class,'Id','UnitSupervisorID');
    }

    public function employee()
    {
        return $this->hasOne(EmployeeModel::class,'Id','EmployeeID');
    }

    public function getUnitSupervisorAttribute(){
        if ($this->attributes['UnitSupervisorID'])
        {
            $unitSupervisor = DB::table("Employee")->where(['Id' => $this->attributes['UnitSupervisorID']])->first();
//             $unitSupervisor = EmployeeModel::where(['Id' => $this->attributes['UnitSupervisorID']])->first();
            return $unitSupervisor;
        }
        else
            return null;
    }



        public function getEmployee()
        {
            return $this->hasOne('App\Model\EmployeeModel','Id','EmployeeID');
        }





        public function getTitle()
        {
            return $this->hasOne('App\Model\TitleModel','Id','TitleID');
        }
        public function getDepartment()
        {
            return $this->hasOne('App\Model\DepartmentModel','Id','DepartmentID');
        }

        public function getRegion()
        {
            return $this->hasOne('App\Model\RegionModel','Id','RegionID');
        }
        public function getCity()
        {
            return $this->hasOne('App\Model\CityModel','Id','CityID');
        }

        public function getUnitSupervisor(){
            return $this->belongsTo('App\Model\EmployeeModel','Id','UnitSupervisorID');
        }



 }
