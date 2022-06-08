<?php

use Illuminate\Http\Request;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\GroupController;
use App\Http\Controllers\api\RelationshipController;
use App\Http\Controllers\api\LikeController;
use App\Http\Controllers\api\PostController;
use App\Http\Controllers\api\CommentController;
use App\Http\Controllers\api\ChatController;
use Illuminate\Support\Facades\Route;
// use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// Auth::routes(['verify' => true]);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('login',[UserController::class,'login'])->name('user.login');
Route::post('register',[UserController::class,'create'])->name('user.register');
Route::post('logoff',[UserController::class,'logoff'])->name('user.logoff');
Route::post('test',[UserController::class,'test'])->name('user.logoffs');
Route::get('test1',function(Request $request) {
    return 1;
});

Route::group(['middleware'=>['auth:api']], function() {
    Route::get('userInfo',function(Request $request) {
        return $request->user();
    });
    // USER
    Route::put('user',[UserController::class,'update'])->name('user.update');
    Route::get('user',[UserController::class,'getInfo'])->name('user.getInfo');
    Route::get('user/searchUser',[UserController::class,'searchUser']);

    // Relationship
    Route::post('relationship/request_friend', [RelationshipController::class, 'requestFriend'])->name('user.request_friend');
    Route::post('relationship/cancel_request_friend', [RelationshipController::class, 'cancelRequestFriend'])->name('user.cancel_request_friend');
    Route::post('relationship/accept_friend', [RelationshipController::class, 'acceptFriend'])->name('user.accept_friend');
    Route::post('relationship/cancel_friend', [RelationshipController::class, 'cancelFriend'])->name('user.cancel_friend');
    Route::post('relationship/follow', [RelationshipController::class, 'follow'])->name('user.follow');
    Route::post('relationship/cancel_follow', [RelationshipController::class, 'cancelFollow'])->name('user.cancel_follow');
    Route::post('relationship/prevent', [RelationshipController::class, 'prevent'])->name('user.prevent');
    Route::post('relationship/cancel_prevent', [RelationshipController::class, 'cancelPrevent'])->name('user.cancel_prevent');
    Route::get('relationship/get_relationship', [RelationshipController::class, 'getRelationship'])->name('user.get_relationship');
    Route::get('relationship/list_followed', [RelationshipController::class, 'listFollowed']);
    Route::get('relationship/count_followed', [RelationshipController::class, 'countFollowed']);
    Route::get('relationship/list_follow', [RelationshipController::class, 'listFollow']);
    Route::get('relationship/list_friend', [RelationshipController::class, 'listFriend'])->name('user.get_relationship');
    Route::get('relationship/list_friend_birth_day', [RelationshipController::class, 'listFriendBirthday']);
    Route::get('relationship/list_mutual_friend', [RelationshipController::class, 'listMutualFriend']);
    Route::get('relationship/list_prevent', [RelationshipController::class, 'listPrevent'])->name('user.get_list_prevent');
    Route::get('relationship/list_request_friend', [RelationshipController::class, 'listRequestFriend'])->name('user.get_list_request_friend');
    Route::get('relationship/list_request_friended', [RelationshipController::class, 'listRequestFriended'])->name('user.get_list_request_friended');
    Route::get('relationship/list_friend_suggestions', [RelationshipController::class, 'listFriendSuggestions'])->name('user.get_list_friend_suggestions');

    //GROUP
    Route::post('group/create', [GroupController::class, 'create'])->name('group.create');
    Route::get('group/get_count_member', [GroupController::class, 'getCountMember']);
    Route::get('group/get_count_pending', [GroupController::class, 'getCountPending']);
    Route::get('group/get_count_prevent', [GroupController::class, 'getCountPrevent']);
    Route::get('group/get_list_manager', [GroupController::class, 'getListManager']);
    Route::get('group/get_list_nomarl', [GroupController::class, 'getListNomarl']);
    Route::get('group/get_list_pending', [GroupController::class, 'getListPending']);
    Route::get('group/get_list_prevent', [GroupController::class, 'getListPrevent']);
    Route::post('group/update', [GroupController::class, 'update'])->name('group.update');
    Route::post('group/delete', [GroupController::class, 'delete'])->name('group.delete');
    Route::post('group/participation', [GroupController::class, 'participation'])->name('group.participation');
    Route::post('group/out-group', [GroupController::class, 'outGroup']);
    Route::post('group/browser-member', [GroupController::class, 'browserMember'])->name('group.browser-member');
    Route::post('group/browser-post', [GroupController::class, 'browserPost'])->name('group.browser-post');
    Route::post('group/cancel-post', [GroupController::class, 'cancelPost'])->name('group.cancel-post');
    Route::post('group/assign-permission', [GroupController::class, 'assignPermission'])->name('group.assign-permission');
    Route::post('group/kick-member', [GroupController::class, 'kickMember'])->name('group.kick-member');
    Route::post('group/prevent-member', [GroupController::class, 'preventMember'])->name('group.prevent-member');
    Route::post('group/cancel-prevent-member', [GroupController::class, 'cancelPreventMember'])->name('group.cancel-prevent-member');
    Route::get('group/get_list_group_manager', [GroupController::class, 'getListGroupManager']);
    Route::get('group/get_list_group', [GroupController::class, 'getListGroup']);
    Route::get('group/get_list_group_nomarl', [GroupController::class, 'getListGroupNomarl']);
    Route::get('group', [GroupController::class, 'getInfo']);
    // Route::get('group/get-list-member', [GroupController::class, 'getListMember'])->name('group.get-list-member');
    // Route::get('group/get-list-admin', [GroupController::class, 'getListAdmin'])->name('group.get-list-member');
    // Route::get('group/get-list-pending', [GroupController::class, 'getListPending'])->name('group.get-list-pending');
//  POST
    Route::get('post/get_count', [PostController::class, 'getCountPost']);
    Route::post('image/upload', [PostController::class, 'uploadImage'])->name('post.uploadImage');
    Route::post('post/create', [PostController::class, 'create'])->name('post.create');
    Route::post('post/update', [PostController::class, 'update'])->name('post.update');
    Route::get('post/get_list', [PostController::class, 'getList'])->name('post.getList');
    Route::get('post/get_list_post_browse', [PostController::class, 'getListPostBrowse']);
    Route::get('post/show', [PostController::class, 'show']);
    Route::get('image/list', [PostController::class, 'getList']);
    //Sahre
Route::get('share/get_list', [PostController::class, 'getListShare']);

    //Like
    Route::post('like', [LikeController::class, 'like']);
    Route::get('like/get_list', [LikeController::class, 'getListByPost']);
    //Comment
    Route::post('comment/create', [CommentController::class, 'create']);
    Route::get('comment/get', [CommentController::class, 'getComment']);

    //Chat
    Route::post('chat/send', [ChatController::class, 'sendMessage']);
    Route::get('chat/getList', [ChatController::class, 'getList']);
    Route::get('chat/getByIdUser', [ChatController::class, 'getByIdUser']);
    Route::get('chat/getById', [ChatController::class, 'getById']);
    Route::get('chat/getMessage', [ChatController::class, 'getMessage']);
    Route::get('online', [UserController::class, 'online']);

});
