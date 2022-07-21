<?php

namespace App\Repositories\Post;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Repositories\Relationship\RelationshipInterface;
use App\Repositories\Member\MemberInterface;
use App\Models\User;
use App\Models\Post;
use App\Models\Group;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Like;
use App\Models\UserViewPost;

class PostRepository implements PostInterface
{
    public function __construct(RelationshipInterface $relationshipInterface, MemberInterface $memberInterface)
    {
        $this->relationshipInterface = $relationshipInterface;
        $this->memberInterface = $memberInterface;
    }


    public function create($options)
    {
        return Post::create($options);
    }
    public function update($id, $options)
    {
        $post = Post::find($id);
        if (isset($options['type_post'])) {
            $post->type_post = $options['type_post'];
        };

        if (isset($options['type_show'])) {
            $post->type_show = $options['type_show'];
        };
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
        $listFriend = User::all();
        $listFriend->push($auth);
        $listGroup = $this->memberInterface->getListGroupId();
        $type_friend = $options['type_friend'] ?? config('relationship.type_friend.no_friend');
        $type_follow = $options['type_follow'] ?? config('relationship.type_follow.no_follow');


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
             * ! get post for group
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
                $post->offset(3 + ($page - 2) * 5)->limit(5);
        }

        return $post->get();
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
        $post->offset(6 + ($page - 2) * 8)->limit(8);
        return $post->get();
    }



    public function getCountPost($options)
    {
        $auth = Auth::user()->id;
        $post = Post::query();
        $listFriend = User::all();
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
            $post->where(function ($q) use ($listGroup, $listFollow, $listFriend, $auth) {
                $q->orWhere(function ($q1) use ($listGroup) {
                    $q1->whereIn('group_id', $listGroup)->whereNotIn('user_id');
                });
                $q->orWhere(function ($q1) use ($listFollow) {
                    $q1->whereIn('user_id', $listFollow)->where('type_show', config('post.type_show.public'));
                });
                $q->orWhere(function ($q1) use ($listFriend, $auth) {
                    $q1->whereIn('user_id', $listFriend)->where(function ($q3) use ($auth) {
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
                    $q1->whereIn('user_id_2', $listFriend)->where(function ($q3) use ($auth) {
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


    public function searchPost($data)
    {

        $post = Post::query();

        $post->orderBy('created_at', 'asc')
            ->with('user', 'user_2', 'isLike', 'post_share', 'post_share.user', 'post_share.user_2')
            ->withCount('like', 'share', 'comment')->where('data', 'like', '%' . $data . '%');
        return $post->get();
    }

    public function getListPostAdmin($options)
    {
        $post = Post::query();
        if ($options['group_id']) {
            $group = Group::find($options['group_id']);
        }

        if ($options['user_id']) {
            $post->whereNull('group_id')->where(function ($q) use ($options) {
                $q->orWhere(function ($q1) use ($options) {
                    $q1->where('user_id', $options['user_id'])->whereNull('user_id_2');
                });
                $q->orWhere(function ($q1) use ($options) {
                    $q1->where('user_id_2', $options['user_id']);
                });
            });
        }

        if ($options['get_image']) {
            $post->where('src_images', '<>', "\"\"");
        }

        if ($options['get_image']) {
            $post->orderBy('created_at', 'asc')
                ->select('id', 'src_images')
                ->withCount('like', 'share', 'comment');
        } else {
            $post->orderBy('created_at', 'asc')
                ->with('user', 'user_2', 'isLike', 'post_share', 'post_share.user', 'post_share.user_2')
                ->withCount('like', 'share', 'comment');
        }

        return $post->get();
    }

    public function deletePost($options)
    {
        $post = Post::find($options);
        return $post->delete();
    }
}
