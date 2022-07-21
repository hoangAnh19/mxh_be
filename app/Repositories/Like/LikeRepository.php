<?php

namespace App\Repositories\Like;

use Illuminate\Support\Facades\Auth;
use App\Repositories\Relationship\RelationshipInterface;
use DB;
use App\Models\Like;

class LikeRepository implements LikeInterface
{
    public function __construct(RelationshipInterface $relationshipInterface)
    {
        $this->relationshipInterface = $relationshipInterface;
    }
    public function getListLikePost($post_id, $page)
    {
        return Like::where('post_id', $post_id)->where('type', '<>', 0)->select('type', 'user_id')->with('user')->offset(($page - 1) * 10)->limit(10)->get();
    }
    public function getCountLikePost($post_id)
    {
        return Like::where('post_id', $post_id)->with('user')->count();
    }
}
