<?php

namespace App\Repositories\User;

interface UserInterface
{
    public function create($options);
    public function update($options);
    public function getListUserByIdsOrderByMessage($array, $key_search);
    public function getListUserByIds($array, $key_search);
    public function getListUserBirthDayByIds($array);

}