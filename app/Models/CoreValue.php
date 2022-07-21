<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoreValue extends Model
{
    protected $table = "core_values";
    use HasFactory;
    protected $fillable = [
        'id', 'CoreValue'
    ];
}
