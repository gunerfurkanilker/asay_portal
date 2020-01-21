<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AsayExpenseLogModel extends Model
{
    protected $table = "asay_expense_log";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
}
