<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleReturnExchange extends Model
{
    protected $guarded = ['id'];

    public static function boot()
    {
        parent::boot();
        if (!app()->runningInConsole()){
            static::creating(function ($model) {
                $model->fill([
                    'company_id' => companyId(),
                    'created_by' => auth()->id(),
                ]);
            });
        }
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function sale_return()
    {
        return $this->belongsTo(SaleReturn::class, 'sale_return_id', 'id');
    }
}
