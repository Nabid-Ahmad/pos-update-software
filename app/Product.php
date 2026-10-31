<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $guarded = [''];

    public static function boot()
    {
        parent::boot();
        if (!app()->runningInConsole()){

            static::creating(function ($model){
                $model->fill([
                    'fk_company_id' => companyId(),
                    'fk_created_by' => auth()->id(),
                    'fk_updated_by' => auth()->id(),
                ]);
            });
            static::updating(function ($model){
                $model->fill([
                    'fk_updated_by' => auth()->id(),
                ]);
            });
            // static::deleting(function($model){
            //     ProductRak::where('product_id', $model->id)->delete();
            //     CustomerPricing::where('product_id', $model->id)->delete();
            //     ProductStock::where('fk_product_id', $model->id)->delete();
            // });
        }
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'fk_category_id', 'id')->withTrashed();
    }

//    public function spricing()
//    {
//        return $this->belongsTo(CustomerPricing::class, 'customer_id', 'id');
//    }

    public function category_name()
    {
        return $this->belongsTo(Category::class, 'fk_category_id', 'id')->select('id','category_name')->withTrashed();
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class)->withTrashed();
    }

    public function generic()
    {
        return $this->belongsTo(Generic::class);
    }

    public function productRak()
    {
        return $this->belongsTo(ProductRak::class);
    }


    public function purchase_details()
    {
        return $this->hasMany(PurchaseDetails::class, 'fk_product_id');
    }


    public function product_serials()
    {
        return $this->hasMany(ProductSerial::class, 'product_id');
    }

    public function sales_details()
    {
        return $this->hasMany(SalesDetails::class, 'fk_product_id');
    }

    public function product_stock()
    {
        return $this->hasOne(ProductStock::class, 'fk_product_id')->where('fk_company_id', companyId());
    }

    public function product_unit()
    {
        return $this->belongsTo(ProductUnit::class, 'fk_product_unit_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }


    public function barcode()
    {
        return $this->hasOne(Barcode::class);
    }


    public function todaySold()
    {
        return $this->sales_details()->whereHas('sale', function ($query) {
                $query->where('sale_date', today());
            });
    }


    public function todayPurchased()
    {
        return $this->purchase_details()->whereHas('purchase', function ($query) {
                $query->where('purchase_date', today());
            });
    }


    public function todaySoldQuantity()
    {
        return $this->todaySold->sum('quantity');
    }

    public function todaySoldAmount()
    {
        return $this->todaySold->sum('product_sub_total');
    }

    public function todayPurchaseQuantity()
    {
        return $this->todayPurchased->sum('quantity');
    }

    public function todayPurchaseAmount()
    {
        return $this->todayPurchased->sum('product_sub_total');
    }




    //scope methods
    public function scopeProducts($q)
    {
        return $q->where('fk_company_id', companyId());
    }

    public function pricing()
    {
        return $this->hasOne(CustomerPricing::class);
    }



    public function created_user()
    {
        return $this->belongsTo(User::class, 'fk_created_by')->select('id', 'name');
    }

    public function updated_user()
    {
        return $this->belongsTo(User::class, 'fk_updated_by')->select('id', 'name');
    }

    public function product_rak()
    {
        return $this->hasOne(ProductRak::class, 'product_id', 'id')->where('fk_company_id', companyId())->with('rak');
    }

    public function scopeCompanies($query)
    {
        return $query->where('fk_company_id', companyId());
    }

    public function scopeUserLog($query)
    {
        return $query->with('created_user', 'updated_user');
    }

    public function warehouse_stock($warehouse_id = null)
    {
        return $this->hasOne(ProductStock::class, 'fk_product_id', 'id')->where('fk_company_id', companyId())->where('warehouse_id', $warehouse_id)->first();
    }

    public function warehouse_stocks()
    {
        return $this->hasMany(ProductStock::class, 'fk_product_id', 'id')->where('fk_company_id', companyId());
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'fk_company_id', 'id');
    }

}
