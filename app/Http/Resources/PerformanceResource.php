<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Model\EmployeePositionModel;
class PerformanceResource extends JsonResource
{
    private $status = array(
        1 => 'Değerlendirme Bekliyor',
        2 => 'Değerlendirildi'
    );
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //'CityId'=> $this->getEmployee->getCity->city_name,
        //'LastName'=> $userEmployee->getEmployee(['Active='>5])->LastName,
        return [
            'id'=>$this->EmployeeID,
            'FirstName'=> ($this->getEmployee) ? $this->getEmployee->FirstName.' '.$this->getEmployee->LastName : null,
            'Manager'=> ($this->Manager) ? $this->Manager->FirstName.' '.$this->Manager->LastName : null,
            'Department'=> $this->Department ? $this->Department->Sym : '',
            'Title' => $this->Title ? $this->Title->Sym : '',
            'Region' => $this->Region ? $this->Region->Sym : '',
            'City' => $this->City ? $this->City->Sym : '',
            'UnitSupervisor' => ($this->UnitSupervisor) ? $this->UnitSupervisor->FirstName." ".$this->UnitSupervisor->LastName: null,
            'EvaluationPeriod'=>($this->getEmployee && $this->getEmployee->getEmployeePerformance) ? $this->getEmployee->getEmployeePerformance->EvaluationPeriod:null,
            'Status'=>($this->getEmployee && $this->getEmployee->getEmployeePerformance) ? $this->status[$this->getEmployee->getEmployeePerformance->StatusID]:null,
            //  'Status' => ($this->getEmployee && $this->getEmployee->getStatusPerformance) ? $this->getEmployee->getStatusPerformance->Status:null,


        ] ;
    }
}

