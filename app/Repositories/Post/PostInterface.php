<?php

namespace App\Repositories\Post;

interface PostInterface
{
    public function create($options);
    public function update($id, $options);
    public function getListPost($options);
    public function getListPostBrowse($options);
    public function getCountPost($options);
    public function getListSharePost($id, $page);
    public function searchPost($options);
    public function getListPostAdmin($page);
    public function deletePost($options);
}
