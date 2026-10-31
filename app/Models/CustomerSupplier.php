<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerSupplier extends Model
{
     public $timestamps = false;
     protected $fillable = ['customer_id', 'supplier_id', 'balance', 'opening_balance'];
     protected $primaryKey = 'customer_id';
    public $incrementing = false; // if it's not auto-increment
}
