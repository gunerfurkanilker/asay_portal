<?php

namespace App\Http\Resources;

use App\Model\PerformanceModel;
use App\Model\PerformanceWeightModel;
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
                          'FirstName'=> ($this->getEmployee) ? $this->getEmployee->FirstName." ".$this->getEmployee->LastName : null,
                          'Manager'=> ($this->manager) ? $this->manager->FirstName.' '.$this->manager->LastName : null,
                          'Department'=>$this->getDepartment->Sym,
                          'Title' => $this->getTitle->Sym,
                          'Region' => $this ->getRegion->Name,
                          'City' => $this->getCity->Sym,
                          'UnitSupervisor' => ($this->supervisor) ? $this->supervisor->FirstName." ".$this->supervisor->LastName : null,
                          'EvaluationPeriod'=>($this->getEmployee && PerformanceModel::where('EmployeeID',$this->getEmployee->Id)->first()) ? PerformanceModel::where('EmployeeID',$this->getEmployee->Id)->first()->EvaluationPeriod : null,
//                           'Status'=>($this->getEmployee && $this->getEmployee->getEmployeePerformance) ? $this->status[$this->getEmployee->getEmployeePerformance->StatusID]:'',
                          'Status'=>PerformanceWeightModel::where('EmployeeID',$this->EmployeeID)->get()->count()>0 ? 'Değerlendirildi' : 'Değerlendirme Bekliyor'
                          //  'Status' => ($this->getEmployee && $this->getEmployee->getStatusPerformance) ? $this->getEmployee->getStatusPerformance->Status:null,


        ] ;
    }
}

