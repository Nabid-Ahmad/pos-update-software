<?php

namespace App;

use App\Permission;
use App\Traits\AddingCreatedBy;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use AddingCreatedBy;
    protected $guarded = ['id'];


    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'role_companies', 'role_id', 'company_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'fk_company_id', 'id');
    }
}
