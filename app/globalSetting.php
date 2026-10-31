<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class globalSetting extends Model
{
    protected $fillable =[
        'fk_company_id',
        'fk_created_by',
        'fk_updated_by',
        'currency_code',
        'fk_product_tax_id',
        'fk_invoice_tax_id',
        'fk_default_discount_id',
        'purchase_prefix',
        'sales_prefix',
        'quotation_prefix'
    ];
}
