<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{

    public static function boot()
    {
        parent::boot();
        if (!app()->runningInConsole()){

            static::creating(function ($model){
                $sale_max_id = $model::max('id') + 1;
                $model->fill([
                    'fk_company_id' => companyId(),
                    'fk_created_by' => auth()->id(),
                    'fk_updated_by' => auth()->id(),
                    'fk_status_id' => optional(Status::where('name', 'Completed')->first())->id ?? 1,
                    'sale_reference' => 'sale-'.$sale_max_id
                ]);
            });

            static::updating(function ($model){
                $model->fill([
                    'fk_updated_by' => auth()->id(),
                ]);
            });

            static::deleting(function($model){

                optional($model->saleMeta)->delete();

                // Removed to prevent double counting of stock during sale deletion.
                // SaleController@destroy already handles stock and serials updates properly.
                /*
                foreach ($model->sale_details as $item){
                    $productStock = \App\ProductStock::where('fk_product_id', $item->fk_product_id)
                        ->where('fk_company_id', $model->fk_company_id)
                        ->where('warehouse_id', $item->warehouse_id)
                        ->first();

                    if ($productStock) {
                        $productStock->update([
                            'sold_quantity' => $productStock->sold_quantity - $item->quantity,
                            'available_quantity' => $productStock->available_quantity + $item->quantity,
                        ]);
                    }
                }
                */

                $model->sale_details->each->delete();
                $model->payments()->delete();
            });
        }
    }
    protected $guarded = ['id'];
    protected $dates = ['sale_date'];

    public function  getInvoiceIdAttribute()
    {
        if ($this->sale_invoice_id) {
            return "#S-".$this->sale_invoice_id;
        }
        return "#S-".str_pad($this->id, 5, '0', 0);
    }

    public function payments()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    public function sale_customer()
    {
        return $this->hasOne('App\Customer', 'id', 'fk_customer_id');
    }

    public function saleMeta()
    {
        return $this->hasOne(SaleMeta::class);
    }

    public function sale_status()
    {
        return $this->hasOne('App\Status', 'id', 'fk_status_id');
    }

    public function sale_details()
    {
        return $this->hasMany(SalesDetails::class, 'fk_sales_id', 'id');
    }

    public function sale_company()
    {
        return $this->hasOne('App\Company', 'id', 'fk_company_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class,'fk_customer_id','id')->withTrashed();
    }
    public function consumer()
    {
        return $this->customer();
    }
    public function transaction()
    {
        return $this->morphOne(Transaction::class,'transactionable');
    }

    public function getPaidAttribute()
    {
        // A sale transaction represents customer payment which is positive (> 0).
        // Company-paid delivery charges are stored as negative transactions (< 0).
        // We sum all positive transactions to retrieve the correct customer payment amount.
        return $this->payments()->where('amount', '>', 0)->sum('amount') + $this->adjusted_paid;
    }

    public function getInvoiceAmountAttribute()
    {
        return $this->sub_total - $this->invoice_discount + $this->invoice_tax;
    }

    public function getPayableAmountAttribute()
    {
        $special_discount = $this->special_discount;
        $courier_charge = 0;
        if ($this->delivery_charge_payer == 'customer') {
            $courier_charge = floatval($this->currier_amount ?? $this->delivery_charge ?? 0);
        }

        $amount = $this->invoiceAmount - $special_discount + floatval($this->cod_amount) + $courier_charge - $this->advanced + $this->previous_due;
        return $amount < 0 ? 0 : $amount;
    }

    public function getDueAttribute()
    {
        if (!$this->payableAmount){
            return 0;
        }
        return $this->payableAmount - $this->paid;
    }


    public function productReturn()
    {
        return $this->hasMany(SaleReturn::class, 'sale_id');
    }

    public function returnedItems()
    {
        return $this->sale_details()->whereHas('itemReturn');
    }
    public function wastedItems()
    {
        return $this->sale_details()->whereHas('itemWasted');
    }

    public function totalReturnItems()
    {
        return $this->sale_details()->whereHas('itemReturn')
            ->orWhereHas('itemWasted');
    }

    public function returns()
    {
        return $this->returnedItems->merge($this->wastedItems);
    }

    public function availableBag()
    {
        return $this->paid - $this->productReturn->sum('change_amount');
    }

    public function currier()
    {
        return $this->belongsTo(Currier::class)->select('name', 'id');
    }

    public function scopeCompanies($query)
    {
        return $query->where('fk_company_id', companyId());
    }


    public function created_user()
    {
        return $this->belongsTo(User::class, 'fk_created_by')->select('id', 'name');
    }

    public function updated_user()
    {
        return $this->belongsTo(User::class, 'fk_updated_by')->select('id', 'name');
    }
    public function marketers()
    {
        return $this->belongsTo(marketer::class, 'marketers_id', 'id');
    }

    public function scopeUserLog($query)
    {
        return $query->with('created_user', 'updated_user');
    }

    public function sale_supplier()
    {
        return $this->belongsTo(\App\Supplier::class, 'supplier_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(\App\Supplier::class, 'supplier_id', 'id');
    }

}
