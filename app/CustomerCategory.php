<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CustomerCategory extends Model
{
    protected $guarded = [];

    public static function boot()
{
    parent::boot();
    static::creating(function ($model){
        $model->fill([
            'company_id' => $model->company_id ?? (\auth()->check() ? companyId() : 1),
            'created_by' => \auth()->id() ?? 1,
            'updated_by' => \auth()->id() ?? 1,
        ]);
    });
    static::updating(function ($model){
        $model->fill([
            'updated_by' => \auth()->id() ?? 1,
        ]);
    });
}


    public function scopeOrdrBy($q)
    {
        return $q->orderBy('id','desc')->get();
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function scopeCompanies($query)
    {
        return $query->where('company_id', companyId());
    }


    public function created_user()
    {
        return $this->belongsTo(User::class, 'created_by')->select('id', 'name');
    }

    public function updated_user()
    {
        return $this->belongsTo(User::class, 'updated_by')->select('id', 'name');
    }

    public function scopeUserLog($query)
    {
        return $query->with('created_user', 'updated_user');
    }

}
