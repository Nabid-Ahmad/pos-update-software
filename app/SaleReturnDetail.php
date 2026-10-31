<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleReturnDetail extends Model
{
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id')->withTrashed();
    }

    public function sale_return()
    {
        return $this->belongsTo(SaleReturn::class, 'sale_return_id', 'id');
    }

    public function getConditionAttribute($value)
    {
        if (!is_null($value)) {
            return (bool) $value;
        }
        return ($this->condition_type ?? 'Good') === 'Good';
    }
}
