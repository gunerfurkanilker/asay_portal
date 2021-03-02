<?php

namespace App\Model;

use App\Library\Asay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ExpenseModel extends Model
{
    protected $table = "Expense";
    protected $primaryKey = "id";
    protected $appends = [
        'Project',
        'Category',
        'EmployeeManager',
        'Employee',
        'Document'
    ];

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];

    public static function sendMailToManager($request)
    {

        $expenseDocuments = ExpenseDocumentModel::where(["expense_id"=>$request->expense_id,"active"=>1])->get();
        $documentIds = [];
        foreach ($expenseDocuments as $expenseDocument)
        {
            array_push($documentIds,$expenseDocument->id);
        }

        $expenseDocumentElements    = ExpenseDocumentElementModel::where(["active"=>1])->where(function ($query) use ($documentIds){
            $query->whereIn('document_id',$documentIds);
        })->get();
        $expense                    = ExpenseModel::find($request->expense_id);

        $expenseType                = $expense->expense_type == 1 ? 'İş Avansı' : 'Seyahat Avansı' ;
        $project                    = ProjectsModel::find($expense->project_id);
        $expenseKind                = "";
        if ($project->id == 3)
        {
            $projectName = 'İşletme Gideri';
            $expenseKind = 'İşletme Gideri';
        }

        else if ($project->id == 4)
        {
            $projectName = 'Satış ve Pazarlama';
            $expenseKind = 'Satış ve Pazarlama';
        }
        else
        {
            $expenseKind = 'Proje';
        }

        $projectName        = $project->name;

        $projectCategory        = ProjectCategoriesModel::find($expense->category_id);
        $projectCategoryName    = $projectCategory->name;

        $documentElementString = "";
        foreach ($expenseDocumentElements as $expenseDocumentElement)
        {

            $documentElementString .= '<tr><td> Gider Hesabı : ' . $expenseDocumentElement->expense_account_name . '</td><td>Tutar : ' . $expenseDocumentElement->price . '</td></tr>';


        }

        $employee = EmployeeModel::find($expense->EmployeeID);
        $employeePosition = EmployeePositionModel::where(['Active' => 2,'EmployeeID' => $expense->EmployeeID])->first();
        $managerUser = EmployeeModel::where(['Id' => $employeePosition->ManagerID])->first();

        $mailTable = $expense->name . ' adlı harcama için onayınız bekleniyor.' . '
<html lang="en">
<head>
<title>Harcama Mail</title>
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  padding: 5px;
  text-align: center;
  
}
</style>
</head>
<body>
<br><br>
<table width="800">
  <tr style="background-color: rgb(0,31,91);color:white" >
    <th>Harcama Başlık</th>
    <th>Harcamayı Oluşturan</th>
  </tr>
  <tr >
    <td>
           ' . $expense->name . '
    </td>
    <td>
           ' . $employee->UsageName . ' ' . $employee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td>
        <b>Harcama Tipi</b>
    </td>
    <td>
        <b>Harcama Türü</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $expenseType . '
    </td>
    <td>
        ' . $expenseKind . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td>
        <b>Proje</b>
    </td>
    <td>
        <b>Harcama Kategorisi</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $projectName . '
    </td>
    <td>
        ' . $projectCategoryName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td colspan="2" >
       <b>İş Emri Kodu</b>
    </td>
  </tr>
  <tr >
    <td  colspan="2" >
        ' . $expense->code . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td colspan="2" >
       <b>Açıklama</b>
    </td>
  </tr>
  <tr >
    <td  colspan="2" >
        ' . $expense->description . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
  	<td  colspan="2"  >
        <b>Harcama Kalemleri</b>
    </td>
  </tr>' . $documentElementString . '
  </table>
</body>
</html>
  ';

        Asay::sendMail($managerUser->JobEmail, "", $expense->name . " adlı harcama onayınızı bekliyor.", $mailTable
            , "Harcama İçin Onayınız Bekleniyor.");


    }

    public static function sendMailToProjectManager($request){
        $expenseDocuments = ExpenseDocumentModel::where(["expense_id"=>$request->expenseId,"active"=>1])->get();
        $documentIds = [];
        foreach ($expenseDocuments as $expenseDocument)
        {
            array_push($documentIds,$expenseDocument->id);
        }

        $expenseDocumentElements    = ExpenseDocumentElementModel::where(["active"=>1])->where(function ($query) use ($documentIds){
            $query->whereIn('document_id',$documentIds);
        })->get();
        $expense                    = ExpenseModel::find($request->expenseId);

        $expenseType                = $expense->expense_type == 1 ? 'İş Avansı' : 'Seyahat Avansı' ;
        $project                    = ProjectsModel::find($expense->project_id);
        $expenseKind                = "";
        if ($project->id == 3)
        {
            $projectName = 'İşletme Gideri';
            $expenseKind = 'İşletme Gideri';
        }

        else if ($project->id == 4)
        {
            $projectName = 'Satış ve Pazarlama';
            $expenseKind = 'Satış ve Pazarlama';
        }
        else
        {
            $expenseKind = 'Proje';
        }

        $projectName        = $project->name;

        $projectCategory        = ProjectCategoriesModel::find($expense->category_id);
        $projectCategoryName    = $projectCategory->name;

        $documentElementString = "";
        foreach ($expenseDocumentElements as $expenseDocumentElement)
        {

            $documentElementString .= '<tr><td> Gider Hesabı : ' . $expenseDocumentElement->expense_account_name . '</td><td>Tutar : ' . $expenseDocumentElement->price . '</td></tr>';

        }

        //TODO Proje yöneticisni belirlemek gerek.
        $expenseCategory    = ProjectCategoriesModel::find($expense->category_id);
        $expenseProject     = ProjectsModel::find($expense->project_id);
        $projectManagerId   = $expenseCategory->manager_id != null ? $expenseCategory->manager_id : $expenseProject->manager_id;
        $projectManager     = EmployeeModel::find($projectManagerId);
        $projectManagerUser = EmployeeModel::where(['Id' => $projectManagerId])->first();

        $employeePosition = EmployeePositionModel::where(['Active' => 2,'EmployeeID' => $expense->EmployeeID])->first();
        $employee = EmployeeModel::find($expense->EmployeeID);

        $mailTable = $expense->name . ' adlı harcama için onayınız bekleniyor.' . '
<html lang="en">
<head>
<title>Harcama Mail</title>
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  padding: 5px;
  text-align: center;
  
}
</style>
</head>
<body>
<br><br>
<table width="800">
  <tr style="background-color: rgb(0,31,91);color:white" >
    <th >Harcama Başlık</th>
    <th >Harcamayı Oluşturan</th>
  </tr>
  <tr >
    <td>
           ' . $expense->name . '
    </td>
    <td>
           ' . $employee->UsageName . ' ' . $employee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td>
        <b>Harcama Tipi</b>
    </td>
    <td>
        <b>Harcama Türü</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $expenseType . '
    </td>
    <td>
        ' . $expenseKind . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td>
        <b>Proje</b>
    </td>
    <td>
        <b>Harcama Kategorisi</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $projectName . '
    </td>
    <td>
        ' . $projectCategoryName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td colspan="2" >
       <b>İş Emri Kodu</b>
    </td>
  </tr>
  <tr >
    <td  colspan="2" >
        ' . $expense->code . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td colspan="2" >
       <b>Açıklama</b>
    </td>
  </tr>
  <tr >
    <td  colspan="2" >
        ' . $expense->description . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
  	<td  colspan="2"  >
        <b>Harcama Kalemleri</b>
    </td>
  </tr>' . $documentElementString . '
  </table>
</body>
</html>
  ';

        Asay::sendMail($projectManagerUser->JobEmail, "", $expense->name . " adlı harcama onayınızı bekliyor.", $mailTable
            , "Harcama İçin Onayınız Bekleniyor.");
    }

    public static function sendMailToAccounters($request){

        $expenseDocuments = ExpenseDocumentModel::where(["expense_id"=>$request->expenseId,"active"=>1])->get();
        $documentIds = [];
        foreach ($expenseDocuments as $expenseDocument)
        {
            array_push($documentIds,$expenseDocument->id);
        }

        $expenseDocumentElements    = ExpenseDocumentElementModel::where(["active"=>1])->where(function ($query) use ($documentIds){
            $query->whereIn('document_id',$documentIds);
        })->get();
        $expense                    = ExpenseModel::find($request->expenseId);

        $expenseType                = $expense->expense_type == 1 ? 'İş Avansı' : 'Seyahat Avansı' ;
        $project                    = ProjectsModel::find($expense->project_id);
        $expenseKind                = "";
        if ($project->id == 3)
        {
            $projectName = 'İşletme Gideri';
            $expenseKind = 'İşletme Gideri';
        }

        else if ($project->id == 4)
        {
            $projectName = 'Satış ve Pazarlama';
            $expenseKind = 'Satış ve Pazarlama';
        }
        else
        {
            $expenseKind = 'Proje';
        }

        $projectName        = $project->name;

        $projectCategory        = ProjectCategoriesModel::find($expense->category_id);
        $projectCategoryName    = $projectCategory->name;

        $documentElementString = "";
        foreach ($expenseDocumentElements as $expenseDocumentElement)
        {

            $documentElementString .= '<tr><td> Gider Hesabı : ' . $expenseDocumentElement->expense_account_name . '</td><td>Tutar : ' . $expenseDocumentElement->price . '</td></tr>';

        }

        //TODO Proje yöneticisni belirlemek gerek.


        $employeePosition = EmployeePositionModel::where(['Active' => 2,'EmployeeID' => $expense->EmployeeID])->first();
        $accounterPositions = EmployeePositionModel::where(['Active' => 2,'RegionID' => $employeePosition->RegionID])->get();
        $accountersMails = [];
        foreach ($accounterPositions as $accounterPosition)
        {
            //Group ID 12 => muhasebe yöneticisi
            $tempEmployeeHasGroup = EmployeeHasGroupModel::where(['active' => 1, 'group_id' => 12,'EmployeeID' => $accounterPosition->EmployeeID])->first();
            if ($tempEmployeeHasGroup)
            {
                $tempAccounterUser = EmployeeModel::where(['Id' => $accounterPosition->EmployeeID])->first();
                array_push($accountersMails,$tempAccounterUser->JobEmail);

            }
        }


        $employee = EmployeeModel::find($expense->EmployeeID);
        $managerUser = EmployeeModel::where(['Id' => $employeePosition->ManagerID])->first();


        $mailTable = $expense->name . ' adlı harcama için onayınız bekleniyor.' . '
<html lang="en">
<head>
<title>Harcama Mail</title>
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  padding: 5px;
  text-align: center;
  
}
</style>
</head>
<body>
<br><br>
<table width="800">
  <tr style="background-color: rgb(0,31,91);color:white" >
    <th >Harcama Başlık</th>
    <th >Harcamayı Oluşturan</th>
  </tr>
  <tr >
    <td>
           ' . $expense->name . '
    </td>
    <td>
           ' . $employee->UsageName . ' ' . $employee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td>
        <b>Harcama Tipi</b>
    </td>
    <td>
        <b>Harcama Türü</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $expenseType . '
    </td>
    <td>
        ' . $expenseKind . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td>
        <b>Proje</b>
    </td>
    <td>
        <b>Harcama Kategorisi</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $projectName . '
    </td>
    <td>
        ' . $projectCategoryName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td colspan="2" >
       <b>İş Emri Kodu</b>
    </td>
  </tr>
  <tr >
    <td  colspan="2" >
        ' . $expense->code . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td colspan="2" >
       <b>Açıklama</b>
    </td>
  </tr>
  <tr >
    <td  colspan="2" >
        ' . $expense->description . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
  	<td  colspan="2"  >
        <b>Harcama Kalemleri</b>
    </td>
  </tr>' . $documentElementString . '
  </table>
</body>
</html>
  ';

        Asay::sendMail($accountersMails, "", $expense->name . " adlı harcama onayınızı bekliyor.", $mailTable
            , "Harcama İçin Onayınız Bekleniyor.");

    }

    public function getProjectAttribute()
    {

        $project = $this->hasOne(ProjectsModel::class,"id","project_id");
        if ($project)
        {
            return $project->first();
        }
        else
        {
            return null;
        }

    }

    public function getCategoryAttribute()
    {

        $category = $this->hasOne(ProjectCategoriesModel::class,"id","category_id");
        if ($category)
        {
            return $category->first();
        }
        else
        {
            return null;
        }

    }

    public function getEmployeeManagerAttribute()
    {

        $employeePosition = $this->hasOne(EmployeePositionModel::class,"EmployeeID","EmployeeID");
        if ($employeePosition)
        {
            $employeePosition = $employeePosition->where(['Active' => 2])->first();
            $employee = EmployeeModel::find($employeePosition->ManagerID);
            return $employee;
        }
        else
        {
            return null;
        }

    }

    public function getEmployeeAttribute()
    {
        if ($this->attributes['EmployeeID'])
        {
            return DB::table("Employee")->where(['Id' => $this->attributes['EmployeeID']])->first();
        }
        else
        {
            return null;
        }
    }

    public function getDocumentAttribute()
    {
        return DB::table("ExpenseDocument")->where(['Id' => $this->attributes['id']])->get();
    }

}
