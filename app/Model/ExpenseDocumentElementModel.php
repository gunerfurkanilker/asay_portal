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
}
