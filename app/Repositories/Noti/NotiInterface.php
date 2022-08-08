<?php

namespace App\Repositories\Noti;

interface NotiInterface
{
    public function create($options);
    public function getNotification($option);
}
