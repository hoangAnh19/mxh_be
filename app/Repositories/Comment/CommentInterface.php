<?php

namespace App\Repositories\Comment;

interface CommentInterface
{
    public function create($options);
    public function getComment($option);
}