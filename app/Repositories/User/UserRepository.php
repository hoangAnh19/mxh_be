<?php
namespace App\Repositories\User;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use DB;
class UserRepository implements UserInterface
{
    public function create($options)
    {
        $user =  new User();
        if (isset($options['first_name'])) {
            $user->first_name = $options['first_name'];
        };
        if (isset($options['last_name'])) {
            $user->last_name = $options['last_name'];
        };
        if (isset($options['password'])) {
            $user->password = bcrypt($options['password']);
        };
        if (isset($options['bird_day'])) {
            $user->bird_day = $options['bird_day'];
        };
        if (isset($options['gender'])) {
            $user->gender = $options['gender'];
        };
        if (isset($options['email'])) {
            $user->email = $options['email'];
        };
        if (isset($options['phone'])) {
            $user->phone = $options['phone'];
        };
        if (isset($options['display_friend'])) {
            $user->display_friend = $options['display_friend'];
        };
        if (isset($options['display_follow'])) {
            $user->display_follow = $options['display_follow'];
        };
        $user->save();
        return $user;
    }

    // public function ()
    public function update($options)
    {
        $user_id = Auth::user()->id;
        $user = User::find($user_id);
        if (isset($options['first_name'])) {
            $user->first_name = $options['first_name'];
        };
        if (isset($options['last_name'])) {
            $user->last_name = $options['last_name'];
        };
        if (isset($options['password'])) {
            $user->password = bcrypt($options['password']);
        };
        if (isset($options['bird_day'])) {
            $user->bird_day = $options['bird_day'];
        };
        if (isset($options['address'])) {
            $user->address = $options['address'];
        };
        if (isset($options['gender'])) {
            $user->gender = $options['gender'];
        };
        if (isset($options['email'])) {
            $user->email = $options['email'];
        };
        if (isset($options['phone'])) {
            $user->phone = $options['phone'];
        };
        if (isset($options['avatar'])) {
            $user->avatar = $options['avatar'];
        };
        if (isset($options['cover'])) {
            $user->cover = $options['cover'];
        };
        if (isset($options['story'])) {
            $user->story = $options['story'];
        };
        if (isset($options['workplace'])) {
            $user->workplace = $options['workplace'];
        };
        if (isset($options['education'])) {
            $user->education = $options['education'];
        };
        if (isset($options['display_friend'])) {
            $user->display_friend = $options['display_friend'];
        };
        if (isset($options['display_follow'])) {
            $user->display_follow = $options['display_follow'];
        };
        if ($user->save())
        return $user;
    }

    public function getListUserByIdsOrderByMessage($user_ids, $key_search){
        // return User::whereIn('id', $user_ids)->select('id', 'first_name', 'last_name', 'avatar', 'cover')->with('relationship1','relationship2')->get();
        $list = User::whereIn('id', $user_ids)->select('id', 'first_name', 'last_name', 'avatar', 'cover');
        if ($key_search) $list->where(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), 'LIKE', "%".$key_search."%");

        return $list->with('relationship1','relationship2')->get();
    }
    public function getListUserByIds($user_ids, $key_search){
        // return User::whereIn('id', $user_ids)->select('id', 'first_name', 'last_name', 'avatar', 'cover')->with('relationship1','relationship2')->get();
        $list = User::whereIn('id', $user_ids)->select('id', 'first_name', 'last_name', 'avatar', 'cover');
        if ($key_search) $list->where(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), 'LIKE', "%".$key_search."%")->orderBy(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"));

        return $list->with('relationship1','relationship2')->get();
    }
    public function getListUserBirthDayByIds($user_ids){
        return User::whereIn('id', $user_ids)->select('id', 'first_name', 'last_name', 'avatar', 'bird_day')->get();
    }
}