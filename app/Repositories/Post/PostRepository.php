<?php

namespace App\Repositories\Post;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Repositories\Member\MemberInterface;
use App\Models\User;
use App\Models\Post;
use App\Models\Group;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Like;
use App\Models\UserViewPost;

class PostRepository implements PostInterface
{
    public function __construct(MemberInterface $memberInterface)
    {
        $this->memberInterface = $memberInterface;
    }


    public function create($options)
    {
        return Post::create($options);
    }
    public function update($id, $options)
    {
        $post = Post::find($id);

        if (isset($options['data'])) {
            $post->data = $options['data'];
        };
        if (isset($options['user_id_tags'])) {
            $post->user_id_tags = $options['user_id_tags'];
        };
        if (isset($options['src_image'])) {
            $post->src_image = $options['src_image'];
        };
        if (isset($options['user_id_browse'])) {
            $post->user_id_browse = $options['user_id_browse'];
        };
        return $post->save();
    }

    public function getListPost($options)
    {
        $auth = Auth::user()->id;
        $post = Post::query();
        if ($options['group_id'] ?? null) {
            $group = Group::find($options['group_id']);
        }
        $listUser = User::all();
        $listUser->push($auth);
        $listGroup = $this->memberInterface->getListGroupId();


        if ($options['user_id'] ?? null) {
            $post->whereNull('group_id')->where(function ($q) use ($options) {
                $q->orWhere(function ($q1) use ($options) {
                    $q1->where('user_id', $options['user_id'])->whereNull('user_id_2');
                });
                $q->orWhere(function ($q1) use ($options) {
                    $q1->where('user_id_2', $options['user_id']);
                });
            });

            /**
             *
             */
        } else if ($options['group_id'] ?? null) {
            $post->where('group_id', $options['group_id']);
            if ($group['browse_post'] == config('group.browse_post.yes')) {
                $post->whereNotNull('user_id_browse');
            };
        } else if ($options['post_id'] ?? null) {
            $post->where('id', $options['post_id']);
        } else {
            $post->whereNull('group_id');
        }



        if ($options['get_image'] ?? null) {
            $post->where('src_images', '<>', "\"\"");
        }
        $page = $options['page'] ?? 1;

        if ($options['get_image'] ?? null) {
            $post->orderBy('created_at', 'desc')
                ->select('id', 'src_images')
                ->withCount('like', 'share', 'comment');
            $post->offset(($page - 1) * 12)->limit(12);
        } else {
            $post->orderBy('created_at', 'desc')
                ->with('user', 'user_2', 'isLike', 'post_share', 'post_share.user', 'post_share.user_2')
                ->withCount('like', 'share', 'comment');
            if ($page == 1)
                $post->offset(0)->limit(5);
            else
                $post->offset(($page - 1) * 5)->limit(5);
        }

        return $post->get();
    }

    public function getListPostSearch($options)
    {
        $post = Post::query();

        if ($options['group_id'] ?? null) {
            $post->where('data', 'like', '%' . $options['keySearch'] . '%')->where('group_id', $options['group_id']);
        } else {
            $post->where('data', 'like', '%' . $options['keySearch'] . '%')->whereNull('group_id');
        }
        $page = $options['page'] ?? 1;

        $post->orderBy('created_at', 'desc')
            ->with('user', 'user_2', 'isLike', 'post_share', 'post_share.user', 'post_share.user_2')
            ->withCount('like', 'share', 'comment');
        if ($page == 1)
            $post->offset(0)->limit(8);
        else
            $post->offset(($page - 1) * 8)->limit(8);


        return $post->get();
    }



    public function getListSearchAdmin($options)
    {

        $post = Post::query();
        if ($options['group_id']) {
            $post->orderBy('created_at', 'asc')->where('group_id', $options['group_id'])
                ->with('user', 'user_2', 'isLike', 'post_share', 'post_share.user', 'post_share.user_2')
                ->withCount('like', 'share', 'comment')->where('data', 'like', '%' . $$options['data'] . '%');
            return $post->get();
        } else {
            $post->orderBy('created_at', 'asc')
                ->with('user', 'user_2', 'isLike', 'post_share', 'post_share.user', 'post_share.user_2')
                ->withCount('like', 'share', 'comment')->where('data', 'like', '%' . $$options['data'] . '%');
            return $post->get();
        }
    }







    public function getListPostBrowse($options)
    {
        $auth = Auth::user()->id;
        $post = Post::query();
        if ($options['group_id']) {
            $group = Group::find($options['group_id']);
        } else return [];

        if ($options['group_id']) {
            $post->where('group_id', $options['group_id']);
            if ($group['browse_post'] == config('group.browse_post.yes')) {
                $post->whereNull('user_id_browse');
            } else return [];
        }
        $page = $options['page'] ?? 1;
        $post->orderBy('created_at', 'desc')->with('user', 'user_2', 'isLike', 'post_share', 'post_share.user', 'post_share.user_2');
        $post->offset(($page - 1) * 8)->limit(8);
        return $post->get();
    }



    public function getCountPost($options)
    {
        $auth = Auth::user()->id;
        $post = Post::query();
        $listUser = User::all();
        $listFollow = User::all();
        $listGroup = $this->memberInterface->getListGroupId();
        $type_friend = $options['type_friend'] ?? config('relationship.type_friend.no_friend');
        $type_follow = $options['type_follow'] ?? config('relationship.type_follow.no_follow');
        if ($options['user_id']) {
            $post->whereNull('group_id')->where(function ($q) use ($options) {
                $q->orWhere(function ($q1) use ($options) {
                    $q1->where('user_id', $options['user_id'])->whereNull('user_id_2');
                });
                $q->orWhere(function ($q1) use ($options) {
                    $q1->where('user_id_2', $options['user_id']);
                });
            });
            if ($auth == $options['user_id']) {
            } else if ($type_friend == config('relationship.type_friend.friend')) {
                $post->where(function ($q) use ($auth) {
                    $q->orWhere(function ($q1) use ($auth) {
                        $q1->where('type_show', config('post.type_show.specific_friend'))->where('user_view_posts', 'like', '%"' . $auth . '"%');
                    });
                    $q->orWhere(function ($q1) use ($auth) {
                        $q1->where('type_show', config('post.type_show.friends_except'))->where('user_view_posts', 'not like', '%"' . $auth . '"%');
                    });
                    $q->orWhere(function ($q1) {
                        $q1->where('type_show', config('post.type_show.friend'))->orWhere('type_show', config('post.type_show.public'));
                    });
                });
            } else {
                $post->where('type_show', config('post.type_show.public'));
            }
        } else if ($options['group_id']) {
            $post->where('group_id', $options['group_id']);
        } else {
            $post->where(function ($q) use ($listGroup, $listFollow, $listUser, $auth) {
                $q->orWhere(function ($q1) use ($listGroup) {
                    $q1->whereIn('group_id', $listGroup)->whereNotIn('user_id');
                });
                $q->orWhere(function ($q1) use ($listFollow) {
                    $q1->whereIn('user_id', $listFollow)->where('type_show', config('post.type_show.public'));
                });
                $q->orWhere(function ($q1) use ($listUser, $auth) {
                    $q1->whereIn('user_id', $listUser)->where(function ($q3) use ($auth) {
                        $q3->orWhere(function ($q4) use ($auth) {
                            $q4->where('type_show', config('post.type_show.specific_friend'))->where('user_view_posts', 'like', '%"' . $auth . '"%');
                        });
                        $q3->orWhere(function ($q4) use ($auth) {
                            $q4->where('type_show', config('post.type_show.friends_except'))->where('user_view_posts', 'not like', '%"' . $auth . '"%');
                        });
                        $q3->orWhere(function ($q4) {
                            $q4->where('type_show', config('post.type_show.friend'))->orWhere('type_show', config('post.type_show.public'));
                        });
                    });
                    $q1->whereIn('user_id_2', $listUser)->where(function ($q3) use ($auth) {
                        $q3->orWhere(function ($q4) use ($auth) {
                            $q4->where('type_show', config('post.type_show.specific_friend'))->where('user_view_posts', 'like', '%"' . $auth . '"%');
                        });
                        $q3->orWhere(function ($q4) use ($auth) {
                            $q4->where('type_show', config('post.type_show.friends_except'))->where('user_view_posts', 'not like', '%"' . $auth . '"%');
                        });
                        $q3->orWhere(function ($q4) {
                            $q4->where('type_show', config('post.type_show.friend'))->orWhere('type_show', config('post.type_show.public'));
                        });
                    });
                });
            });
        }
        // if ()
        return $post->count();
    }



    public function getListSharePost($post_id, $page)
    {
        return Post::where('post_id', $post_id)->select('id', 'user_id')->with('user')->offset(($page - 1) * 10)->limit(10)->get();
    }




    public function getListPostAdmin($page)
    {
        $sumPage = Post::all()->count();
        $post = Post::orderBy('created_at', 'desc')
            ->with('user', 'user_2', 'isLike', 'post_share', 'post_share.user', 'post_share.user_2', 'group')
            ->withCount('like', 'share', 'comment')->offset(($page - 1) * 6)->limit(6)->get();

        return [$post, $sumPage];
    }

    public function deletePost($options)
    {
        $post = Post::find($options);
        return $post->delete();
    }
}
