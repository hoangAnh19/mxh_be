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
use Validator;

class RelationshipController extends Controller
{
    public function __construct(UserInterface $userInterface, RelationshipInterface $relationshipInterface)
    {
        $this->userInterface = $userInterface;
        $this->relationshipInterface = $relationshipInterface;
    }

    public function requestFriend(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'exists:users,id'],
        ], [
            'user_id.required' => 'Vui long chon tai khoan',
            'user_id.exists' => 'Tai khoan khong ton tai',
        ]);
        if ($validator->fails()) {
            return [
            'status' => 'failed',
            "errors " => json_decode($validator->errors())
            ];
        }
        $user_id_1 = Auth::user()->id;
        $user_id_2 = intval($request->user_id);
        $relationship = $this->relationshipInterface->getRelationship($user_id_1, $user_id_2);
        try {
            if (!$relationship) {
                $relationship = new Relationship;
                $relationship->user_id_1 = $user_id_1;
                $relationship->user_id_2 = $user_id_2;
                $relationship->type_follow = config('relationship.type_follow.follow');
                $relationship->type_friend = config('relationship.type_friend.request_friend');
                $relationship->date_accept = Carbon::now()->format('Y-m-d H:i:s');
                $relationship->save();
                return [
                    'status' => 'success',
                    'message' => 'Gui loi moi thanh cong',
                ];
            } else {
                $user_id_1 = ($relationship->user_id_1 == $user_id_1) ? $user_id_1 : $user_id_2;
                $user_id_2 = ($relationship->user_id_2 == $user_id_2) ? $user_id_2 : $user_id_1;
                $compensation = ($relationship->user_id_2 == $user_id_2) ? 0 : 1;
                if ($relationship->type_friend == config('relationship.type_friend.no_friend')) {
                    $this->relationshipInterface->updateRelationship($relationship->id, config('relationship.type_friend.request_friend') + $compensation, config('relationship.type_follow.follow') + $compensation);
                    return [
                        'status' => 'success',
                        'message' => 'Gui loi moi thanh cong',
                    ];
                } else if ($relationship->type_friend == config('relationship.type_friend.friend')) {
                    return [
                        'status' => 'failed',
                        'message' => 'Cac ban da la ban be truoc do',
                    ];
                } else if ($relationship->type_friend == config('relationship.type_friend.request_friend') + $compensation) {
                    return [
                        'status' => 'failed',
                        'message' => 'Ban da gui yeu cau truoc do roi',
                    ];
                } else if ($relationship->type_friend == config('relationship.type_friend.request_friended') - $compensation) {
                    if ($relationship->type_follow == config('relationship.type_follow.double_follow') || $relationship->type_follow == config('relationship.type_follow.followed') - $compensation) {
                        $this->relationshipInterface->updateRelationship($relationship->id, config('relationship.type_friend.friend'), config('relationship.type_follow.double_follow'), true);
                    } else {
                        $this->relationshipInterface->updateRelationship($relationship->id, config('relationship.type_friend.friend'), config('relationship.type_follow.follow') + $compensation, true);
                    }
                    return [
                        'status' => 'success',
                        'message' => 'Cac ban da tro thanh ban be',
                    ];
                } else {
                    return [
                        'status' => 'failed',
                        'message' => 'Tai khoan khong ton tai',
                    ];
                }
            }
        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'message' => 'Da co loi xay ra, vui long thu lai',
            ];
        }
    }

    public function acceptFriend(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'exists:users,id'],
        ], [
            'user_id.required' => 'Vui long chon tai khoan',
            'user_id.exists' => 'Tai khoan khong ton tai',
        ]);
        if ($validator->fails()) {
            return [
            'status' => 'failed',
            "errors " => json_decode($validator->errors())
            ];
        }
        $user_id_1 = Auth::user()->id;
        $user_id_2 = intval($request->user_id);
        $relationship = $this->relationshipInterface->getRelationship($user_id_1, $user_id_2);
        try {
            if (!$relationship) {
                return [
                    'status' => 'failed',
                    'message' => 'Tai khoan nay khong gui loi moi den ban',
                ];
            } else {
                $user_id_1 = ($relationship->user_id_1 == $user_id_1) ? $user_id_1 : $user_id_2;
                $user_id_2 = ($relationship->user_id_2 == $user_id_2) ? $user_id_2 : $user_id_1;
                $compensation = ($relationship->user_id_2 == $user_id_2) ? 0 : 1;
                if ($relationship->type_friend == config('relationship.type_friend.no_friend')) {
                    return [
                        'status' => 'failed',
                        'message' => 'Tai khoan nay khong gui loi moi den ban',
                    ];
                } else if ($relationship->type_friend == config('relationship.type_friend.friend')) {
                    return [
                        'status' => 'failed',
                        'message' => 'Cac ban da la ban be truoc do',
                    ];
                } else if ($relationship->type_friend == config('relationship.type_friend.request_friend') + $compensation) {
                    return [
                        'status' => 'failed',
                        'message' => 'Tai khoan nay khong gui loi moi den ban',
                    ];
                } else if ($relationship->type_friend == config('relationship.type_friend.request_friended') - $compensation) {
                    if ($relationship->type_follow == config('relationship.type_follow.double_follow') || $relationship->type_follow == config('relationship.type_follow.followed') - $compensation) {
                        $this->relationshipInterface->updateRelationship($relationship->id, config('relationship.type_friend.friend'), config('relationship.type_follow.double_follow'), true);
                    } else {
                        $this->relationshipInterface->updateRelationship($relationship->id, config('relationship.type_friend.friend'), config('relationship.type_follow.follow') + $compensation, true);
                    }
                    return [
                        'status' => 'success',
                        'message' => 'Cac ban da tro thanh ban be',
                    ];
                } else {
                    return [
                        'status' => 'failed',
                        'message' => 'Tai khoan khong ton tai',
                    ];
                }
            }
        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'message' => 'Da co loi xay ra, vui long thu lai',
            ];
        }
    }

    public function cancelFriend(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'exists:users,id'],
        ], [
            'user_id.required' => 'Vui long chon tai khoan',
            'user_id.exists' => 'Tai khoan khong ton tai',
        ]);
        if ($validator->fails()) {
            return [
            'status' => 'failed',
            "errors " => json_decode($validator->errors())
            ];
        }
        $user_id_1 = Auth::user()->id;
        $user_id_2 = intval($request->user_id);
        $relationship = $this->relationshipInterface->getRelationship($user_id_1, $user_id_2);
        try {
            if (!$relationship) {
                return [
                    'status' => 'failed',
                    'message' => 'Cac ban khong phai la ban be',
                ];
            } else {
                $user_id_1 = ($relationship->user_id_1 == $user_id_1) ? $user_id_1 : $user_id_2;
                $user_id_2 = ($relationship->user_id_2 == $user_id_2) ? $user_id_2 : $user_id_1;
                $compensation = ($relationship->user_id_2 == $user_id_2) ? 0 : 1;
                if ($relationship->type_friend == config('relationship.type_friend.no_friend') || $relationship->type_friend == config('relationship.type_friend.request_friend') || $relationship->type_friend == config('relationship.type_friend.request_friended')) {
                    return [
                        'status' => 'failed',
                        'message' => 'Cac ban khong phai la ban be',
                    ];
                } else if ($relationship->type_friend == config('relationship.type_friend.friend')) {
                    $this->relationshipInterface->updateRelationship($relationship->id, config('relationship.type_friend.no_friend'), config('relationship.type_follow.no_follow'), true);
                    return [
                        'status' => 'success',
                        'message' => 'Huy ket ban thanh cong',
                    ];
                } else {
                    return [
                        'status' => 'failed',
                        'message' => 'Tai khoan khong ton tai',
                    ];
                }
            }
        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'message' => 'Da co loi xay ra, vui long thu lai',
            ];
        }
    }

    public function cancelRequestFriend(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'exists:users,id'],
        ], [
            'user_id.required' => 'Vui long chon tai khoan',
            'user_id.exists' => 'Tai khoan khong ton tai',
        ]);
        if ($validator->fails()) {
            return [
            'status' => 'failed',
            "errors " => json_decode($validator->errors())
            ];
        }
        $user_id_1 = Auth::user()->id;
        $user_id_2 = intval($request->user_id);
        $relationship = $this->relationshipInterface->getRelationship($user_id_1, $user_id_2);
        try {
            if (!$relationship) {
                return [
                    'status' => 'failed',
                    'message' => 'Không thể hủy kết bạn',
                ];
            } else {
                $user_id_1 = ($relationship->user_id_1 == $user_id_1) ? $user_id_1 : $user_id_2;
                $user_id_2 = ($relationship->user_id_2 == $user_id_2) ? $user_id_2 : $user_id_1;
                $compensation = ($relationship->user_id_2 == $user_id_2) ? 0 : 1;
                if ($relationship->type_friend == config('relationship.type_friend.no_friend')) {
                    return [
                        'status' => 'failed',
                        'message' => 'Không thể hủy kết bạn',
                    ];
                } else if ($relationship->type_friend == config('relationship.type_friend.request_friended') || $relationship->type_friend == config('relationship.type_friend.request_friend')) {
                    $this->relationshipInterface->updateRelationship($relationship->id, config('relationship.type_friend.no_friend'), null);
                    return [
                        'status' => 'success',
                        'message' => 'Huy thanh cong',
                    ];
                } else {
                    return [
                        'status' => 'failed',
                        'message' => 'Tai khoan khong ton tai',
                    ];
                }
            }
        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'message' => 'Da co loi xay ra, vui long thu lai',
            ];
        }
    }

    public function follow(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'exists:users,id'],
        ], [
            'user_id.required' => 'Vui long chon tai khoan',
            'user_id.exists' => 'Tai khoan khong ton tai',
        ]);
        if ($validator->fails()) {
            return [
            'status' => 'failed',
            "errors " => json_decode($validator->errors())
            ];
        }
        $user_id_1 = Auth::user()->id;
        $user_id_2 = intval($request->user_id);
        $relationship = $this->relationshipInterface->getRelationship($user_id_1, $user_id_2);
        try {
            if (!$relationship) {
                $relationship = new Relationship();
                $relationship->user_id_1 = $user_id_1;
                $relationship->user_id_2 = $user_id_2;
                $relationship->type_follow = config('relationship.type_follow.follow');
                $relationship->type_friend = config('relationship.type_friend.no_friend');
                $relationship->save();
                return [
                    'status' => 'success',
                    'message' => 'Follow thanh cong',
                ];
            } else {
                if ($relationship->type_friend == config('relationship.type_friend.prevent') || $relationship->type_friend == config('relationship.type_friend.prevented')) {
                    return [
                        'status' => 'failed',
                        'message' => 'Tai khoan khong ton tai',
                    ];
                }
                $user_id_1 = ($relationship->user_id_1 == $user_id_1) ? $user_id_1 : $user_id_2;
                $user_id_2 = ($relationship->user_id_2 == $user_id_2) ? $user_id_2 : $user_id_1;
                $compensation = ($relationship->user_id_2 == $user_id_2) ? 0 : 1;
                if ($relationship->type_follow == config('relationship.type_follow.no_follow')) {
                    $this->relationshipInterface->updateRelationship($relationship->id, null, config('relationship.type_follow.follow') + $compensation);
                    return [
                        'status' => 'success',
                        'message' => 'Follow thanh cong',
                    ];
                } else if ($relationship->type_follow == config('relationship.type_follow.double_follow') || $relationship->type_follow == config('relationship.type_follow.follow') + $compensation) {
                    return [
                        'status' => 'failed',
                        'message' => 'Ban da follow tai khoan nay roi',
                    ];
                } else if ($relationship->type_follow == config('relationship.type_follow.followed') - $compensation) {
                    $this->relationshipInterface->updateRelationship($relationship->id, null, config('relationship.type_follow.double_follow'));
                    return [
                        'status' => 'success',
                        'message' => 'Follow thanh cong',
                    ];
                }
            }
        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'message' => 'Da co loi xay ra, vui long thu lai',
            ];
        }
    }

    public function cancelFollow(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'exists:users,id'],
        ], [
            'user_id.required' => 'Vui long chon tai khoan',
            'user_id.exists' => 'Tai khoan khong ton tai',
        ]);
        if ($validator->fails()) {
            return [
            'status' => 'failed',
            "errors " => json_decode($validator->errors())
            ];
        }
        $user_id_1 = Auth::user()->id;
        $user_id_2 = intval($request->user_id);
        $relationship = $this->relationshipInterface->getRelationship($user_id_1, $user_id_2);
        try {
            if (!$relationship) {
                return [
                    'status' => 'failed',
                    'message' => 'Ban khong follow tai khoan nay',
                ];
            } else {
                if ($relationship->type_friend == config('relationship.type_friend.prevent') || $relationship->type_friend == config('relationship.type_friend.prevented')) {
                    return [
                        'status' => 'failed',
                        'message' => 'Tai khoan khong ton tai',
                    ];
                }
                $user_id_1 = ($relationship->user_id_1 == $user_id_1) ? $user_id_1 : $user_id_2;
                $user_id_2 = ($relationship->user_id_2 == $user_id_2) ? $user_id_2 : $user_id_1;
                $compensation = ($relationship->user_id_2 == $user_id_2) ? 0 : 1;
                if ($relationship->type_follow == config('relationship.type_follow.no_follow') || $relationship->type_follow == config('relationship.type_follow.followed') - $compensation) {
                    return [
                        'status' => 'failed',
                        'message' => 'Ban khong follow tai khoan nay',
                    ];
                } else if ($relationship->type_follow == config('relationship.type_follow.double_follow')) {
                    $this->relationshipInterface->updateRelationship($relationship->id, null, config('relationship.type_follow.followed') - $compensation);
                    return [
                        'status' => 'success',
                        'message' => 'Huy follow thanh cong',
                    ];
                } else if ($relationship->type_follow == config('relationship.type_follow.follow') + $compensation) {
                    $this->relationshipInterface->updateRelationship($relationship->id, null, config('relationship.type_follow.no_follow'));
                    return [
                        'status' => 'success',
                        'message' => 'Huy follow thanh cong',
                    ];
                }
            }
        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'message' => 'Da co loi xay ra, vui long thu lai',
            ];
        }
    }

    public function prevent(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'exists:users,id'],
        ], [
            'user_id.required' => 'Vui long chon tai khoan',
            'user_id.exists' => 'Tai khoan khong ton tai',
        ]);
        if ($validator->fails()) {
            return [
            'status' => 'failed',
            "errors " => json_decode($validator->errors())
            ];
        }
        $user_id_1 = Auth::user()->id;
        $user_id_2 = intval($request->user_id);
        $relationship = $this->relationshipInterface->getRelationship($user_id_1, $user_id_2);
        try {
            if (!$relationship) {
                $relationship = new Relationship();
                $relationship->user_id_1 = $user_id_1;
                $relationship->user_id_2 = $user_id_2;
                $relationship->type_follow = config('relationship.type_follow.no_follow');
                $relationship->type_friend = config('relationship.type_friend.prevent');
                $relationship->save();
                return [
                    'status' => 'success',
                    'message' => 'Chan thanh cong',
                ];
            } else {
                $user_id_1 = ($relationship->user_id_1 == $user_id_1) ? $user_id_1 : $user_id_2;
                $user_id_2 = ($relationship->user_id_2 == $user_id_2) ? $user_id_2 : $user_id_1;
                $compensation = ($relationship->user_id_2 == $user_id_2) ? 0 : 1;
                if ($relationship->type_friend == config('relationship.type_friend.prevented') - $compensation) {
                    return [
                        'status' => 'failed',
                        'message' => 'Tai khoan khong ton tai',
                    ];
                } else {
                    $this->relationshipInterface->updateRelationship($relationship->id, config('relationship.type_friend.prevent') + $compensation, config('relationship.type_follow.no_follow'));
                    return [
                        'status' => 'success',
                        'message' => 'Chan thanh cong',
                    ];
                }
            }
        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'message' => 'Da co loi xay ra, vui long thu lai',
            ];
        }
    }
    public function listFollowed(Request $request)
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
        $relationship = $this->relationshipInterface->getRelationship(Auth::user()->id, $user_id);
        if ($relationship && ($relationship->type_friend == config('relationship.type_friend.prevent') || $relationship->type_friend == config('relationship.type_friend.prevented'))){
            return [
                'status' => 'failed',
                'message' => 'Tai khoan khong ton tai',
            ];
        } else {
            $user = User::find($user_id);
            if ($user_id == Auth::user()->id || $user->display_follow) {
                $ids = $this->relationshipInterface->getListFollowed($user_id);
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
    public function countFollowed(Request $request)
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
        $relationship = $this->relationshipInterface->getRelationship(Auth::user()->id, $user_id);
        if ($relationship && ($relationship->type_friend == config('relationship.type_friend.prevent') || $relationship->type_friend == config('relationship.type_friend.prevented'))){
            return [
                'status' => 'failed',
                'message' => 'Tai khoan khong ton tai',
            ];
        } else {
            $user = User::find($user_id);
                $count = $this->relationshipInterface->getCountFollowed($user_id);

                return [
                    'status' => 'success',
                    'data' => $count
                ];
            }
    }

    public function listFriend(Request $request)
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
        $page = $request->page ?? 1;
        $relationship = $this->relationshipInterface->getRelationship(Auth::user()->id, $user_id);
        if ($relationship && ($relationship->type_friend == config('relationship.type_friend.prevent') || $relationship->type_friend == config('relationship.type_friend.prevented'))){
            return [
                'status' => 'failed',
                'message' => 'Tai khoan khong ton tai',
            ];
        } else {
            $user = User::find($user_id);
            if ($user_id == Auth::user()->id || $user->display_friend) {
                $ids = $this->relationshipInterface->getListFriend($user_id, $page, 18);
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
    public function listFriendBirthday()
    {
        try {
            $ids = $this->relationshipInterface->getListFriend(Auth::user()->id,0,0);
            $list = $this->userInterface->getListUserBirthDayByIds($ids);
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
        if ($relationship && ($relationship->type_friend == config('relationship.type_friend.prevent') || $relationship->type_friend == config('relationship.type_friend.prevented'))){
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
        if ($relationship && ($relationship->type_friend == config('relationship.type_friend.prevent') || $relationship->type_friend == config('relationship.type_friend.prevented'))){
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
    public function listFriendSuggestions(Request $request) {
        $page = $request->page ?? 1;
        $ids = $this->relationshipInterface->getListFriendSuggestions($page);
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
