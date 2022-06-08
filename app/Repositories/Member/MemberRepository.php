<?php
namespace App\Repositories\Member;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Member;

class MemberRepository implements MemberInterface
{
    public function create($options) {
        $member = new Member();
        if (isset($options['group_id'])) {
            $member->group_id = $options['group_id'];
        };
        if (isset($options['user_id'])) {
            $member->user_id = $options['user_id'];
        };
        if (isset($options['answer'])) {
            $member->answer = $options['answer'];
        };
        if (isset($options['role'])) {
            $member->role = $options['role'];
        };
        if (isset($options['status'])) {
            $member->status = $options['status'];
        };
        if ($data = $member->save()) {
            return $member;
        } else
        return null;
    }

    public function update($id, $options) {
        $member = Member::find($id);
        if (isset($options['role'])) {
            $member->role = $options['role'];
        };
        if (isset($options['status'])) {
            $member->status = $options['status'];
        };
        if ($data = $member->save()) {
            return $data;
        } else return null;
    }
    public function getListGroupId() {
        $user_id = Auth::user()->id;
        return Member::where('user_id', $user_id)->where('status', config('member.status.public'))->pluck('group_id');
    }

    // public function getListGroupManagerId() {
    //     $user_id = Auth::user()->id;
    //     return Member::where('user_id', $user_id)->where('status', config('member.status.public'))->whereIn('role', [ config('member.role.admin'), config('member.role.censor')])->pluck('group_id');
    // }
    // public function getListGroupNomalId() {
    //     $user_id = Auth::user()->id;
    //     return Member::where('user_id', $user_id)->where('status', config('member.status.public'))->where('role', config('member.role.normal'))->pluck('group_id');
    // }
}