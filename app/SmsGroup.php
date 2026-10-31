<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmsGroup extends Model
{
    protected $guarded = ['id'];
    protected $table ='sms_groups';
}
