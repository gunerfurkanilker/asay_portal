<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ExpenseAccountCodesModel extends Model {

    protected $table = "ExpenseAccountCodes";
    public $timestamps = false;
    public $appends = [
        "code"
    ];

    public function getCodeAttribute()
    {
        return $this->attributes["account"]."-".
            $this->attributes["expense_type"]."-".
            $this->attributes["project"]."-".
            $this->attributes["project_category"]."-".
            $this->attributes["accounting_code"];
    }


}
