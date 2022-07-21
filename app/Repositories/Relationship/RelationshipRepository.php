<?php

namespace App\Repositories\Relationship;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Relationship;

class RelationshipRepository implements RelationshipInterface
{

    public function getListFriend1()
    {
        $listFriend = User::where('email', '!=', 'admin123@gmail.com')->get();
        return $listFriend;
    }
}
