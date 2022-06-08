<?php

namespace App\Repositories\Member;

interface MemberInterface
{
    public function create($options);
    public function update($id, $options);
    public function getListGroupId();
}