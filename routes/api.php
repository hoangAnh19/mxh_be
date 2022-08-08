<?php

use Illuminate\Http\Request;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\PasswordResetController;
use App\Http\Controllers\api\GroupController;
use App\Http\Controllers\api\RelationshipController;
use App\Http\Controllers\api\LikeController;
use App\Http\Controllers\api\PostController;
use App\Http\Controllers\api\CommentController;
use App\Http\Controllers\api\ChatController;
use App\Http\Controllers\api\CoreValueController;
use App\Http\Controllers\api\NotificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

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
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('register', [UserController::class, 'create'])->name('user.register');
Route::post('login', [UserController::class, 'login'])->name('user.login');
Route::post('logoff', [UserController::class, 'logoff'])->name('user.logoff');
Route::post('test', [UserController::class, 'test'])->name('user.logoffs');



Route::group([
    'prefix' => 'admin'
], function () {
    Route::post('coreValue', [CoreValueController::class, 'createCoreValue']);
    Route::get('coreValue', [CoreValueController::class, 'getCoreValue']);
    Route::put('banUser', [UserController::class, 'banUser']);
    Route::put('activeUser', [UserController::class, 'activeUser']);
    Route::post('deletePostAdmin', [PostController::class, 'deletePostAdmin']);
    Route::put('assignRole', [UserController::class, 'assignRole']);
});


// Route::group(['middleware' => ['auth:api']], function () {
Route::group(['middleware' => ['auth:api']], function () {



    Route::get('userInfo', function (Request $request) {
        return $request->user();
    });
    // USER
    Route::put('user', [UserController::class, 'update'])->name('user.update');
    Route::get('user', [UserController::class, 'getUser'])->name('user.getUser');
    Route::get('user/searchUser', [UserController::class, 'searchUser']);
    Route::put('user/uploadAvatar', [UserController::class, 'uploadAvatar'])->name('user.uploadAvatar');
    Route::get('user/list_user', [UserController::class, 'listUser'])->name('user.list_user');

    // Relationship
    Route::group(['prefix' => 'relationship'], function () {


        Route::get('list_user_birth_day', [RelationshipController::class, 'listUserBirthday']);
    });


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
    Route::get('group/get_fullList_group', [GroupController::class, 'getFullListGroup']);
    Route::get('group', [GroupController::class, 'getInfo']);
    Route::get('group/roleGroup', [GroupController::class, 'getListRoleGroup']);
    Route::post('group/updateRoleGroup', [GroupController::class, 'updateRoleGroup']);
    Route::post('group/createRoleGroup', [GroupController::class, 'createRoleGroup']);



    // Route::get('group/get-list-member', [GroupController::class, 'getListMember'])->name('group.get-list-member');
    // Route::get('group/get-list-admin', [GroupController::class, 'getListAdmin'])->name('group.get-list-member');
    // Route::get('group/get-list-pending', [GroupController::class, 'getListPending'])->name('group.get-list-pending');
    //  POST


    Route::get('post/get_count', [PostController::class, 'getCountPost']);
    Route::post('image/upload', [PostController::class, 'uploadImage'])->name('post.uploadImage');
    Route::post('file/upload', [PostController::class, 'uploadFile'])->name('post.uploadFile');

    Route::post('post/create', [PostController::class, 'create'])->name('post.create');
    Route::post('post/update', [PostController::class, 'update'])->name('post.update'); //ko co
    Route::get('post/get_list', [PostController::class, 'getList'])->name('post.getList');
    Route::get('post/get_list_search', [PostController::class, 'getListSearch'])->name('post.getListSearch');

    Route::get('post/get_list_admin', [PostController::class, 'getListPostAdmin'])->name('post.getListPostAdmin');

    Route::get('post/get_list_post_browse', [PostController::class, 'getListPostBrowse']);
    Route::get('post/show', [PostController::class, 'show']);
    Route::get('image/list', [PostController::class, 'getList']);
    Route::get('post/searchPost', [PostController::class, 'searchPost']);



    //Sahre
    Route::get('share/get_list', [PostController::class, 'getListShare']);

    //Like
    Route::post('like', [LikeController::class, 'like']);
    Route::get('like/get_list', [LikeController::class, 'getListByPost']);
    //Comment
    Route::post('comment/create', [CommentController::class, 'create']);
    Route::get('comment/get', [CommentController::class, 'getComment']);

    //Notification
    Route::get('notification/get', [NotificationController::class, 'getNoti']);
    Route::post('notification/create', [NotificationController::class, 'createNoti']);
    Route::post('notification/update', [NotificationController::class, 'updateNoti']);



    //Chat
    Route::post('chat/send', [ChatController::class, 'sendMessage']);
    Route::get('chat/getList', [ChatController::class, 'getList']);
    Route::get('chat/getByIdUser', [ChatController::class, 'getByIdUser']);
    Route::get('chat/getById', [ChatController::class, 'getById']);
    Route::get('chat/getMessage', [ChatController::class, 'getMessage']);
    Route::get('online', [UserController::class, 'online']);

    //admin



});
