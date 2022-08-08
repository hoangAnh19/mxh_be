<?php

namespace App\Repositories\Relationship;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Relationship;

class RelationshipRepository implements RelationshipInterface
{

    public function getlistUser()
    {
        $listUser = User::where('email', '!=', 'admin123@gmail.com')->get();
        return $listUser;
    }
}
