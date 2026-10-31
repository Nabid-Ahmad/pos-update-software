<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class taxRate extends Model
{
    protected $fillable = [
        'fk_company_id',
        'fk_created_by',
        'fk_updated_by',
        'tax_rate_title',
        'tax_rate_type',
        'amount'
    ];

    public function scopeCompanies($query)
    {
        return $query->where('fk_company_id', companyId());
    }
}