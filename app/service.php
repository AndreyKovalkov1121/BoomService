<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class service extends Model
{
    protected $fillable = [
        'category_id','service_name','normal_price','boom_price'
    ];
}
