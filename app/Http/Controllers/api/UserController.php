<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Repositories\User\UserInterface;
use App\Repositories\Relationship\RelationshipInterface;
use App\Repositories\Chat\ChatInterface;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Validator;
use Image;
use Storage;
use Hash;
use App\Events\OnlineEvent;
use App\Models\Relationship;

class UserController extends Controller
{
    public function __construct(UserInterface $userInterface, RelationshipInterface $relationshipInterface, ChatInterface $chatInterface)
    {
        $this->userInterface = $userInterface;
        $this->relationshipInterface  = $relationshipInterface;
        $this->chatInterface  = $chatInterface;
    }
    public function online(Request $request)
    {
        $user_id = Auth::id();
        $ids_1 = $this->relationshipInterface->getListFriend($user_id, 0 ,0);
        $ids_2 = $this->chatInterface->getListIdChat($user_id);
        $ids = $ids_1->merge($ids_2)->unique();
        event(
            $e = new OnlineEvent([
                'list' => $ids,
                'user' => $user_id,
                'client_id' => $request->client_id,
            ])
        );
        return;
    }


    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email_or_phone' => ['regex:/(0?)(3[2-9]|5[6|8|9]|7[0|6-9]|8[0-6|8|9]|9[0-4|6-9])[0-9]{7}|^.+@.+$/i', 'required'],
            'password' => ['required', 'min:6', 'max:30'],
        ], [
            'email_or_phone.required' => 'Khong duoc de trong',
            'email_or_phone.regex' => 'Vui long nhap email hoac so dien thoai',
            'password.required' => 'Khong duoc de trong',
            'password.min' => 'Mat khau khong duoc it hon 6 ki tu',
            'password.max' => 'Mat khau khong duoc qua 30 ki tu',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'errors' => $validator->errors()
            ]);
        }

        $user_name = $request->email_or_phone;
        $regex_email = '/^.+@.+$/i';
        if (preg_match($regex_email, $user_name)){
            $column = 'email';
        } else
        {
            $column = 'phone';
        }
        $token = Auth::guard('api')->attempt([$column => $user_name, 'password'=>$request->password], true);
        if ($token ) {

            return response()->json([
                'status' => 'success',
                'access_token' => $token,
            ]);
        }
        else {
            return response()->json([
                'status' => 'failed',
                'errors' => ['loser'=>['Tai khoan va mat khau khong chinh xac']]

            ]);
        }
    }
    public function searchUser(Request $request){
        $validator = Validator::make($request->all(), [
            'user_name' => ['max:30'],
            'id' => ['exists:users,id'],
        ], [
            'user_name.max' => 'Tên không hợp lệ',
            'id.exists' => 'User không tồn tại'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'errors' => $validator->errors()
            ]);
        }

        $user_name = $request->user_name?? null;
        $id = $request->id ?? null;
        if ($user_name) {
            $ids = $this->relationshipInterface->getListFriend(Auth::user()->id, 1, 5);
            $list = $this->userInterface->getListUserByIds($ids, $user_name);

        } else if ($id){
            $list = $this->userInterface->getListUserByIds([$id], null);

        } else  return response()->json([
            'status' => 'failed',
            'errors' => ['Thiếu dữ liệu đầu vào']

        ]);;

        if ($list ) {
            return response()->json([
                'status' => 'success',
                'friend' => $list,
            ]);
        }
        else {
            return response()->json([
                'status' => 'failed',
                'errors' => ['Đã có lỗi xảy ra, vui lòng thử lại']

            ]);
        }

    }
    public function getInfo(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'exists:users,id'],
        ], [
            'user_id.required' => 'Vui long chon tai khoan',
            'user_id.exists' => 'Tai khoan khong ton tai',
        ]);
        if ($validator->fails()) {
            return [
            'status' => 'failed',
            "message" => json_decode($validator->errors())
            ];
        }
        $user_id_2 = intval($request->user_id);

        $relationship = $this->relationshipInterface->getRelationship(Auth::user()->id, $user_id_2);
        if ($relationship && ($relationship->type_friend == config('relationship.type_friend.prevent') || $relationship->type_friend == config('relationship.type_friend.prevented'))){
            return [
                'status' => 'failed',
                'message' => 'Taif khoản không tồn tại',
            ];
        } else {
            if ($user_id_2 != Auth::user()->id)
            $user = User::where('id', $user_id_2)->select('id', 'first_name', 'last_name', 'gender', 'bird_day', 'workplace', 'avatar', 'cover', 'education', 'story', 'address', 'created_at')->first();
            else $user = Auth::user();
            return [
                'status' => 'success',
                'data' => $user,
            ];
        }

    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_or_phone' => ['regex:/(0?)(3[2-9]|5[6|8|9]|7[0|6-9]|8[0-6|8|9]|9[0-4|6-9])[0-9]{7}|^.+@.+$/i', 'required'],
            'password' => ['required', 'min:6', 'max:30', 'confirmed'],
            'first_name' => ['required', 'max:20'],
            'last_name' => ['required', 'max:20'],
            'bird_day' => ['required', 'date', 'before:today'],
            'gender' => ['required','in:0,1,2'],
        ], [
            'email_or_phone.required' => 'Khong duoc de trong',
            'email_or_phone.regex' => 'Vui long nhap email hoac so dien thoai',
            'password.required' => 'Khong duoc de trong',
            'password.min' => 'Mat khau khong duoc it hon 6 ki tu',
            'password.max' => 'Mat khau khong duoc qua 30 ki tu',
            'password.confirmed' => 'Mat khau khong trung khop',
            'first_name.required' => 'Khong duoc de trong',
            'first_name.max' => 'Ho, ten dem khong duoc qua 50 ki tu',
            'last_name.required' => 'Khong duoc de trong',
            'last_name.max' => 'Ten khong duoc qua 50 ki tu',
            'bird_day.required' => 'Khong duoc de trong',
            'bird_day.date' => 'Vui long nhap ngay',
            'bird_day.date' => 'Ngay sinh phai nho hon ngay hien tai',
            'gender.required' => 'Vui long chon gioi tinh',
            'gender.in' => 'Gioi tinh khong hop le',
        ]);
        $errors = (array)json_decode($validator->errors());
        $options = [];
        $user_name = $request->email_or_phone;
        $regex_email = '/^.+@.+$/i';
        if (preg_match($regex_email, $user_name)){
            $options['email'] = $user_name;
            if (User::where('email', $user_name)->count())
            {
                $errors["email_or_phone"][] = "Email da ton tai";
            };
        } else
        {
            $options['phone'] = $user_name;
            if (User::where('phone', $user_name)->count())
            {
                $errors["email_or_phone"][] = "So dien thoai da ton tai";
            };
        }
        if ($errors) {
            return response()->json([
            'status' => 'failed',
            "message " => $errors,
            ]);
        }
        $options['first_name'] = $request->first_name;
        $options['last_name'] = $request->last_name;
        $options['password'] = $request->password;
        $options['bird_day'] = $request->bird_day;
        $options['gender'] = $request->gender;
        $options['display_friend'] = config('user.display_friend.yes');
        $options['display_follow'] = config('user.display_follow.yes');
        $result = $this->userInterface->create($options);
        return response()->json([
            'status' => 'success',
            'message' => 'Tao moi thanh cong',
            'data' => $result
        ]);


    }

    public function logoff(Request $request)
    {
        Auth::logout();
        return response()->json([
            "status" => "success",
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'email' => ['email', Rule::unique('users')->ignore($user->id)],
            'phone' =>['regex:/^(0?)(3[2-9]|5[6|8|9]|7[0|6-9]|8[0-6|8|9]|9[0-4|6-9])[0-9]{7}$/', Rule::unique('users')->ignore($user->id)],
            'password' => ['min:6', 'max:30', 'confirmed'],
            'first_name' => ['required', 'max:50'],
            'last_name' => ['required', 'max:50'],
            'bird_day' => ['date', 'before:today'],
            'gender' => ['in:1,2,3'],
            'avatar' => ['image'],
            'cover' => ['image'],
            'story' => ['max:256'],
            'address' => ['max:100'],
            'display_friend' => ['in:1, 2'],
            'display_follow' => ['in: 1, 2'],
            'education' => ['array'],
            'workplace' => ['array'],
            'education.*' => ['array'],

        ], [
            'email.email' => 'Vui long nhap email',
            'email.unique' => 'Email da ton tai',
            'phone.regex' => 'Vui long nhap so dien thoai',
            'phone.unique' => 'So dien thoai da ton tai',
            'password.min' => 'Mat khau khong duoc it hon 6 ki tu',
            'password.max' => 'Mat khau khong duoc qua 30 ki tu',
            'password.confirmed' => 'Mat khau khong trung khop',
            'first_name.required' => 'Khong duoc de trong',
            'first_name.max' => 'Ho, ten dem khong duoc qua 50 ki tu',
            'last_name.required' => 'Khong duoc de trong',
            'last_name.max' => 'Ten khong duoc qua 50 ki tu',
            'bird_day.date' => 'Vui long nhap ngay',
            'bird_day.date' => 'Ngay sinh phai nho hon ngay hien tai',
            'gender.in' => 'Gioi tinh khong hop le',
            'avatar.image' => 'Vui long nhap anh',
            'cover.image' => 'Vui long nhap anh',
            'story.max' => 'Khong duoc nhap qua 256 ky tu',
            'address.max' => 'Khong duoc nhap qua 100 ky tu',
            'workplace.array'   => 'Định dạng không hợp lệ',
            'education.array'   => 'Định dạng không hợp lệ',
            'education.*.array'   => 'Định dạng không hợp lệ',
        ]);
        $errors = (array)json_decode($validator->errors());
        $options = $request->all();
        if (($options['password'] ?? null) && Hash::check($options['password'], $user->password)) {
        $errors['password'][] = "Mat khau khong duoc trung voi mat khau cu";
        }
        // if ( ($options['email'] ?? null) && $user->email) {
        //     $errors['email'][]="Ban da dang ky email";
        // }
        // if ( ($options['phone'] ?? null) && $user->phone) {
        //     $errors['phone'][]="Ban da dang ky so dien thoai";
        // }
        if ($options['education'] ?? null)
            $options['education'] = json_encode($options['education']);
        if ($options['workplace'] ?? null)
            $options['workplace'] = json_encode($options['workplace']);
        if ($errors) {
            return response()->json([
                'status' => 'failed',
                'errors' => (object)$errors,
            ]);
        }
        $result = $this->userInterface->update($options);
        if ($result) {
            return response()->json([
                'status' => 'success',
                'message' => 'Cap nhap thanh cong',
                'data' => $result
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'errors' => ["loser" => 'Cap nhap that bai']
            ]);
        }
    }

    public function test(Request $request)
    {

        $imageName = uniqid() . time() . '.' . $request->image->getClientOriginalExtension();
        $image = Image::make($request->image)->encode('jpg', 75);
        // Storage::disk('google')->put('test.txt',  "123");
        Storage::disk('google')->put($imageName, $image);
            return response()->json([
                'status' => 'success',
                'data' => [
                    'image_name' => $imageName
                ]
            ]);
    }
}
