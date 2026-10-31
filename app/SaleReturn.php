<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{

    protected $guarded = [];

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

            static::updating(function ($model) {
                $model->fill([
                    'fk_updated_by' => auth()->id(),
                ]);
            });
            static::deleting(function ($model) {
                foreach ($model->sale_return_details as $item) {
                    $productStock = \App\ProductStock::where('fk_product_id', $item->product_id)
                        ->where('fk_company_id', $model->company_id)
                        ->where('warehouse_id', $item->warehouse_id)
                        ->first();

                    if ($productStock) {
                        $productStock->update([
                            'available_quantity' => $productStock->available_quantity - $item->quantity,
                        ]);
                    }
                }
                optional($model->transaction)->delete();
            });
        }
    }

    protected $casts = [
        'date' => 'date'
    ];

    /**
     * Accessor: returns the formatted invoice ID.
     * Falls back to #SR-{id} if invoice_no not set.
     */
    public function getInvoiceIdAttribute()
    {
        return $this->invoice_no ?? ('#SR-' . str_pad($this->id, 7, '0', STR_PAD_LEFT));
    }

    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'transactionable');
    }

    public function sale_return_details()
    {
        return $this->hasMany(SaleReturnDetail::class, 'sale_return_id', 'id');
    }

    public function sale_return_exchanges()
    {
        return $this->hasMany(SaleReturnExchange::class, 'sale_return_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id', 'id');
    }

    public function scopeCompanies($query)
    {
        return $query->where('company_id', companyId());
    }


    public function created_user()
    {
        return $this->belongsTo(User::class, 'created_by')->select('id', 'name');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
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


