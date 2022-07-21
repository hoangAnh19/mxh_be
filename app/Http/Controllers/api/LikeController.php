<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\Repositories\Like\LikeInterface;
use Illuminate\Http\Request;
use App\Models\Like;
use Illuminate\Support\Facades\Validator;

class LikeController extends Controller
{
    public function __construct(LikeInterface $like)
    {
        $this->likeInterface = $like;
    }
    public function like(Request $request)
    {
        $type = array(0, 1, 2, 3, 4, 5, 6);
        $validator = Validator::make($request->all(), [
            'post_id' => ['required', 'exists:post,id'],
            'type'  =>  [Rule::in($type)],
        ], [
            'post_id.required' => 'Vui lòng chọn bài viết',
            'post_id.exists' => 'Bài viết không tồn tại',
            'type.in_array' => 'Chế độ không tồn tại',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                "message " => json_decode($validator->errors())
            ]);
        }
        if (Like::updateOrCreate(
            [
                'post_id' => $request->post_id,
                'user_id' => Auth::user()->id,
            ],
            [
                'type' => $request->type,
            ]
        )) {
            return response()->json([
                'status' => 'success',
            ]);;
        } else {
            response()->json([
                'status' => 'failed',
                'message' => 'Đã có lỗi xảy ra, vui lòng thử lại'
            ]);
        }
    }
    public function getListByPost(Request $request)
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
        if ($result = $this->likeInterface->getListLikePost($request->post_id, $request->page ?? 1)) {
            return  response()->json([
                'status' => 'success',
                'data' => $result
            ]);
        } else  return response()->json([
            'status' => 'failed',
            'message' => 'Đã có lỗi xảy ra, vui lòng thử lại'
        ]);
    }
}
