<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Repositories\Post\PostInterface;
use App\Repositories\Comment\CommentInterface;
use App\Models\Post;
use App\Models\Member;
use App\Models\Group;
use Validator;
use Image;
use Storage;


class CommentController extends Controller
{
    public function __construct(PostInterface $postInterface, CommentInterface $commentInterface)
    {
        $this->commentInterface = $commentInterface;
        $this->postInterface = $postInterface;
    }
    public function getComment(Request $request) {
        $validator = Validator::make($request->all(), [
            'post_id' => ['exists:post,id'],
            'comment_id' => ['exists:comment,id'],
        ], [
            'post_id.exists' => 'Bai viet không ton tai',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            "message" => json_decode($validator->errors())
            ]);
        }
        $options = [];
        $options['post_id'] = $request->post_id ?? null;
        $options['page'] = intval($request->page) ?? 1;
        $options['comment_id'] = intval($request->comment_id) ?? null;
        if ($result = $this->commentInterface->getComment($options)) {
            return response()->json([
                'status' => 'success',
                "data" => $result
                ]);
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'Đã có lỗi xảy ra, vui lòng thử lại'
                ]);
        }
    }
    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'post_id' => ['exists:post,id'],
            'comment_id' => ['exists:comment,id']
        ], [
            'post_id.exists' => 'Bai viet không ton tai',
            'comment.exists' => 'Bình luận không ton tai',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status' => 'failed',
            "message" => json_decode($validator->errors())
            ]);
        }
        $options = [];
        $options['user_id'] = Auth::user()->id;

        $options['post_id'] = $request->post_id ?? null;
        $options['comment_id'] = $request->comment_id ?? null;
        if (!($options['post_id'] || $options['comment_id'])) {
            return response()->json([
                'status' => 'failed',
                "message" => "Vui lòng chọn nơi để bình luận"
                ]);
        }
        $options['level'] = config('post.level.post');
        $options['data'] = $request->data ?? '';
        if ((!$request->images) && (!$options['data'])) {
            return response()->json([
                'status' => 'failed',
                "message" => 'Noi dung khong duoc trong'
            ]);
        }
        $options['src_images'] = json_encode($request->images ?? '');
        $post = $this->postInterface->getListPost(["post_id" => $options['post_id']]);
        if ($post) {
            if ($result = $this->commentInterface->create($options)) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Bình luận thành công",
                    'data' => $result
                ]);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        "message" => 'He thong da co loi xay ra, vui long thu lai',
                    ]);
                }
        } else {
            return response()->json([
                'status' => 'failed',
                "message" => 'Bài viết không tồn tại'
            ]);
        }
        // $images = $request->images ?? null;

    }

}
