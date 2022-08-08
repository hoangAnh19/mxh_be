<?php

namespace App\Repositories\Comment;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Repositories\Relationship\RelationshipInterface;
use App\Repositories\Member\MemberInterface;
use App\Models\User;
use App\Models\Comment;
use App\Models\Like;
use App\Models\UserViewComment;

class CommentRepository implements CommentInterface
{
    public function __construct(RelationshipInterface $relationshipInterface, MemberInterface $memberInterface, Comment $comment)
    {
        $this->relationshipInterface = $relationshipInterface;
        $this->memberInterface = $memberInterface;
        $this->comment = $comment;
    }
    public function create($options)
    {
        return $this->comment::create($options);
    }
    public function getComment($option)
    {
        $query = Comment::query();
        if (isset($option['post_id'])) {
            $query->where('post_id', $option['post_id']);
        } else if (isset($option['comment_id'])) {
            $query->where('comment_id', $option['comment_id']);
        }
        $query->orderBy('id', 'desc');
        if ($option['page'] == 1)
            $query->offset(0)->limit(2);
        else  $query->offset(2 + ($option['page'] - 2) * 5)->limit(5);
        return $query->with('user')->get();
    }
}

// }
// $options['user_id'] = Auth::user()->id;
// $options['user_id_2'] = $request->user_id_2 ?? null;
// $options['group_id'] = $request->group_id ?? null;
// $options['post_id'] = $request->post_id ?? null;
// $options['type_post'] = $request->type_post ?? config('post.type_post.nomarl');
// $options['type_show'] = $request->type_show ?? config('post.type_show.public');
// $options['data'] = $request->data ?? null;
// $options['user_id_tags'] = $request->user_id_tags ?? null;
// $images = $request->images ?? null;
// $options['src_images'] = [];
