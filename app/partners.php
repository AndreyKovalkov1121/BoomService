<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class partners extends Model
{
    protected $fillable = [
        'category_id','partner_name','address','postcodes','contact_name','email','telephone'
    ];
}
