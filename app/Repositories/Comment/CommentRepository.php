<?php

namespace App\Repositories\Comment;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Repositories\Member\MemberInterface;
use App\Models\User;
use App\Models\Comment;
use App\Models\Like;
use App\Models\UserViewComment;

class CommentRepository implements CommentInterface
{
    public function __construct(MemberInterface $memberInterface, Comment $comment)
    {
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
