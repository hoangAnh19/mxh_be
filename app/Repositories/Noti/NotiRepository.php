<?php

namespace App\Repositories\Noti;


use Carbon\Carbon;
use App\Models\Notification;

class NotiRepository implements NotiInterface
{

    public function create($options)
    {
        return Notification::create($options);
    }
    public function getNotification($option)
    {
        $query = Notification::query();
        if (isset($option['user_id'])) {
            $query->where('user_id_1', $option['user_id']);
        }
        $query->orderBy('id', 'desc')->limit(10);
        return $query->with('user_2')->get();
    }
}
