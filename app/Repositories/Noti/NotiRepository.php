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

// }
// $options['user_id'] = Auth::user()->id;
// $options['user_id_2'] = $request->user_id_2 ?? null;
// $options['group_id'] = $request->group_id ?? null;
// $options['post_id'] = $request->post_id ?? null;
// $options['type_post'] = $request->type_post ?? config('post.type_post.nomarl');
// $options['type_show'] = $request->type_show ?? config('post.type_show.public');
// $options['data'] = $request->data ?? null;
// $options['user_id_tags'] = $request->user_id_tags ?? null;
// $images = $request->images ?? null;
// $options['src_images'] = [];
