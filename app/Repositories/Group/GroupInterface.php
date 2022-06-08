<?php

namespace App\Repositories\Group;

interface GroupInterface
{
    public function create($options);
    public function update($id, $options);
    public function delete($id);
}