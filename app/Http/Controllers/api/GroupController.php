<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Group\GroupInterface;
use App\Repositories\Member\MemberInterface;
use App\Repositories\Relationship\RelationshipInterface;
use App\Repositories\Post\PostInterface;
use App\Models\Group;
use App\Models\Member;
use App\Models\Post;
use Validator;
use Image;
use Storage;
use Carbon\Carbon;
use DB;
use Illuminate\Validation\Rule;



class GroupController extends Controller
{
    private $role=array(1,2 ,3);
    public function __construct(
        GroupInterface $groupInterface,
        MemberInterface $memberInterface,
        PostInterface $postInterface,
        RelationshipInterface $relationshipInterface)
    {
        $this->relationshipInterface = $relationshipInterface;
        $this->groupInterface = $groupInterface;
        $this->postInterface = $postInterface;
         $this->memberInterface = $memberInterface;
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'min:6', 'max:255'],
            'browse_post' => ['in: 0, 1'],
            'type' => ['in: 1, 2'],
            'regulations' => ['max:3000'],
            'intro' => ['max:3000'],
            'question' => ['max:3000'],
        ], [
            'name.required' => 'Khong duoc de trong ten',
            'name.min' => 'Ten group phai co it nhat 6 ki tu',
            'name.max' => 'Ten group khong duoc qua 255 ki tu',
            'browse_post.in' => 'Che do phe duyet khong hop le',
            'type.in' => 'Loai nhom khong hop le',
            'intro.max' => 'Vuot qua 3000 ki tu',
            'regulations.max' => 'Vuot qua 3000 ki tu',
        'question.max' => 'Vuot qua 3000 ki tu',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            'message' => $validator->errors()
            ]);
        }
        $options = [];
        $options['name'] = $request->name ?? '';
        $options['type'] = $request->type ?? config('group.type.public');
        $options['browse_post'] = $request->browse_post ?? config('group.browse_post.no');
        $options['regulations'] = $request->regulations ?? '';
        $options['intro'] = $request->intro ?? '';
        $options['question'] = $request->question ?? '';
        $options['cover'] = $request->cover ?? '';
            try {
            DB::beginTransaction();
            if ($result = $this->groupInterface->create($options)) {
                $options2 = [];
                $options2['user_id'] = Auth::user()->id;
                $options2['group_id'] = $result->id;
                $options2['status'] = config('member.status.member');
                $options2['role'] = config('member.role.admin');
                $options2['answer'] = '';
                $admin = $this->memberInterface->create($options2);
                if ($admin) {
                    DB::commit();
                    return response()->json([
                        'status' => 'success',
                        'message' => "Tao nhom thanh cong",
                        'data' => $result,
                    ]);
                } else {
                    DB::rollback();
                    return response()->json([
                        'status' => 'failed',
                        "message" => 'He thong da co loi xay ra, vui long thu lai',
                    ]);
                }

            } else {
                return response()->json([
                    'status' => 'failed',
                    "message" => 'He thong da co loi xay ra, vui long thu lai',
                ]);
            }

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                "message" => $e
            ]);
        }
    }
    public function getCountMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => ['required', 'exists:group,id'],
        ], [
            'group_id.required' => 'Vui long chon group',
            'group_id.exists' => 'Group khong ton tai',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            'message' => $validator->errors()
            ]);
        }
        $count = Member::where('group_id', $request->group_id)->where('status', config('member.status.member'))->count();
        if ($count !== null) {
            return response()->json([
                'status' => 'success',
                'data' => $count
                ]);
        } else
        return response()->json([
            'status' => 'failed',
            'message' => 'Đã có lỗi xảy ra, vui lòng thử lại'
            ]);
    }
    public function getCountPending(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => ['required', 'exists:group,id'],
        ], [
            'group_id.required' => 'Vui long chon group',
            'group_id.exists' => 'Group khong ton tai',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            'message' => $validator->errors()
            ]);
        }
        $count = Member::where('group_id', $request->group_id)->where('status', config('member.status.pending'))->count();
        if ($count !== null) {
            return response()->json([
                'status' => 'success',
                'data' => $count
                ]);
        } else
        return response()->json([
            'status' => 'failed',
            'message' => 'Đã có lỗi xảy ra, vui lòng thử lại'
            ]);
    }
    public function getCountPrevent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => ['required', 'exists:group,id'],
        ], [
            'group_id.required' => 'Vui long chon group',
            'group_id.exists' => 'Group khong ton tai',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            'message' => $validator->errors()
            ]);
        }
        $count = Member::where('group_id', $request->group_id)->where('status', config('member.status.prevent'))->count();
        if ($count !== null) {
            return response()->json([
                'status' => 'success',
                'data' => $count
                ]);
        } else
        return response()->json([
            'status' => 'failed',
            'message' => 'Đã có lỗi xảy ra, vui lòng thử lại'
            ]);
    }
    public function getListManager(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => ['required', 'exists:group,id'],
        ], [
            'group_id.required' => 'Vui long chon group',
            'group_id.exists' => 'Group khong ton tai',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            'message' => $validator->errors()
            ]);
        }
        $listPrevent = $this->relationshipInterface->getListPreventAndPrevented();
        $list = Member::where('group_id', $request->group_id)->whereNotIn('user_id', $listPrevent)->where('status', config('member.status.member'))->whereIn('role',[config('member.role.admin'), config('member.role.censor')])->with(['user', 'user.relationship1', 'user.relationship2'])->get();
        foreach ($list as $item) {
            if ($item->user->id != Auth::user()->id)
            $item['user']['count_mutual_friends'] = count($this->relationshipInterface->getMutualFriends($item->user->id));
        };
        if ($list !== null) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'list' => $list,
                    'count' => $list->count(),
                ],

                ]);
        } else
        return response()->json([
            'status' => 'failed',
            'message' => 'Đã có lỗi xảy ra, vui lòng thử lại'
            ]);
    }
    public function getListPending(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => ['required', 'exists:group,id'],
        ], [
            'group_id.required' => 'Vui long chon group',
            'group_id.exists' => 'Group khong ton tai',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            'message' => $validator->errors()
            ]);
        }
        $listPrevent = $this->relationshipInterface->getListPreventAndPrevented();
        $list = Member::where('group_id', $request->group_id)->whereNotIn('user_id', $listPrevent)->where('status', config('member.status.pending'))->with(['user', 'user.relationship1', 'user.relationship2'])->get();
        foreach ($list as $item) {
            if ($item->user->id != Auth::user()->id)
            $item['user']['count_mutual_friends'] = count($this->relationshipInterface->getMutualFriends($item->user->id));
        };
        if ($list !== null) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'list' => $list,
                    'count' => $list->count(),
                ],

                ]);
        } else
        return response()->json([
            'status' => 'failed',
            'message' => 'Đã có lỗi xảy ra, vui lòng thử lại'
            ]);
    }
    public function getListPrevent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => ['required', 'exists:group,id'],
        ], [
            'group_id.required' => 'Vui long chon group',
            'group_id.exists' => 'Group khong ton tai',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            'message' => $validator->errors()
            ]);
        }
        $listPrevent = $this->relationshipInterface->getListPreventAndPrevented();
        $list = Member::where('group_id', $request->group_id)->whereNotIn('user_id', $listPrevent)->where('status', config('member.status.prevent'))->with(['user', 'user.relationship1', 'user.relationship2'])->get();
        foreach ($list as $item) {
            if ($item->user->id != Auth::user()->id)
            $item['user']['count_mutual_friends'] = count($this->relationshipInterface->getMutualFriends($item->user->id));
        };
        if ($list !== null) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'list' => $list,
                    'count' => $list->count(),
                ],

                ]);
        } else
        return response()->json([
            'status' => 'failed',
            'message' => 'Đã có lỗi xảy ra, vui lòng thử lại'
            ]);
    }
    public function getListNomarl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => ['required', 'exists:group,id'],
        ], [
            'group_id.required' => 'Vui long chon group',
            'group_id.exists' => 'Group khong ton tai',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            'message' => $validator->errors()
            ]);
        }
        $listPrevent = $this->relationshipInterface->getListPreventAndPrevented();
        $list = Member::where('group_id', $request->group_id)->whereNotIn('user_id', $listPrevent)->where('status', config('member.status.member'))->where('role',config('member.role.nomarl'))->with(['user', 'user.relationship1', 'user.relationship2'])->get();
        foreach ($list as $item) {
            if ($item->user->id != Auth::user()->id)
            $item['user']['count_mutual_friends'] = count($this->relationshipInterface->getMutualFriends($item->user->id));
        };
        if ($list !== null) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'list' => $list,
                ],

                ]);
        } else
        return response()->json([
            'status' => 'failed',
            'message' => 'Đã có lỗi xảy ra, vui lòng thử lại'
            ]);
    }
    public function getInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => ['required', 'exists:group,id'],
        ], [
            'group_id.required' => 'Vui long chon group',
            'group_id.exists' => 'Group khong ton tai',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            'message' => $validator->errors()
            ]);
        }
        $group = Group::find($request->group_id);
        if ($group) {
            $member = Member::where('group_id', $request->group_id)->where('user_id', Auth::user()->id)->with(['user'])->first();
            if ($member) {
                if ($member->status === config('member.status.prevent')) {
                    return  response()->json([
                        'status' => 'failed',
                        "message" => 'Không tìm thấy group'
                    ]);
                } else {}
                return response()->json([
                    'status' => 'success',
                    "group" => $group,
                    'member' => $member
                ]);
            } else {
                return response()->json([
                    'status' => 'success',
                    "group" => $group
                ]);
            }
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'Không tìm thấy group'
            ]);
        }
    }
    public function getListGroupManager(Request $request)
    {
        $page = intval($request->page) ?? 1;
        $ids = Member::where('user_id', Auth::user()->id)
        ->where('status', config('member.status.member'))
        ->whereIn('role', [config('member.role.censor'), config('member.role.admin')])
        ->pluck('group_id');
        $list = Group::whereIn('id', $ids)->select('type', 'name', 'id','cover')->offset(($page - 1) * 3)->limit(3)->get();
        foreach($list as $item) {
            $item['count_member'] = Member::where('group_id', $item->id)->where('status', config('member.status.member'))->count();

        };
        return response()->json([
            'status' => 'success',
            "group" => $list
        ]);

    }
    public function getListGroup(Request $request)
    {
        $page = intval($request->page) ?? 1;
        $ids = Member::where('user_id', Auth::user()->id)
        ->where('status', config('member.status.member'))
        ->pluck('group_id');
        $list = Group::whereIn('id', $ids)->select('type', 'name', 'id','cover')->offset(($page - 1) * 3)->limit(3)->get();
        return response()->json([
            'status' => 'success',
            "group" => $list
        ]);

    }
    public function getListGroupNomarl(Request $request)
    {
        $page = intval($request->page) ?? 1;
        $ids = Member::where('user_id', Auth::user()->id)
        ->where('status', config('member.status.member'))
        ->where('role', config('member.role.nomarl'))
        ->pluck('group_id');
        $list = Group::whereIn('id', $ids)->select('type', 'name', 'id','cover')->offset(($page - 1) * 3)->limit(3)->get();
        foreach($list as $item) {
            $item['count_member'] = Member::where('group_id', $item->id)->where('status', config('member.status.member'))->count();

        };
        return response()->json([
            'status' => 'success',
            "group" => $list
        ]);

    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'exists:group,id'],
            'name' => ['min:6', 'max:255'],
            'browse_post' => ['in: 0, 1'],
            'type' => ['in: 2, 1'],
            'regulations' => ['max:3000'],
            'intro' => ['max:3000']
        ], [
            'id.required' => 'Vui long chon group',
            'id.exists' => 'Group khong ton tai',
            'name.min' => 'Ten group phai co it nhat 6 ki tu',
            'name.max' => 'Ten group khong duoc qua 255 ki tu',
            'browse_post.in' => 'Che do phe duyet khong hop le',
            'type.in' => 'Loai nhom khong hop le',
            'intro.max' => 'Vuot qua 3000 ki tu',
            'regulations.max' => 'Vuot qua 3000 ki tu'
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            'message' => $validator->errors()
            ]);
        }

        $user_id = Auth::user()->id;
        $member = Member::where('user_id', $user_id)->where('group_id', $request->id)->first();
        if (($member->role ?? null)  != config('member.role.admin')) {
            return [
                'status' => 'failed',
                'message' => 'Ban khong co quyen chinh sua'
            ];
        }
        $options = [];
        $options['name'] = $request->name ?? '';
        $options['type'] = $request->type ?? 1;
        $options['browse_post'] = $request->browse_post ?? 0;
        $options['regulations'] = $request->regulations ?? '';
        $options['question'] = $request->question ?? '';
        $options['cover'] = $request->cover ?? '';
        $options['intro'] = $request->intro ?? '';
        if ($result = $this->groupInterface->update($request->id, $options)) {
            if ($options['type'] == config('group.type.public')) {
                Member::where('group_id', $request->id)->where('status', config('member.status.pending'))->update(['stauts', config('member.status.member')]);
            }
            return response()->json([
                'status' => 'success',
                'message' => "Cap nhap nhom thanh cong",
                'data' => $result
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'He thong da co loi xay ra, vui long thu lai',
            ]);
        }

    }

    public function delete(Request $request) {
        $group_id = $request->id;
        if ($group = Group::find($group_id)) {
            $admin = Member::where('group_id', $group_id)->where('role', config('member.role.admin'))->orderBy('updated_at')->first();
            if (Auth::user()->id === $admin->user_id) {
                if ($result = $this->groupInterface->delete($group_id)) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Xoa group thanh cong'
                    ]);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Da co loi xay ra, vui long thu lai'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Ban khong co quyen xoa group'
                ]);
            }

        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'Group khong ton tai'
            ]);
        }
    }
    public function participation(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'exists:group,id'],
            'answer' => ['min:0', 'max:3000'],
        ], [
            'id.required' => 'Vui long chon group',
            'id.exists' => 'Group khong ton tai',
            'answer.min' => 'Cau tra loi khong duoc de trong',
            'answer.max' => 'Vuot qua 3000 ki tu'
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            'message' => $validator->errors()
            ]);
        }
        $user_id = Auth::user()->id;
        $group_id = $request->id ?? '';
        $member = Member::where('user_id', $user_id)->where('group_id', $group_id)->first();
        if (!$member) {
            $options = [];
            $options['user_id'] = $user_id;
            $options['group_id'] = $group_id;
            $options['role'] = config('member.role.nomarl');
            $options['answer'] = $request->answer ?? '';
            $group = Group::find($group_id);
            $options['status'] = $group->type == config('group.type.public') ? config('member.status.member') : config('member.status.pending');
            if ($result = $this->memberInterface->create($options)) {
                return response()->json([
                    'status' => 'success',
                    'message' => $group->type == config('group.type.public') ? 'Tham gia group thanh cong' : 'Yeu cau tham gia nhom da duoc gui, vui long cho duyet',
                    'data' => $result
                ]);
            } else {
                return response()->json([
                    'status' => 'failed',
                    "message" => 'He thong da co loi xay ra, vui long thu lai',
                ]);
            }
        } else {
            if ($member->status == config('member.status.member')) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Ban da la thanh vien cua group'
                ]);
            } else if ($member->status == config('member.status.pending')) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Ban da gui yeu cau truoc do roi'
                ]);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Group khong ton tai'
                ]);
            }
        }
    }
    public function outGroup(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'exists:group,id'],
        ], [
            'id.required' => 'Vui long chon group',
            'id.exists' => 'Group khong ton tai',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            'message' => $validator->errors()
            ]);
        }
        $user_id = Auth::user()->id;
        $group_id = $request->id ?? '';
        $member = Member::where('user_id', $user_id)->where('group_id', $group_id)->first();
        if ($member) {
            if ($member->role == config('member.role.admin')) {
                $count = Member::where('group_id', $group_id)->where('role', config('member.role.admin'))->count();
                if ($count > 1) {
                    if ($member->delete())
                    return response()->json([
                        'status' => 'success',
                        'data' => 'Rời nhóm thành công'
                    ]);
                } else
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Nếu bạn rời nhóm, nhóm sẽ không còn quản trị viên, vui lòng chọn quản trị viên trước khi rời nhóm hoặc xóa nhóm'
                ]);
            } else {
                if ($member->delete())
                return response()->json([
                    'status' => 'success',
                    'data' => 'Rời nhóm thành công'
                ]);
            }
        }
        return response()->json([
            'status' => 'failed',
            'message' => 'Yêu cầu thất bại, vui lòng thử lại'
        ]);
    }
    public function browserMember(Request $request) {
        $validator = Validator::make($request->all(), [
            'group_id' => ['required', 'exists:group,id'],
            'member_id' => ['required', 'exists:users,id'],
        ], [
            'group_id.required' => 'Vui long chon group',
            'group_id.exists' => 'Group khong ton tai',
            'member_id.required' => 'Vui long chon group',
            'member_id.exists' => 'Tai khoan khong ton tai',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            'message' => $validator->errors()
            ]);
        }
        $group_id = $request->group_id;
        $member_id = $request->member_id;
        $admin = Auth::user()->id;
        $role_admin = Member::where('group_id', $group_id)->where('user_id', $admin)->first()->role ?? null;
        if ($role_admin) {
            if (!in_array($role_admin, [config('member.role.admin')])) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Ban khong co quyen duyet thanh vien'
                ]);
            } else {
                $member = Member::where('group_id', $group_id)->where('user_id', $member_id)->first();
                if (!$member) {
                    return response()->json([
                        'status' => 'failed',
                        "message" => 'Tai khoan nay khong yeu cau tham gia nhom'
                    ]);
                } else if ($member->status == config('member.status.member')) {
                    return response()->json([
                        'status' => 'failed',
                        "message" => 'Tai khoan nay da la thanh vien cua nhom'
                    ]);
                } else if ($member->status == config('member.status.prevent')) {
                    return response()->json([
                        'status' => 'failed',
                        "message" => 'Tai khoan nay dang bi chan'
                    ]);
                } else {
                    $options = [];
                    $options['status'] = config('member.status.member');
                    if ($result = $this->memberInterface->update($member->id, $options)) {
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Duyet thanh cong',
                            'data' => $result
                        ]);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            "message" => 'He thong da co loi xay ra, vui long thu lai',
                        ]);
                    }
                }
            }
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'Ban khong phai la thanh vien cua nhom'
            ]);
        }

    }
    public function browserPost(Request $request) {
        $validator = Validator::make($request->all(), [
            'post_id' => ['required', 'exists:post,id'],
        ], [
            'post_id.required' => 'Vui long chon group',
            'post_id.exists' => 'Tai khoan khong ton tai',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            'message' => $validator->errors()
            ]);
        }
        $post_id = $request->post_id;
        $admin = Auth::user()->id;
        $post = Post::where('id', $post_id)->whereNotNull('group_id')->first();
        if (!$post) {
            return response()->json([
                'status' => 'failed',
                "message" => 'Bài viết không tồn tại'
        ]);
    }
        $role_admin = Member::where('group_id', $post->group_id)->where('user_id', $admin)->first()->role ?? null;
        if ($role_admin) {
            if (!in_array($role_admin, [config('member.role.admin'), config('member.role.censor')])) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Ban khong co quyen duyet bài viết'
                ]);
            } else {
              if ($post->user_id_browse) {
                    return response()->json([
                        'status' => 'failed',
                        "message" => 'Bài viết đã được duyệt'
                    ]);
                } else {
                    $options = [];
                    $options['user_id_browse'] = Auth::user()->id;
                    if ($result = $this->postInterface->update($post->id, $options)) {
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Duyet thanh cong',
                            'data' => $result
                        ]);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            "message" => 'He thong da co loi xay ra, vui long thu lai',
                        ]);
                    }
                }
            }
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'Ban khong phai la thanh vien cua nhom'
            ]);
        }

    }
    public function cancelPost(Request $request) {
        $validator = Validator::make($request->all(), [
            'post_id' => ['required', 'exists:post,id'],
        ], [
            'post_id.required' => 'Vui long chon group',
            'post_id.exists' => 'Tai khoan khong ton tai',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            'message' => $validator->errors()
            ]);
        }
        $post_id = $request->post_id;
        $admin = Auth::user()->id;
        $post = Post::where('id', $post_id)->whereNotNull('group_id')->first();
        if (!$post) {
            return response()->json([
                'status' => 'failed',
                "message" => 'Bài viết không tồn tại'
        ]);
    }
        $role_admin = Member::where('group_id', $post->group_id)->where('user_id', $admin)->first()->role ?? null;
        if ($role_admin) {
            if (!in_array($role_admin, [config('member.role.admin'), config('member.role.censor')])) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Ban khong co quyen duyet bài viết'
                ]);
            } else {
              if ($post->user_id_browse) {
                    return response()->json([
                        'status' => 'failed',
                        "message" => 'Bài viết đã được duyệt'
                    ]);
                } else {
                    $options = [];
                    $options['user_id_browse'] = Auth::user()->id;
                    if ($result = $post->delete()) {
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Xóa thanh cong',
                            'data' => $result
                        ]);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            "message" => 'He thong da co loi xay ra, vui long thu lai',
                        ]);
                    }
                }
            }
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'Ban khong phai la thanh vien cua nhom'
            ]);
        }

    }
    public function kickMember(Request $request) {
        $validator = Validator::make($request->all(), [
            'group_id' => ['required', 'exists:group,id'],
            'member_id' => ['required', 'exists:users,id'],
        ], [
            'group_id.required' => 'Vui long chon group',
            'group_id.exists' => 'Group khong ton tai',
            'member_id.required' => 'Vui long chon group',
            'member_id.exists' => 'Tai khoan khong ton tai',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            'message' => $validator->errors()
            ]);
        }
        $group_id = $request->group_id;
        $member_id = $request->member_id;
        $admin = Auth::user()->id;
        $member_admin = Member::where('group_id', $group_id)->where('user_id', $admin)->first();
        $role_admin = $member_admin->role ?? null;
        if ($role_admin) {
            if (!in_array($role_admin, [config('member.role.admin'), config('member.role.censor')])) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Ban khong co quyen kick thanh vien'
                ]);
            } else {
                $member = Member::where('group_id', $group_id)->where('user_id', $member_id)->first();
                if (!$member) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Tai khoan nay khong thuoc group'
                    ]);
                } else {
                    if ($member->status == config('member.status.prevent')) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'Tai khoan nay hien dang bi chan'
                        ]);
                    } else {
                        if ($role_admin < $member->role) {
                            if ($member->delete()) {
                                return response()->json([
                                    'status' => 'success',
                                    "message" => 'Kich tai khoan thanh cong'
                                ]);
                            } else {
                                return response()->json([
                                    'status' => 'failed',
                                    "message" => 'He thong da co loi, vui long thu lai'
                                ]);
                            }
                        } else if ($role_admin > $member->role) {
                            return response()->json([
                                'status' => 'failed',
                                "message" => 'Ban khong co quyen kich tai khoan nay'
                            ]);
                        } else {
                            $day_updated_member = new Carbon($member->updated_at);
                            $day_updated_admin = new Carbon($member_admin->updated_at);
                            if ($day_updated_admin->lessThan($day_updated_member)) {
                                if ($member->delete()) {
                                    return response()->json([
                                        'status' => 'success',
                                        "message" => 'Kich tai khoan thanh cong'
                                    ]);
                                } else {
                                    return response()->json([
                                        'status' => 'failed',
                                        "message" => 'He thong da co loi, vui long thu lai'
                                    ]);
                                }
                            } else {
                                return response()->json([
                                    'status' => 'failed',
                                    "message" => 'Ban khong co quyen kich tai khoan nay'
                                ]);
                            }
                        }
                    }
                }

            }
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'Ban khong phai la thanh vien cua nhom'
            ]);
        }

    }

    public function preventMember(Request $request) {
        $validator = Validator::make($request->all(), [
            'group_id' => ['required', 'exists:group,id'],
            'member_id' => ['required', 'exists:users,id'],
        ], [
            'group_id.required' => 'Vui long chon group',
            'group_id.exists' => 'Group khong ton tai',
            'member_id.required' => 'Vui long chon group',
            'member_id.exists' => 'Tai khoan khong ton tai',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            'message' => $validator->errors()
            ]);
        }
        $group_id = $request->group_id;
        $member_id = $request->member_id;
        $admin = Auth::user()->id;
        $member_admin = Member::where('group_id', $group_id)->where('user_id', $admin)->first();
        $role_admin = $member_admin->role ?? null;
        if ($role_admin) {
            if (!in_array($role_admin, [config('member.role.admin'), config('member.role.censor')])) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Ban khong co quyen chan thanh vien'
                ]);
            } else {
                $member = Member::where('group_id', $group_id)->where('user_id', $member_id)->first();
                if (!$member) {
                    $options = [];
                    $options['user_id'] = $member_id;
                    $options['group_id'] = $group_id;
                    $options['status'] = config('member.status.prevent');
                    if ($result = $this->memberInterface->create($options)) {
                        return response()->json([
                            'status' => 'success',
                            "message" => 'Chan tai khoan thanh cong'
                        ]);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            "message" => 'He thong da co loi, vui long thu lai'
                        ]);
                    }
                } else {
                    if ($member->status == config('member.status.prevent')) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'Tai khoan da bi chan truoc do roi'
                        ]);
                    } else {
                        if ($role_admin < $member->role) {
                            $options = [];
                            $options['status'] = config('member.status.prevent');
                            if ($result = $this->memberInterface->update($member->id, $options)) {
                                return response()->json([
                                    'status' => 'success',
                                    "message" => 'Chan tai khoan thanh cong'
                                ]);
                            } else {
                                return response()->json([
                                    'status' => 'failed',
                                    "message" => 'He thong da co loi, vui long thu lai'
                                ]);
                            }
                        } else if ($role_admin > $member->role) {
                            return response()->json([
                                'status' => 'failed',
                                "message" => 'Ban khong co quyen chan tai khoan nay'
                            ]);
                        } else {
                            $day_updated_member = new Carbon($member->updated_at);
                            $day_updated_admin = new Carbon($member_admin->updated_at);
                            if ($day_updated_admin->lessThan($day_updated_member)) {
                                $options = [];
                                $options['status'] = config('member.status.prevent');
                                if ($result = $this->memberInterface->update($member->id, $options)) {
                                    return response()->json([
                                        'status' => 'success',
                                        "message" => 'Chan tai khoan thanh cong'
                                    ]);
                                } else {
                                    return response()->json([
                                        'status' => 'failed',
                                        "message" => 'He thong da co loi, vui long thu lai'
                                    ]);
                                }
                            } else {
                                return response()->json([
                                    'status' => 'failed',
                                    "message" => 'Ban khong co quyen chan tai khoan nay'
                                ]);
                            }
                        }
                    }
                }

            }
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'Ban khong phai la thanh vien cua nhom'
            ]);
        }

    }


    public function cancelPreventMember(Request $request) {
        $validator = Validator::make($request->all(), [
            'group_id' => ['required', 'exists:group,id'],
            'member_id' => ['required', 'exists:users,id'],
        ], [
            'group_id.required' => 'Vui long chon group',
            'group_id.exists' => 'Group khong ton tai',
            'member_id.required' => 'Vui long chon group',
            'member_id.exists' => 'Tai khoan khong ton tai',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            'message' => $validator->errors()
            ]);
        }
        $group_id = $request->group_id;
        $member_id = $request->member_id;
        $admin = Auth::user()->id;
        $member_admin = Member::where('group_id', $group_id)->where('user_id', $admin)->first();
        $role_admin = $member_admin->role ?? null;
        if ($role_admin) {
            if (!in_array($role_admin, [config('member.role.admin'), config('member.role.censor')])) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Ban khong co quyen bo chan'
                ]);
            } else {
                $member = Member::where('group_id', $group_id)->where('user_id', $member_id)->first();
                if (!$member) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Tai khoan khong thuoc danh sach chan'
                    ]);
                } else {
                    if ($member->status == config('member.status.prevent')) {
                        if ($member->delete()) {
                            return response()->json([
                                'status' => 'success',
                                "message" => 'Bo chan thanh cong'
                            ]);
                        } else {
                            return response()->json([
                                'status' => 'failed',
                                "message" => 'He thong da co loi, vui long thu lai'
                            ]);
                        }
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'Tai khoan nay khong bi chan truoc do'
                        ]);
                    }
                }

            }
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'Ban khong phai la thanh vien cua nhom'
            ]);
        }

    }
    public function assignPermission(Request $request) {
        $validator = Validator::make($request->all(), [
            'group_id' => ['required', 'exists:group,id'],
            'member_id' => ['required', 'exists:users,id'],
            'role' => ['required', Rule::in($this->role)]
        ], [
            'group_id.required' => 'Vui long chon group',
            'group_id.exists' => 'Group khong ton tai',
            'member_id.required' => 'Vui long chon group',
            'member_id.exists' => 'Tai khoan khong ton tai',
            'role.required' => 'Quyen khong hop le',
            'role.in' => 'Quyen khong hop le',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            'message' => $validator->errors()
            ]);
        }
        $group_id = $request->group_id;
        $member_id = $request->member_id;
        $admin = Auth::user()->id;
        $member_admin = Member::where('group_id', $group_id)->where('user_id', $admin)->first();
        $role_admin = $member_admin->role ?? null;
        if ($role_admin) {
            if ($role_admin != config('member.role.admin')) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Ban khong co quyen de thuc hien thao tac'
                ]);
            } else {
                $member = Member::where('group_id', $group_id)->where('user_id', $member_id)->first();
                if (!$member) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Tai khoan nay khong thuoc group'
                    ]);
                } else {
                    if ($member->status == config('member.status.prevent')) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'Tai khoan nay hien dang bi chan'
                        ]);
                    } else {
                        $day_updated_member = new Carbon($member->updated_at);
                        $day_updated_admin = new Carbon($member_admin->updated_at);
                        if ($member->role == config('member.role.admin') && $day_updated_member->lessThan($day_updated_admin)) {
                            return response()->json([
                                'status' => 'failed',
                                "message" => 'Ban khong co quyen thay doi quyen cua nguoi nay'
                            ]);
                        } else {
                            $options = [];
                            $options['status'] = config('member.status.member');
                            $options['role'] = $request->role ?? null;
                            if ($this->memberInterface->update($member->id, $options)) {
                                return response()->json([
                                    'status' => 'success',
                                    "message" => 'Gan quyen thanh cong',
                                ]);
                            } else {
                                return response()->json([
                                    'status' => 'failed',
                                    "message" => 'Da co loi xay ra vui long thu lai'
                                ]);
                            }
                        }

                    }
                }

            }
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'Ban khong phai la thanh vien cua nhom'
            ]);
        }
    }
}