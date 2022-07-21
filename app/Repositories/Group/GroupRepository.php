<?php

namespace App\Repositories\Group;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Post;
use App\Models\Member;
use DB;
use App\Models\Group;

class GroupRepository implements GroupInterface
{
    public function create($options)
    {
        $group = new Group();
        if (isset($options['name'])) {
            $group->name = $options['name'];
        };
        if (isset($options['cover'])) {
            $group->cover = $options['cover'];
        };
        if (isset($options['type'])) {
            $group->type = $options['type'];
        };
        if (isset($options['browse_post'])) {
            $group->browse_post = $options['browse_post'];
        };
        if (isset($options['intro'])) {
            $group->intro = $options['intro'];
        };
        if (isset($options['regulations'])) {
            $group->regulations = $options['regulations'];
        };
        if (isset($options['question'])) {
            $group->question = $options['question'];
        };
        if ($group->save()) {
            return $group;
        } else {
            return null;
        }
    }
    public function update($id, $options)
    {
        $group = Group::find($id);
        if (isset($options['name'])) {
            $group->name = $options['name'];
        };
        if (isset($options['cover'])) {
            $group->cover = $options['cover'];
        };
        if (isset($options['type'])) {
            $group->type = $options['type'];
        };
        if (isset($options['browse_post'])) {
            $group->browse_post = $options['browse_post'];
        };
        if (isset($options['intro'])) {
            $group->intro = $options['intro'];
        };
        if (isset($options['regulations'])) {
            $group->regulations = $options['regulations'];
        };
        if (isset($options['question'])) {
            $group->question = $options['question'];
        };
        if ($group->save()) {
            return $group;
        } else {
            return null;
        }
    }
    public function delete($id)
    {
        try {
            DB::beginTransaction();
            Post::where('group_id', $id)->delete();
            Member::where('group_id', $id)->delete();
            Group::find($id)->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
    }
}
