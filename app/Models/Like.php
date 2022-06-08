<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $table='like';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'post_id',
        'type',
    ];
    public function post()
    {
        return $this->hasOne('App\Models\Post', 'id', 'post_id');
    }
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->select('id','first_name', 'last_name', 'avatar');
    }
}
