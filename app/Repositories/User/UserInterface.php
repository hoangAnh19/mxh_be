<?php

namespace App\Repositories\User;

interface UserInterface
{
    public function create($options);
    public function update($options);
    public function getListUserBirthDayByIds($array);
    public function searchUser($user_name);
    public function assignRole($user_id, $role);
    public function getlistUser($page);
}
