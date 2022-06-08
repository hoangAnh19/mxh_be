<?php
namespace App\Repositories\Relationship;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Relationship;

class RelationshipRepository implements RelationshipInterface
{
    private $user_id_1;
    private $user_id_2;
    public function getRelationship($user_id_1, $user_id_2) {
        $this->user_id_1 = $user_id_1;
        $this->user_id_2 = $user_id_2;
        $query = Relationship::where(function($q) {
            $q->orWhere(function($q1) {
                $q1->where('user_id_1', $this->user_id_1)->where('user_id_2', $this->user_id_2);
            });
            $q->orWhere(function($q1) {
                $q1->where('user_id_2', $this->user_id_1)->where('user_id_1', $this->user_id_2);
            });
        });
        return $query->first();
    }

    public function createRelationship($user_id_1, $user_id_2, $type)
    {
        $relationship = new Relationship();
        $relationship->user_id_1 = $user_id_1;
        $relationship->user_id_2 = $user_id_2;
        $relationship->type = $type;
        $relationship->friend_day = \Carbon\Carbon::now()->format('d/m/Y H:i:s');
        return $relationship->save();
    }

    public function getListFollowed($user_id)
    {
        $list_prevent_1 = Relationship::
        where('user_id_1', Auth::user()->id)
        ->whereIn('type_friend', [config('relationship.type_friend.prevent'), config('relationship.type_friend.prevented')])
        ->pluck('user_id_2');
        $list_prevent_2 = Relationship::
        where('user_id_2', Auth::user()->id)
        ->whereIn('type_friend', [config('relationship.type_friend.prevent'), config('relationship.type_friend.prevented')])
        ->pluck('user_id_1');
        $query1 = Relationship::where('user_id_1', $user_id)
                ->whereIn('type_follow', [config('relationship.type_follow.followed'), config('relationship.type_follow.double_follow')])
                ->where('type_friend', '<>', config('relationship.type_friend.friend'))->whereNotIn('user_id_2', $list_prevent_1)
                ->whereNotIn('user_id_2', $list_prevent_2)->pluck('user_id_2');
        $query2 = Relationship::where('user_id_2', $user_id)->whereIn('type_follow', [config('relationship.type_follow.follow'), config('relationship.type_follow.double_follow')])
        ->where('type_friend', '<>', config('relationship.type_friend.friend'))
        ->whereNotIn('user_id_1', $list_prevent_1)
        ->whereNotIn('user_id_1', $list_prevent_2)
        ->pluck('user_id_1');

        return $query1->concat($query2);
    }
    public function getCountFollowed($user_id)
    {
        $list_prevent_1 = Relationship::
        where('user_id_1', Auth::user()->id)
        ->whereIn('type_friend', [config('relationship.type_friend.prevent'), config('relationship.type_friend.prevented')])
        ->pluck('user_id_2');
        $list_prevent_2 = Relationship::
        where('user_id_2', Auth::user()->id)
        ->whereIn('type_friend', [config('relationship.type_friend.prevent'), config('relationship.type_friend.prevented')])
        ->pluck('user_id_1');
        $query1 = Relationship::where('user_id_1', $user_id)
                ->whereIn('type_follow', [config('relationship.type_follow.followed'), config('relationship.type_follow.double_follow')])
                ->where('type_friend', '<>', config('relationship.type_friend.friend'))->whereNotIn('user_id_2', $list_prevent_1)
                ->whereNotIn('user_id_2', $list_prevent_2)->count();
        $query2 = Relationship::where('user_id_2', $user_id)->whereIn('type_follow', [config('relationship.type_follow.follow'), config('relationship.type_follow.double_follow')])
        ->where('type_friend', '<>', config('relationship.type_friend.friend'))
        ->whereNotIn('user_id_1', $list_prevent_1)
        ->whereNotIn('user_id_1', $list_prevent_2)
        ->count();

        return $query1 + $query2;
    }
    public function getListFriend($user_id, $page=1,$limit=5)
    {
        $this->user_id_1 = $user_id;
        // $list_prevent_1 = Relationship::
        // where('user_id_1', $this->user_id_2)
        // ->whereIn('type_friend', [config('relationship.type_friend.prevent'), config('relationship.type_friend.prevented')])
        // ->pluck('user_id_2');
        // $list_prevent_2 = Relationship::
        // where('user_id_2', $this->user_id_2)
        // ->whereIn('type_friend', [config('relationship.type_friend.prevent'), config('relationship.type_friend.prevented')])
        // ->pluck('user_id_1');
        $query1 = Relationship::where('user_id_1', $this->user_id_1)
            ->where('type_friend', config('relationship.type_friend.friend'))
            // ->whereNotIn('user_id_2', $list_prevent_1)
            // ->whereNotIn('user_id_2', $list_prevent_2)
            ->pluck('user_id_2');
        $query2 = Relationship::where('user_id_2', $this->user_id_1)
            ->where('type_friend', config('relationship.type_friend.friend'))
            // ->whereNotIn('user_id_1', $list_prevent_1)
            // ->whereNotIn('user_id_1', $list_prevent_2)
            ->pluck('user_id_1');
            if ($page) {
                return collect($query1->concat($query2)->chunk($limit)->toArray()[$page - 1] ?? []);

            } else {
                return $query1->concat($query2);
            }
    }


    public function getListPrevent()
    {
        $user_id = Auth::user()->id;

        $query1 = Relationship::where('user_id_1', $user_id)
        ->where('type_friend', config('relationship.type_friend.prevent'))->pluck('user_id_2');
        $query2 = Relationship::where('user_id_2', $user_id)
        ->where('type_friend', config('relationship.type_friend.prevented'))->pluck('user_id_1');
        return $query1->concat($query2);
    }
    public function getListPreventAndPrevented()
    {
        $user_id = Auth::user()->id;

        $query1 = Relationship::where('user_id_1', $user_id)
        ->whereIn('type_friend', [config('relationship.type_friend.prevented'), config('relationship.type_friend.prevent')])->pluck('user_id_2');
        $query2 = Relationship::where('user_id_2', $user_id)
        ->whereIn('type_friend', [config('relationship.type_friend.prevented'), config('relationship.type_friend.prevent')])->pluck('user_id_1');
        return $query1->concat($query2);
    }

    public function updateRelationship($id, $type_friend = null, $type_follow = null, $date_accept = false)
    {
        $relationship = Relationship::find($id);
        if ($type_friend !== null) {
            $relationship->type_friend = $type_friend;
        }

        if ($type_follow !== null) {
            $relationship->type_follow = $type_follow;
        }

        if ($date_accept) {
            if ($type_friend == config('relationship.type_friend.friend')) {
                $relationship->date_accept = Carbon::now();
            } else {
                $relationship->date_accept = null;
            }
        }
        return $relationship->save();
    }

    public function getMutualFriends($user_id) {
       $array1 = $this->getListFriend(Auth::user()->id,0,0);
       $array2 = $this->getListFriend($user_id,0,0);
       return $array1->intersect($array2);
    }

    public function getListFollow() {
        $user_id = Auth::user()->id;
        $list_prevent_1 = Relationship::
        where('user_id_1', Auth::user()->id)
        ->whereIn('type_friend', [config('relationship.type_friend.prevent'), config('relationship.type_friend.prevented')])
        ->pluck('user_id_2');
        $list_prevent_2 = Relationship::
        where('user_id_2', Auth::user()->id)
        ->whereIn('type_friend', [config('relationship.type_friend.prevent'), config('relationship.type_friend.prevented')])
        ->pluck('user_id_1');
        $query1 = Relationship::where('user_id_1', $user_id)
                ->whereIn('type_follow', [config('relationship.type_follow.follow'), config('relationship.type_follow.double_follow')])
                ->where('type_friend', '<>', config('relationship.type_friend.friend'))->whereNotIn('user_id_2', $list_prevent_1)
                ->whereNotIn('user_id_2', $list_prevent_2)->pluck('user_id_2');
        $query2 = Relationship::where('user_id_2', $user_id)->whereIn('type_follow', [config('relationship.type_follow.followed'), config('relationship.type_follow.double_follow')])
        ->where('type_friend', '<>', config('relationship.type_friend.friend'))
        ->whereNotIn('user_id_1', $list_prevent_1)
        ->whereNotIn('user_id_1', $list_prevent_2)
        ->pluck('user_id_1');

        return $query1->concat($query2);
    }

    public function getListRequestFriend() {
        $id = Auth::user()->id;
        $query1 = Relationship::where('user_id_1', $id)
            ->where('type_friend', config('relationship.type_friend.request_friend'))
            ->pluck('user_id_2');
        $query2 = Relationship::where('user_id_2', $id)
        ->where('type_friend', config('relationship.type_friend.request_friended'))
        ->pluck('user_id_1');

        return $query1->concat($query2);
    }

    public function getListRequestFriended($page, $limit = 18) {
        $id = Auth::user()->id;
        $query1 = Relationship::where('user_id_1', $id)
            ->where('type_friend', config('relationship.type_friend.request_friended'))
            ->pluck('user_id_2');
        $query2 = Relationship::where('user_id_2', $id)
        ->where('type_friend', config('relationship.type_friend.request_friend'))
        ->pluck('user_id_1');
            if ($page) {
                return collect($query1->concat($query2)->chunk($limit)->toArray()[$page - 1] ?? []);

            } else {
                return $query1->concat($query2);
            }

    }
    public function getListFriendHasFollow() {
        $user_id = Auth::user()->id;
        $list_friend_1 = Relationship::
        where('user_id_1', Auth::user()->id)
        ->where('type_friend', config('relationship.type_friend.friend'))
        ->pluck('user_id_2');
        $list_friend_2 = Relationship::
        where('user_id_2', Auth::user()->id)
        ->where('type_friend', config('relationship.type_friend.friend'))
        ->pluck('user_id_1');
        $query1 = Relationship::where('user_id_1', $user_id)
                ->whereIn('type_follow', [config('relationship.type_follow.follow'), config('relationship.type_follow.double_follow')])
                ->whereIn('user_id_2', $list_friend_1)
                ->pluck('user_id_2');
        $query2 = Relationship::where('user_id_2', $user_id)->whereIn('type_follow', [config('relationship.type_follow.followed'), config('relationship.type_follow.double_follow')])

        ->whereIn('user_id_1', $list_friend_2)
        ->pluck('user_id_1');

        return $query1->concat($query2);
    }
    public function getListFriendSuggestions($page) {
        $listFriend = $this->getListFriend(Auth::user()->id,0,0);
        $list_prevent_1 = Relationship::
        where('user_id_1', Auth::user()->id)
        ->whereIn('type_friend', [config('relationship.type_friend.prevent'), config('relationship.type_friend.prevented')])
        ->pluck('user_id_2');
        $list_prevent_2 = Relationship::
        where('user_id_2', Auth::user()->id)
        ->whereIn('type_friend', [config('relationship.type_friend.prevent'), config('relationship.type_friend.prevented')])
        ->pluck('user_id_1');
        $list_prevent = $list_prevent_1->concat($list_prevent_2);
        $listFollow = $this->getListFollow();
        $listRequestFriend = $this->getListRequestFriend();
        $listRequestFriended = $this->getListRequestFriended(0);
        $listNotDisplay = $list_prevent->concat($listFriend)->concat($listFollow)->concat($listRequestFriend)->concat($listRequestFriended)->concat([Auth::user()->id]);
        $list_1 = Relationship::whereIn('user_id_1', $listFriend)->whereNotIn('user_id_2', $listNotDisplay)->pluck('user_id_2');
        $list_2 = Relationship::whereIn('user_id_2', $listFriend)->whereNotIn('user_id_1', $listNotDisplay)->pluck('user_id_1');
        if ((!($list_1->count())) && (!($list_2->count()))) {
            $list_1 = Relationship::whereNotIn('user_id_2', $listNotDisplay)->pluck('user_id_2');
            $list_2 = Relationship::whereNotIn('user_id_1', $listNotDisplay)->pluck('user_id_1');
        }
        if ($page) {
            return collect($list_1->concat($list_2)->chunk(18)->toArray()[$page - 1 ?? []]);

        } else {
            return $list_1->concat($list_2);
        }
        return $list_1->concat($list_2);
    }
}
