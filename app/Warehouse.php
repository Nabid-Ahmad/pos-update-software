<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $guarded = ['id'];

    public function scopeCompanies($query)
    {
        return $query->where('fk_company_id', companyId());
    }
}
