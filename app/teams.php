<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class teams extends Model
{
    protected $fillable = [
        'user_id','profile','photo','partner_id'
    ];
}
