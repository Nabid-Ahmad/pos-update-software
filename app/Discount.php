<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable =[
        'fk_company_id',
        'fk_created_by',
        'fk_updated_by',
        'title',
        'amount',
        'type'
    ];
}
