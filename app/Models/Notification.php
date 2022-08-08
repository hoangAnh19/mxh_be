<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;


    protected $table = 'notification';
    use HasFactory;
    protected $fillable = [
        'user_id_1',
        'user_id_2',
        'post_id',
        'type',
        'data',
        'seen'
    ];


    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->select('id', 'first_name', 'last_name', 'avatar');
    }
    public function user_2()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id_2')->select('id', 'first_name', 'last_name', 'avatar');
    }
}
