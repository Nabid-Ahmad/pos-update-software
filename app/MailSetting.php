<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MailSetting extends Model
{
    protected $guarded = ['id'];

    public function scopeCompanies($query)
    {
        if (auth()->check() && companyId()) {
            return $query->where('fk_company_id', companyId());
        }
        return $query;
    }
}
