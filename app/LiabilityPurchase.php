<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class LiabilityPurchase extends Model
{
    protected $guarded = ['id'];

    public static function boot()
    {
        parent::boot();

        if (!app()->runningInConsole() ){
            static::creating(function ($model) {
                $model->fill([
                    'company_id' => companyId(),
                    'created_by' => auth()->id(),
                ]);
            });

            static::updating(function ($model){
                $model->fill([
                    'updated_by' => auth()->id(),
                ]);
            });
        }
    }

    public function details()
    {
        return $this->hasMany(LiabilityPurchaseDetail::class, 'liability_purchase_id', 'id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }
}
