<?php

namespace App\Http\Controllers\api;

use App\Events\PostEvent;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Repositories\Post\PostInterface;
use App\Models\Post;
use App\Models\Member;
use App\Models\Group;
use App\Models\Notification;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Illuminate\Support\Facades\Validator;
use Image;
use Storage;


class PostController extends Controller
{

    public function __construct(PostInterface $postInterface)
    {
        $this->postInterface = $postInterface;
    }

    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => ['required', 'image'],
        ], [
            'image.required' => 'Ảnh không được trống',
            'image.image' => 'Ảnh không hợp lệ',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                "message " => json_decode($validator->errors())
            ]);
        }
        $image = $request->file('image');
        $folderName = 'file_upload';
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        if ($image->move(public_path($folderName), $imageName))
            return response()->json([
                'status' => 'success',
                "data" => $imageName
            ]);
        else
            return response()->json([
                'status' => 'failed',
                "message " => 'Đã có lỗi xảy ra, vui lòng thử lại'
            ]);
    }



    public function uploadFile(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            ['file' => ['required', 'file']],
            [
                'file.required' => 'file không được trống',
                'file.file' => 'file không hợp lệ '
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'status' => 'falied',
                'message' => json_decode($validator->errors())
            ]);
        }
        $file = $request->file('file');
        $forderName = 'file_upload';
        $getFileName = $file->getClientOriginalName();
        $newFile = $getFileName . time() . '.' . $file->getClientOriginalExtension();
        if ($file->move(public_path($forderName), $newFile))
            return response()->json([
                'status' => 'success',
                'data' => $newFile
            ]);
        else
            return response()->json([
                'status' => 'failed',
                "message " => 'Đã có lỗi xảy ra, vui lòng thử lại'
            ]);
    }



    public function create(Request $request)
    {

        $options = [];
        $options['user_id'] = Auth::user()->id;
        $options['user_id_2'] = $request->user_id_2 ?? null;
        $options['group_id'] = $request->group_id ?? null;
        $options['post_id'] = $request->post_id ?? null;
        $options['data'] = $request->data ?? '';
        if ((!$request->images) && (!$options['data']) && (!$options['post_id'])) {
            return response()->json([
                'status' => 'failed',
                "message" => 'Nội dung không được trống'
            ]);
        }
        $options['user_id_browse'] = 0;
        $options['src_images'] = json_encode($request->images ?? '');

        if ($options['group_id']) {
            $group = Group::where('id', $options['group_id'])->first();
            if ($group['type'] == config('group.type.private')) {
                $options['user_id_browse'] = null;
            }
        }

        if ($result = $this->postInterface->create($options)) {
            event(
                $e = new PostEvent($result)
            );
            if ($request->user_id_2 ?? null) {
                $noti = new Notification();

                $noti->user_id_1 = $request->user_id_2;
                $noti->user_id_2 =  Auth::user()->id;
                $noti->type = 4;
                $noti->seen = 0;
                $noti->post_id = 9999;
                $noti->save();
            }
            return response()->json([
                'status' => 'success',
                'message' => "Tao bai viet thanh cong",
                'data' => Post::where('id', $result->id)->with('user', 'user_2', 'isLike', 'post_share', 'post_share.user', 'post_share.user_2')->get()

            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'He thong da co loi xay ra, vui long thu lai',
            ]);
        }
    }



    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => ['required', 'exists:post,id'],
        ], [
            'post_id.required' => 'Vui lòng chọn bài viết',
            'post_id.exists' => 'Bài viết không tồn tại',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                "message " => json_decode($validator->errors())
            ]);
        }

        $post = $this->postInterface->getListPost(['post_id' => $request->post_id]);
        return $post;
    }
    public function delete(Request $request)
    {
        $post = Post::find($request->post_id);
        if (($post->user_id) != Auth::user()->id) {
            return response()->json([
                'status' => 'failed',
                "message " => 'Bạn không có quyền xoá bài viết'
            ]);
        } else {

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
    public function getListPostBrowse(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'group_id' => ['exists:group,id'],
        ], [
            'group_id.exists' => 'Group khong ton tai',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                "message " => json_decode($validator->errors())
            ]);
        }
        $options = [];
        $options['group_id'] = $request->group_id ?? null;
        $options['page'] = intval($request->page) ?? 1;

        $user_id = Auth::user()->id;

        $level = User::where('id', $user_id)->first()->level;

        if (!in_array($level, [4, 5])) {
            if ($options['group_id']) {
                $group_type = Group::find($options['group_id'])->type;
                if ($group_type == 2) {
                    $member = Member::where('user_id', Auth::user()->id)->where('group_id', $options['group_id'])->first();
                    if (!$member) {
                        return response()->json([
                            'status' => 'failed',
                            "message" => 'Bạn không là thành viên của group',
                        ]);
                    }
                    if ($member->role == config('member.role.nomarl')) {
                        return response()->json([
                            'status' => 'failed',
                            "message" => 'Bạn không phải là admin',
                        ]);
                    }
                }
            }
        }

        if ($result = $this->postInterface->getListPostBrowse($options)) {
            return response()->json([
                'status' => 'success',
                'data' => $result
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'He thong da co loi xay ra, vui long thu lai',
            ]);
        }
    }




    public function getList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['exists:users,id'],
            'group_id' => ['exists:group,id'],
        ], [
            'user_id.exists' => 'Tai khoan khong ton tai',
            'group_id.exists' => 'Group khong ton tai',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                "message " => json_decode($validator->errors())
            ]);
        }
        $options = [];
        $options['get_image'] = $request->get_image ?? null;
        $options['user_id'] = $request->user_id ?? null;
        $options['group_id'] = $request->group_id ?? null;
        $options['page'] = intval($request->page) ?? 1;

        $user_id = Auth::user()->id;
        if ($options['group_id']) {
            $group_type = Group::find($options['group_id'])->type;
            if ($group_type == 2) {
                $member = Member::where('user_id', Auth::user()->id)->where('group_id', $options['group_id'])->first();
                if (!$member) {
                    return response()->json([
                        'status' => 'failed',
                        "message" => 'Bạn không là thành viên của group',
                    ]);
                }
            }
        }

        if ($result = $this->postInterface->getListPost($options)) {
            return response()->json([
                'status' => 'success',
                'data' => $result
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'He thong da co loi xay ra, vui long thu lai',
            ]);
        }
    }


    public function getCountPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['exists:users,id'],
            'group_id' => ['exists:group,id'],
        ], [
            'user_id.exists' => 'Tai khoan khong ton tai',
            'group_id.exists' => 'Group khong ton tai',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                "message " => json_decode($validator->errors())
            ]);
        }
        $options = [];
        $options['user_id'] = $request->user_id ?? null;
        $options['group_id'] = $request->group_id ?? null;


        $result = $this->postInterface->getCountPost($options);
        if ($result !== null) {
            return response()->json([
                'status' => 'success',
                'data' => $result
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'He thong da co loi xay ra, vui long thu lai',
            ]);
        }
    }
    public function getListShare(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => ['required', 'exists:post,id'],
        ], [
            'post_id.required' => 'Vui lòng chọn bài viết',
            'post_id.exists' => 'Bài viết không tồn tại',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                "message " => json_decode($validator->errors())
            ]);
        }
        if ($result = $this->postInterface->getListSharePost($request->post_id, $request->page ?? 1)) {
            return  response()->json([
                'status' => 'success',
                'data' => $result
            ]);
        } else  return response()->json([
            'status' => 'failed',
            'message' => 'Đã có lỗi xảy ra, vui lòng thử lại'
        ]);
    }


    public function getListSearchAdmin(Request $request)
    {

        $options = [];
        $options['data'] = $request->keySearch ?? null;
        $options['group_id'] = $request->group_id ?? null;

        $list = $this->postInterface->getListSearchAdmin($options);

        if ($list) return response()->json([
            'status' => 'success',
            'data' => $list
        ]);
        else return response()->json([
            'status' => 'falied',
            'data' => 'da co loi'
        ]);
    }



    public function getListSearch(Request $request)
    {
        $options = [];
        $options['group_id'] = $request->group_id ?? null;
        $options['keySearch'] = $request->keySearch ?? null;
        $options['page'] = intval($request->page) ?? 1;



        if ($result = $this->postInterface->getListPostSearch($options)) {
            return response()->json([
                'status' => 'success',
                'data' => $result
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => $options['keySearch'],
            ]);
        }
    }


    function deletePostAdmin(Request $request)
    {
        $post = Post::find($request->id);
        $post->delete();
        return response()->json([
            'status' => 'success',
            'data' => 'xoa thanh cong'
        ]);
    }


    public function getListPostAdmin(Request $request)
    {
        $page = $request->page ?? 1;

        if ($result = $this->postInterface->getListPostAdmin($page)) {
            return response()->json([
                'status' => 'success',
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
