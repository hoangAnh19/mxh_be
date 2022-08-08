<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Repositories\User\UserInterface;
use App\Repositories\Relationship\RelationshipInterface;
use App\Models\User;
use App\Models\Relationship;
use Exception;
use Illuminate\Support\Facades\Validator;

class RelationshipController extends Controller
{
    public function __construct(UserInterface $userInterface, RelationshipInterface $relationshipInterface)
    {
        $this->userInterface = $userInterface;
        $this->relationshipInterface = $relationshipInterface;
    }






    public function listUserBirthday()
    {
        try {
            $list = $this->relationshipInterface->getlistUser();
            return [
                'status' => 'success',
                'data' => $list
            ];
        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'message' => $e
            ];
        }
    }
    public function getRelationship(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['exists:users,id'],
        ], [
            'user_id.exists' => 'Tai khoan khong ton tai',
        ]);
        if ($validator->fails()) {
            return [
                'status' => 'failed',
                "errors " => json_decode($validator->errors())
            ];
        }
        $user_id = $request->user_id ?? Auth::user()->id;
        if ($user_id == Auth::user()->id) return [
            'status' => 'success',
            'data' => 1
        ];
        $relationship = $this->relationshipInterface->getRelationship(Auth::user()->id, $user_id);
        if (!$relationship) {
            $friend = 'Chưa kết bạn';
            $follow = 'Chưa theo dõi';
            return [
                'status' => 'success',
                'data' => [
                    'friend' => $friend,
                    'follow' => $follow,
                    'type_friend'  => 4,
                    'type_follow'   => 2,
                    'date_accept' => null,
                ]
            ];
        } else
        if ($relationship && ($relationship->type_friend == config('relationship.type_friend.prevent') || $relationship->type_friend == config('relationship.type_friend.prevented'))) {
            return [
                'status' => 'failed',
                'message' => 'Tai khoan khong ton tai',
            ];
        } else {
            $compensation = ($relationship->user_id_1 == Auth::user()->id) ? 0 : 1;


            if ($relationship->type_follow == config('relationship.type_follow.follow') + $compensation || $relationship->type_follow == config('relationship.type_follow.double_follow')) {
                $follow = 'Đang theo dõi';
                $type_follow = 1;
            } else {
                $follow = 'Chưa theo dõi';
                $type_follow = 2;
            }
            if ($relationship->type_friend == config('relationship.type_friend.friend')) {
                $friend = 'Bạn bè';
                $type_friend = 1;
            } else if ($relationship->type_friend == config('relationship.type_friend.request_friend') + $compensation) {
                $friend = 'Đã gửi lời mời kết bạn';
                $type_friend = 2;
            } else if ($relationship->type_friend == config('relationship.type_friend.request_friended') - $compensation) {
                $friend = 'Chấp nhận lời mời';
                $type_friend = 3;
            } else {
                $type_friend = 4;
                $friend = 'Chưa kết bạn';
            }

            return [
                'status' => 'success',
                'data' => [
                    'friend' => $friend,
                    'follow' => $follow,
                    'type_friend' => $type_friend,
                    'type_follow'   => $type_follow,
                    'date_accept' => $relationship->type_friend == config('relationship.type_friend.friend') ? $relationship->date_accept : null,
                ]
            ];
        }
    }

    public function listPrevent()
    {
        $ids = $this->relationshipInterface->getListPrevent();
        $list = $this->userInterface->getListUserByIds($ids, null);

        return [
            'status' => 'success',
            'data' => $list
        ];
    }

    public function listFollow()
    {
        $ids = $this->relationshipInterface->getListFollow();
        $list = $this->userInterface->getListUserByIds($ids, null);
        foreach ($list as $item) {
            if ($item->id != Auth::user()->id)
                $item['count_mutual_friends'] = count($this->relationshipInterface->getMutualFriends($item->id));
        };
        return [
            'status' => 'success',
            'data' => $list
        ];
    }
    public function listRequestFriend()
    {
        $ids = $this->relationshipInterface->getListRequestFriend();
        $list = $this->userInterface->getListUserByIds($ids, null);
        foreach ($list as $item) {
            if ($item->id != Auth::user()->id)
                $item['count_mutual_friends'] = count($this->relationshipInterface->getMutualFriends($item->id));
        };
        return [
            'status' => 'success',
            'data' => $list
        ];
    }
    public function listRequestFriended(Request $request)
    {
        $page = $request->page ?? 1;
        $ids = $this->relationshipInterface->getListRequestFriended($page, 18);
        $list = $this->userInterface->getListUserByIds($ids, null);
        foreach ($list as $item) {
            if ($item->id != Auth::user()->id)
                $item['count_mutual_friends'] = count($this->relationshipInterface->getMutualFriends($item->id));
        };
        return [
            'status' => 'success',
            'data' => $list
        ];
    }
    public function listMutualFriend(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'exists:users,id'],
        ], [
            'user_id.required' => 'Vui lòng chọn tài khoản',
            'user_id.exists' => 'Tai khoan khong ton tai',
        ]);
        if ($validator->fails()) {
            return [
                'status' => 'failed',
                "errors " => json_decode($validator->errors())
            ];
        }
        $user_id = $request->user_id;
        if ($user_id ==  Auth::user()->id) {
            return [
                'status' => 'failed',
                "errors " => 'Error'
            ];
        }
        $relationship = $this->relationshipInterface->getRelationship(Auth::user()->id, $user_id);
        if ($relationship && ($relationship->type_friend == config('relationship.type_friend.prevent') || $relationship->type_friend == config('relationship.type_friend.prevented'))) {
            return [
                'status' => 'failed',
                'message' => 'Tai khoan khong ton tai',
            ];
        } else {
            $user = User::find($user_id);
            if ($user->display_friend) {
                $ids = $this->relationshipInterface->getMutualFriends($user_id);
                $list = $this->userInterface->getListUserByIds($ids, null);
                foreach ($list as $item) {
                    if ($item->id != Auth::user()->id)
                        $item['count_mutual_friends'] = count($this->relationshipInterface->getMutualFriends($item->id));
                };
                return [
                    'status' => 'success',
                    'data' => $list
                ];
            } else {
                return [
                    'status' => 'success',
                    'data'  => []
                ];
            }
        }
    }
    public function listUserSuggestions(Request $request)
    {
        $page = $request->page ?? 1;
        $ids = $this->relationshipInterface->getlistUserSuggestions($page);
        $list = $this->userInterface->getListUserByIds($ids, null);
        foreach ($list as $item) {
            if ($item->id != Auth::user()->id)
                $item['count_mutual_friends'] = count($this->relationshipInterface->getMutualFriends($item->id));
        };
        return [
            'status' => 'success',
            'data' => $list
        ];
    }
}
