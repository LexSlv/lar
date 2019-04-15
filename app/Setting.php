<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $fillable = ['id', 'setting_key', 'setting_value', 'created_at', 'updated_at'];
}
