<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Events\ChatEvent;
use App\Repositories\Chat\ChatInterface;
use App\Models\Chat;
use Carbon\Carbon;
class ChatController extends Controller
{
    //
    public function __construct(ChatInterface $chatInterface)
    {
        $this->chatInterface = $chatInterface;
    }

    public function sendMessage(Request $request) {
        $sender_id = Auth::user()->id;
        // $receiver_id
        $receiver_id = $request->receiver_id ?? null;
        $chat = Chat::where(function($q) use ($receiver_id, $sender_id){
            $q->orWhere(function($q1) use ($receiver_id, $sender_id) {
                $q1->where('user_id_1', $sender_id)->where('user_id_2', $receiver_id);
            });
            $q->orWhere(function($q1) use ($receiver_id, $sender_id) {
                $q1->where('user_id_1', $receiver_id)->where('user_id_2', $sender_id);
            });
        })->first();
        if (!$chat) {
            $chat = new Chat();
            $chat->user_id_1 = $sender_id;
            $chat->user_id_2 = $receiver_id;
        }
        $option = [];
        $option['data'] = $request->data;
        $chat->last_time = Carbon::now()->format('Y-m-d H:i:s');
        $chat->save();
        if ($chat->user_id_1 == $sender_id) {
            $option['isOne'] = true;
        } else $option['isOne'] = false;
        $option['chat_id'] = $chat->id;
        if ($mess = $this->chatInterface->send($option)) {
            $result = [
                'mess_id' => $mess->id,
                'id' => $chat->id,
                'sender_id' => $sender_id,
                'receiver_id' => $receiver_id,
                'data' => $option['data'],
                'created_at' => $chat->last_time,
                'isOne' => $option['isOne']
            ];
            event(
                $e = new ChatEvent($result)
            );
            return $result;
        } else return [
            'status' => 'failed',
            "message" => 'Đã có lỗi xảy ra, vui lòng thử lại'
            ];

    }

    public function getList(Request $request) {
        $offset = $request->offset ?? 0;
        $list = $this->chatInterface->getListChat($offset);
        return $list;
    }
    public function getByIdUser(Request $request) {
        $id = $request->user_id;
        $item = $this->chatInterface->getByUserId($id);
        return $item;
    }
    public function getById(Request $request) {
        $id = $request->id;
        $item = $this->chatInterface->getById($id);
        return $item;
    }
    public function getMessage(Request $request) {
       $auth_id = Auth::user()->id;
       $receiver_id = $request->id;
       $chat = Chat::where(function($q) use ($receiver_id, $auth_id){
        $q->orWhere(function($q1) use ($receiver_id, $auth_id) {
            $q1->where('user_id_1', $auth_id)->where('user_id_2', $receiver_id);
        });
        $q->orWhere(function($q1) use ($receiver_id, $auth_id) {
            $q1->where('user_id_1', $receiver_id)->where('user_id_2', $auth_id);
        });
    })->first();
       $page = $request->page;
        if ($chat)
       return [
           'status' => 'success',
           'data'   => $this->chatInterface->getMessage($chat->id, $page)
       ];
       else return [
        'status' => 'failed',
        "message" => 'Đã có lỗi xảy ra, vui lòng thử lại'
        ];
    }
}
