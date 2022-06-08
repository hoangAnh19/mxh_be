<?php

namespace App\Providers;

use App\Repositories\User\UserInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\Member\MemberInterface;
use App\Repositories\Member\MemberRepository;
use App\Repositories\Comment\CommentInterface;
use App\Repositories\Comment\CommentRepository;
use App\Repositories\Chat\ChatInterface;
use App\Repositories\Chat\ChatRepository;
use App\Repositories\Group\GroupInterface;
use App\Repositories\Group\GroupRepository;
use App\Repositories\Relationship\RelationshipInterface;
use App\Repositories\Relationship\RelationshipRepository;
use App\Repositories\Post\PostInterface;
use App\Repositories\Post\PostRepository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Repositories\Like\LikeInterface;

use App\Repositories\Like\LikeRepository;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->bind(GroupInterface::class,GroupRepository::class);
        $this->app->bind(MemberInterface::class,MemberRepository::class);
        $this->app->bind(UserInterface::class,UserRepository::class);
        $this->app->bind(RelationshipInterface::class,RelationshipRepository::class);
        $this->app->bind(PostInterface::class,PostRepository::class);
        $this->app->bind(LikeInterface::class,LikeRepository::class);
        $this->app->bind(ChatInterface::class,ChatRepository::class);
        $this->app->bind(CommentInterface::class,CommentRepository::class);

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);
    }
}
