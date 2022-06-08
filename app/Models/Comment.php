<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Comment extends Model
{
    protected $table='comment';
    use HasFactory;
    protected $fillable = [
        'user_id',
        'post_id',
        'comment_id',
        'data',
        'src_images',
    ];
    public $listPrevent=[];
    // public function group()
    // {
    //     return $this->hasOne('App\Models\Group', 'id', 'group_id')->select('id', 'name', 'type','cover');
    // }
    public function answer_comment()
    {
        return $this->hasMany('App\Models\Comment', 'comment_id', 'id')->whereNull('post_id')->whereNotIn('user_id', $this->listPrevent);
    }
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->select('id', 'first_name', 'last_name','avatar');
    }
    // public function user_2()
    // {
    //     return $this->hasOne('App\Models\User', 'id', 'user_id_2')->select('id', 'first_name', 'last_name');
    // }
}
