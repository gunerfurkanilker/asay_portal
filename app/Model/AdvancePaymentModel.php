<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AdvancePaymentModel extends Model
{
    protected $table = "AdvancePayment";
    protected $primaryKey = "id";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
}
