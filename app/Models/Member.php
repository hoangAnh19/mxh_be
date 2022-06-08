<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $table='member';
    public $timestamps = true;
    use HasFactory;
    public function group()
    {
        return $this->belongsTo('App\Models\Group', 'group_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

}
