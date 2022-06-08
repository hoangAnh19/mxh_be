<?php
namespace App\Repositories\Chat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Message;
use App\Models\Chat;
use App\Models\Member;
use DB;

class ChatRepository implements ChatInterface
{
    public function send($options) {
        return Message::create($options);

    }
    public function getMessage($chat_id, $page = 1) {
       return Message::where('chat_id',$chat_id)->orderBy('id', 'desc')->offset(($page-1)*20)->limit(20)->select('isOne', 'data', 'created_at')->get();
    }
    public function getListChat($offset = 1) {
        $query = Chat::query();
        $auth = Auth::user()->id;
         $query->where(function($q) use ($auth){
            $q->orWhere(function($q1) use ($auth) {
                $q1->where('user_id_1', $auth);
            });
            $q->orWhere(function($q1) use ($auth) {
                $q1->where('user_id_2', $auth);
            });
        })->with([
            'user_1',
            'user_2',
            'last_message',
        ]);
        if($offset <12) {
            return $query->orderBy('last_time', 'desc')->offset($offset)->limit(12)->get();
        }
        else
            return $query->orderBy('last_time', 'desc')->offset($offset)->limit(6)->get();

    }
    public function getById($id) {
        $query = Chat::where('id', $id)->with([
            'user_1',
            'user_2',
            'last_message',
        ])->first();;

    return $query;
    }

    public function getListIdChat($user_id) {
        $query1 = Chat::where('user_id_1', $user_id)
        ->pluck('user_id_2');
        $query2 = Chat::where('user_id_2', $user_id)
        ->pluck('user_id_1');
        return $query2->concat($query1);
    }
    public function getByUserId($user_id) {
        $query = Chat::where('user_id_1', $user_id)->where('user_id_2', Auth::id())->with([
            'last_message',
        ])->get();
        if (!count($query)) {
            $query = Chat::where('user_id_2', $user_id)->where('user_id_1', Auth::id())->with([
                'last_message',
            ])->get();
        }
        return $query;
    }
}