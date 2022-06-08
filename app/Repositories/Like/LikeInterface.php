<?php

namespace App\Repositories\Like;

interface LikeInterface
{
    public function getListLikePost($post_id,$page);
    public function getCountLikePost($post_id);
}