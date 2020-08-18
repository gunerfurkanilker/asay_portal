<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ExpenseDocumentElementModel extends Model
{
    protected $table = "ExpenseDocumentElement";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
    protected $appends = ["expense_account_name"];

    public function getExpenseAccountNameAttribute()
    {
        return ExpenseAccountCodesModel::whereRaw("CONCAT(account,'-',expense_type,'-',project,'-',project_category,'-',accounting_code)='".$this->attributes["expense_account"]."'")->first()->name;
    }

    public static function saveExpenseDocumentElement($requestArray)
    {

    }

}
