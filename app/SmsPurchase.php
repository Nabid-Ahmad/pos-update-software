<?php

namespace App;

use App\Traits\AddingCreatedBy;
use Illuminate\Database\Eloquent\Model;

class SmsPurchase extends Model
{
    use AddingCreatedBy;

    protected $fillable = [
        'fk_company_id',
        'fk_created_by',
        'fk_updated_by',
        'fk_purchased_by',
        'quantity',
        'amount',
        'status'
    ];
}