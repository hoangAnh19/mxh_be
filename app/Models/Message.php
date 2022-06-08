<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table='message';
    public $timestamps = true;
    use HasFactory;
    protected $fillable = [
       'chat_id',
       'data',
       'isOne',
    ];
    public function chat()
    {
        return $this->hasOne('App\Models\chat', 'id', 'chat_id');
    }
}
