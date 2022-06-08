<?php

namespace App\Repositories\Relationship;

interface RelationshipInterface
{
    public function getRelationship($user_id_1, $user_id_2);
    public function createRelationship($user_id_1, $user_id_2, $type);
    public function updateRelationship($id, $type_friend, $type_follow, $date);
    public function getListFollowed($id);
    public function getListFollow();
    public function getListFriend($id, $page,$limit);
    public function getListPrevent();
    public function getListPreventAndPrevented();
    public function getListRequestFriend();
    public function getListFriendHasFollow();
    public function getListRequestFriended($page, $limit);
    public function getMutualFriends($id);
    public function getListFriendSuggestions($page);
    public function getCountFollowed($id);

}