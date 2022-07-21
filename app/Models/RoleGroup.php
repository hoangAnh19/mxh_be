<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleGroup extends Model
{
    protected $table = "role_groups";
    use HasFactory;
    protected $fillable = [
        'id', 'roleGroup'
    ];
}
