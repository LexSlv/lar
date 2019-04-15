<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bloger extends Model
{
    protected $table = 'blogers';
    protected $fillable = ['id', 'url', 'platform','page','check','remember_token','created_at', 'updated_at'];
}
