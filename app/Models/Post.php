<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Post extends Model
{
    protected $table = 'post';
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'user_id_2',
        'group_id',
        'post_id',
        'type_post',
        'type_show',
        'data',
        'user_id_tags',
        'user_id_browse',
        'src_images',
    ];
    public function comment()
    {
        return $this->hasMany('App\Models\Comment', 'post_id', 'id');
    }
    public function share()
    {
        return $this->hasMany('App\Models\Post', 'post_id', 'id');
    }
    public function like()
    {
        return $this->hasMany('App\Models\Like', 'post_id', 'id')->where('type', '<>', 0);
    }
    public function group()
    {
        return $this->hasOne('App\Models\Group', 'id', 'group_id')->select('id', 'name', 'type', 'cover');
    }
    public function post_share()
    {
        return $this->hasOne('App\Models\Post', 'id', 'post_id');
    }
    public function isLike()
    {
        return $this->hasMany('App\Models\Like', 'post_id', 'id')->where('type', '<>', 0)->where('user_id', Auth::user()->id);
    }
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->select('id', 'first_name', 'last_name', 'avatar', 'phone');
    }
    public function user_2()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id_2')->select('id', 'first_name', 'last_name');
    }
}
