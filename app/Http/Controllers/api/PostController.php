<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Repositories\Post\PostInterface;
use App\Models\Post;
use App\Models\Member;
use App\Models\Group;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Illuminate\Support\Facades\Validator;
use Image;
use Storage;


class PostController extends Controller
{
    private $type_post = array(1, 2, 3, 4, 5, 6);
    private $type_show = array(1, 2, 3, 4, 5);
    private $level = array(0, 1, 2);
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
        $folderName = 'tmp_images';
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
        $forderName = 'documents';
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
        // hieenj tao chua co truong hop kiem tra, tag nguoi khong thuoc group, user_id_2, nguoi tag, dang tunog , group phai co mqh
        $validator = Validator::make($request->all(), [
            'user_id_2' => ['exists:users,id'],
            'group_id' => ['exists:group,id'],
            'post_id' => ['exists:post,id'],
            'type_post' => [Rule::in($this->type_post)],
            'type_show' => [Rule::in($this->type_show)],
            'images' => ['array'],
            'user_id_tags' => ['array'],
            'user_id_tags.*' => ['exists:users,id'],
            'user_view_posts.*' => ['exists:users,id'],
            'user_view_posts' => ['array'],
        ], [
            'user_id_2.exists' => 'Tai khoan khong ton tai',
            'group_id.exists' => 'Group khong ton tai',
            'post_id.exists' => 'Bai viet không ton tai',
            'type_show.in_array' => 'Che do hien thi khong ton tai',
            'type_post.in_array' => 'Che do bai viet khong ton tai',
            'image.array' => 'Thong tin anh khong chinh xac',
            'user_id_tag.array' => 'Danh sach tag khong hop le',
            'user_id_tag.*.exists' => 'Danh sach tag khong hop le',
            'user_view_posts.array' => ($request->type_show == config('post.type_show.specific_friend')) ? 'Danh sách người xem không hợp lệ' : 'Danh sách người cấm xem không hợp lệ',
            'user_view_posts.*.exists' => ($request->type_show == config('post.type_show.specific_friend')) ? 'Danh sách người xem không hợp lệ' : 'Danh sách người cấm xem không hợp lệ',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                "message " => json_decode($validator->errors())
            ]);
        }
        $options = [];
        $options['user_id'] = Auth::user()->id ?? 3;
        $options['user_id_2'] = $request->user_id_2 ?? null;
        $options['group_id'] = $request->group_id ?? null;

        $options['post_id'] = $request->post_id ?? null;
        $options['type_post'] = $request->type_post ?? config('post.type_post.nomarl');
        $options['type_show'] = $request->type_show ?? config('post.type_show.public');
        $options['level'] = config('post.level.post');
        $options['data'] = $request->data ?? '';
        if ((!$request->images) && (!$options['data']) && (!$options['post_id'])) {
            return response()->json([
                'status' => 'failed',
                "message" => 'Noi dung khong duoc trong'
            ]);
        }
        $options['user_id_tags'] = json_encode($request->user_id_tags ?? '');
        $options['user_view_posts'] = json_encode($request->user_view_posts ?? '');
        $options['user_id_browse'] = 0;
        $options['src_images'] = json_encode($request->images ?? '');

        if ($options['group_id']) {
            if ($options['user_id_2']) {
                return response()->json([
                    'status' => 'failed',
                    "message" => 'Khong the dang 1 bai viet tai nhieu noi'
                ]);
            }
            $member = Member::where('user_id', $options['user_id'])->where('group_id', $options['group_id'])->with("group")->first();
            if (!$member) {
                return response()->json([
                    'status' => 'failed',
                    "message" => 'Bạn không là thành viên của group',
                ]);
            } else if ($options['type_post'] != config('post.type_post.nomarl') && $member->role != config('member.role.admin')) {
                return response()->json([
                    'status' => 'failed',
                    "message" => 'Khong the dang bai viet nay tai group'
                ]);
            } else if ($member['group']['type'] == config('group.type.private')) {
                $options['user_id_browse'] = null;
            }
        }

        // $images = $request->images ?? null;
        if ($result = $this->postInterface->create($options)) {
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
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required'],
            'type_post' => ['required', 'in_array:array(1, 2, 3, 4, 5)'],
            'type_show' => ['required', 'in_array:array(1, 2, 3, 4, 5)'],
            'images' => ['array'],
            'user_id_tags' => ['array'],
            'user_id_tags.*' => ['exists:users,id'],
        ], [
            'id.required' => 'Chưa chọn bài viết cần sửa',
            'type_show.in_array' => 'Che do hien thi khong ton tai',
            'type_post.in_array' => 'Che do bai viet khong ton tai',
            'image.array' => 'Thong tin anh khong chinh xac',
            'user_id_tag.array' => 'Danh sach tag khong hop le',
            'user_id_tag.*.exists' => 'Danh sach tag khong hop le',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                "message " => json_decode($validator->errors())
            ]);
        }
        if ((Post::find($request->id)->user_id ?? null) != Auth::user()->id) {
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    "message " => 'Bạn không có quyền chỉnh sửa bài viết'
                ]);
            }
        }
        $options = [];
        $id = $request->id;
        $options['user_id'] = Auth::user()->id;
        $options['type_post'] = $request->type_post;
        $options['type_show'] = $request->type_show;
        $options['data'] = $request->data ?? null;
        $options['user_id_tags'] = $request->user_id_tags ?? null;
        $options['src_images'] = json_encode($request->images ?? null);
        if ($result = $this->postInterface->update($id, $options)) {
            return response()->json([
                'status' => 'success',
                'message' => "Sửa thành công",
                'data' => $result
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'He thong da co loi xay ra, vui long thu lai',
            ]);
        }
    }
    public function getListPostBrowse(Request $request)
    {
        // return json_encode(['12'=> '14']);
        // hieenj tao chua co truong hop kiem tra, tag nguoi khong thuoc group, user_id_2, nguoi tag, dang tunog , group phai co mqh
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
        // return json_encode(['12'=> '14']);
        // hieenj tao chua co truong hop kiem tra, tag nguoi khong thuoc group, user_id_2, nguoi tag, dang tunog , group phai co mqh
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
        // hieenj tao chua co truong hop kiem tra, tag nguoi khong thuoc group, user_id_2, nguoi tag, dang tunog , group phai co mqh
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

        $user_id = Auth::user()->id;



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


    public function searchPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => ['max:30'],

        ], [
            'user_name.max' => 'Tên không hợp lệ',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'errors' => $validator->errors()
            ]);
        }

        $data = $request->data ?? null;
        if ($data) {
            $list = $this->postInterface->searchpost($data);
        }
        if ($list) return response()->json([
            'status' => 'success',
            'data' => $list
        ]);
        else return response()->json([
            'status' => 'falied',
            'data' => 'da co loi'
        ]);
    }

    public function getListPostAdmin(Request $request)
    {
        // return json_encode(['12'=> '14']);
        // hieenj tao chua co truong hop kiem tra, tag nguoi khong thuoc group, user_id_2, nguoi tag, dang tunog , group phai co mqh
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
        if ($result = $this->postInterface->getListPostAdmin($options)) {
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

    function deletePostAdmin(Request $request)
    {
        $post = Post::find($request->id);
        $post->delete();
        return response()->json([
            'status' => 'success',
            'data' => 'xoa thanh cong'
        ]);
    }
}
