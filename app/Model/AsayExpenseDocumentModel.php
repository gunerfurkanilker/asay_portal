<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AsayExpenseDocumentModel extends Model
{
    protected $table = "asay_expense_document";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
}
