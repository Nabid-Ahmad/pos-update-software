<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillingCycle extends Model
{

    protected $fillable =[
        'name',
        'days',
        'description'
    ];
    
    public function packages()
    {
        return $this->hasMany(Package::class, 'fk_billing_cycle_id', 'id');
    }
    
    public function company_packages()
    {
        return $this->hasMany(CompanyPackage::class, 'fk_billing_cycle_id', 'id');
    }
}
