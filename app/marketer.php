<?php

namespace App;

use App\Traits\AddingCreatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class marketer extends Model
{
    use AddingCreatedBy;

    protected $guarded = ['id'];

    public function scopeCompanies($query)
    {
        if (Schema::hasColumn('marketers', 'fk_company_id')) {
            return $query->where('fk_company_id', companyId());
        }
        return $query;
    }

    public function marketersDetails()
    {
       return  $this->hasMany(marketers_details::class, 'marketers_id', 'id');
    }
}
