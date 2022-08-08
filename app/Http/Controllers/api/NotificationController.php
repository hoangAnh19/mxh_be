<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Repositories\Noti\NotiInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    //


    public function getNoti(Request $request)
    {
        $options = [];
        $options['user_id'] = $request->user_id ?? null;


        $query = Notification::where('user_id_1', $options['user_id'])->orderBy('id', 'desc')->limit(10)->with('user_2')->get();

        if ($query) {
            return response()->json([
                'status' => 'success',
                "data" => $query,
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'Đã có lỗi xảy ra, vui lòng thử lại'
            ]);
        }
    }

    public function createNoti(Request $request)
    {
        $noti = new Notification();

        $noti->user_id_1 = $request->user_id;
        $noti->user_id_2 =  Auth::user()->id;
        $noti->post_id = $request->post_id;
        $noti->type = $request->type;
        $noti->seen = 0;
        if ($noti->save()) {
            return response()->json([
                'status' => 'success',
                "data" => $noti,
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'Đã có lỗi xảy ra, vui lòng thử lại'
            ]);
        }
    }

    public function updateNoti(Request $request)
    {
        $noti =  Notification::find($request->noti_id);

        $noti->seen = 1;
        if ($noti->save()) {
            return response()->json([
                'status' => 'success',
                "data" => $noti,
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'Đã có lỗi xảy ra, vui lòng thử lại'
            ]);
        }
    }
}
