<?php

namespace App\Models;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table='chat';
    public $timestamps = false;
    use HasFactory;
    protected $fillable = [
       'user_id_1',
       'user_id_2',
       'last_time'
    ];
    public function message()
    {
        return $this->hasMany('App\Models\Message', 'chat_id', 'id');
    }
    public function last_message()
    {
        return $this->hasOne('App\Models\Message', 'chat_id', 'id')->latest();
    }
    public function user_1()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id_1')->where('id', '<>', Auth::user()->id)->select('avatar', 'id','first_name','last_name','cover');
    }
    public function user_2()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id_2')->where('id', '<>', Auth::user()->id)->select('avatar','id','first_name','last_name','cover');
    }
}
