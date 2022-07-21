<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class   User extends Authenticatable  implements JWTSubject, MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $table = 'users';
    protected $fillable = [
        'avatar', 'cover'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function groups()
    {
        return $this->belongsToMany('App\Models\Member', 'user_id', 'group_id');
    }
    
    public function relationship1()
    {
        return $this->hasMany('App\Models\Relationship', 'user_id_1', 'id')->where('user_id_2', Auth::user()->id);
    }
    public function relationship2()
    {
        return $this->hasMany('App\Models\Relationship', 'user_id_2', 'id')->where('user_id_1', Auth::user()->id);
    }

    // public function posts()
    // {
    //     return $this->hasMany('App\Post', 'user_id', 'id');
    // }
}
