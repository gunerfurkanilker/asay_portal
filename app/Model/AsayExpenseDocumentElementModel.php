<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AsayExpenseDocumentElementModel extends Model
{
    protected $table = "asay_expense_document_element";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
}
