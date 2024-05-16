<?php

use App\Http\Controllers\BusinessAndPersonalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TodoController;
use Illuminate\Http\Request;

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
/*
|--------------------------------------------------------------------------
| Mou APP
|--------------------------------------------------------------------------
|
*/
/**
 * Only check auth:api - Check firebase token
 */
Route::group(['prefix' => 'v1', 'middleware' => 'auth:api'], function () {
    //Auth
    Route::post('/register', 'Auth\RegisterController@register');

    //    Route::get('/token-info', function(Request $request){
    //        $userFirebase = $request->user();
    //        $authTime     = (string) $userFirebase->getAll()['auth_time'];
    //
    //        $exp = (string) $userFirebase->getAll()['exp'];
    ////    return $userFirebase->getAll();
    //        $expiresAt = new DateTime();
    //        $expiresAt->setTimestamp($exp);
    //        $authAt = new DateTime();
    //        $authAt->setTimestamp($authTime);
    //
    ////    return $authAt->format('d/m/Y H:i:s') . " -> ".$expiresAt->format('d/m/Y H:i:s');
    //        return response()->json((array) Auth::user());
    //    });
});
/**
 * auth:api - Check firebase token
 * existUserPhone - Check exist user with phone number (phone get on firebase token)
 */
Route::group(['prefix' => 'v1', 'middleware' => ['auth:api', 'existUserPhone']], function () {
    //User
    Route::get('/exist-phone', 'UserController@existPhone');
    Route::get('/me', 'UserController@me');
    Route::post('/me/profile', 'UserController@updateMe');
    Route::post('/me/avatar', 'UserController@updateMeAvatar');
    Route::post('/me/setting', 'UserController@setting');
    Route::get('/user/search', 'UserController@search');
    Route::delete('user/destroy', 'UserController@destroy');
    //FCM
    Route::post('/me/fcm-token', 'UserController@saveFCMToken');
    Route::delete('/me/fcm-token/{token}', 'UserController@destroyFCMToken');

    //Facebook
    Route::post('/facebook/connect', 'UserController@connectFacebook');
    Route::post('/facebook/import', 'UserController@importFriendsFacebook');

    // Contacts
    Route::post('/contacts/import-contacts', 'ContactController@importContacts');
    Route::delete('/contacts/{id}', 'ContactController@destroy');
    Route::post('/contacts/{id}/edit', 'ContactController@update');
    Route::post('/contacts', 'ContactController@addContact');
    Route::get('/contacts', 'ContactController@all');
    Route::post('/contacts/{id}/link', 'ContactController@linkContact');
    Route::post('/contacts/addConnect/{id}', 'ContactController@addConnect');

    //Route::get('/test/add-friend/{id}', 'UserController@addFriend');

    //Event
    Route::get('/event/alarm-device', 'EventController@eventAlarmOnDevice');
    Route::get('/event/month', 'EventController@checkEventDateOfMonth');
    Route::get('/event/date', 'EventController@indexByDate');
    Route::get('/event/status/for-you-to-confirm', 'EventController@indexForYouToConfirm');
    Route::get('/event/status/waiting-to-confirm', 'EventController@indexWaitingToConfirm');
    Route::get('/event/status/confirmed', 'EventController@indexConfirmed');
    Route::get('/event/status/denied', 'EventController@indexDenied');
    Route::post('/event/{id}/chat/add', 'EventController@addRoomChat');
    Route::post('/event/send-sms', 'EventController@sendSms');

    Route::get('/event/status/count', 'EventController@countEventStatus');

    Route::post('/event', 'EventController@store');

    Route::post('/event/{id}/update', 'EventController@update');
    Route::post('/event/{id}/confirm', 'EventController@confirm');
    Route::post('/event/{id}/deny', 'EventController@deny');
    Route::post('/event/{id}/leave', 'EventController@leave');
    Route::delete('/event/{id}', 'EventController@destroy');

    Route::post('/feedback', 'FeedbackController@store');

    Route::post('/send-notify', 'EventController@sendNotify');

    //Todo
    Route::post('/todo/order', [TodoController::class, 'orderTodo']);
    Route::get('/todo/{id}', [TodoController::class, 'show']);
    Route::get('/todo/group/{id}', [TodoController::class, 'getChildren']);
    Route::get('/todo', [TodoController::class, 'index']);
    Route::post('/todo/{id}', [TodoController::class, 'update']);
    Route::post('/todo', [TodoController::class, 'store']);
    Route::delete('/todo/{id}', [TodoController::class, 'destroy']);
    Route::post('/todo/{id}/completed', [TodoController::class, 'completedTodo']);
    Route::post('/todo/{id}/overline', [TodoController::class, 'overlineTodo']);
});
//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

/*
|--------------------------------------------------------------------------
| Mou Business APP
|--------------------------------------------------------------------------
|
*/
/**
 * Only check auth:api - Check firebase token
 */
Route::group(['prefix' => 'v1', 'middleware' => 'auth:api'], function () {
    //Auth
    Route::post('/business/register-company', 'CompanyController@registerCompany'); //Flow register company: 2. register company

    // update new phone number
    Route::patch('/change-phone', 'UserController@changePhone');
});
/**
 * auth:api - Check firebase token
 * existUserPhone - Check exist user with phone number (phone get on firebase token)
 */
Route::group(['prefix' => 'v1', 'middleware' => ['auth:api', 'existUserPhone', 'language']], function () {
    /*
     * PERSONAL APP
     */
    // Company
    Route::get('/business/personal/company-invite', 'CompanyController@invitedToMe');
    Route::post('/business/personal/company-invite/{id}/accept', 'CompanyController@acceptInvitedToMe');
    Route::post('/business/personal/company-invite/{id}/deny', 'CompanyController@denyInvitedToMe');
    // Project
    Route::get('/business/personal/project/open', 'ProjectController@getProjectOpenOfEmployee');
    Route::get('/business/personal/project/in-progress', 'ProjectController@getProjectInProgressOfEmployee');
    Route::get('/business/personal/project/done', 'ProjectController@getProjectDoneOfEmployee');
    Route::post('/business/personal/project/{project_id}/leave', 'ProjectController@leaveProjectByEmployee');
    Route::get('/business/personal/project/{project_id}', 'ProjectController@detailProjectByEmployee');
    Route::post('/business/personal/project/{project_id}/chat/update', 'ProjectController@updateRoomChat');

    Route::post('/business/personal/event-task/{task_id}/done', 'EventController@doneTask');

    /*
     * BUSINESS APP
     */

    //FCM
    Route::post('/business/me/fcm-token', 'UserController@saveFCMTokenBusinessApp');
    Route::delete('/business/me/fcm-token/{token}', 'UserController@destroyFCMToken');

    //User
    Route::get('/business/exist-phone', 'CompanyController@existPhone'); //Flow register company: 1. check phone => NEED_REGISTER_COMPANY go to Step 2 (require personal account), OK go to step 3
    Route::get('/business/me', 'CompanyController@me'); //Flow register company: 3. Get info

    Route::post('/business/me/company-profile', 'CompanyController@updateCompanyProfile')->name('api.company.update_profile');
    Route::post('/business/me/company-logo', 'CompanyController@updateCompanyLogo')->name('api.company.update_logo');
    Route::post('/business/me/working-days', 'CompanyController@workingDays');

    /*
     * EMPLOYEE
     */
    Route::get('/business/{company_id}/employee', 'CompanyController@employees');
    Route::post('/business/{company_id}/employee', 'CompanyController@addEmployee');
    Route::post('/business/{company_id}/employee/{employee_id}/edit', 'CompanyController@updateEmployee');
    Route::delete('/business/{company_id}/employee/{employee_id}', 'CompanyController@destroyEmployee');

    /*
     * Project list
     */
    Route::get('/business/{company_id}/project/open', 'ProjectController@getProjectOpen');
    Route::get('/business/{company_id}/project/in-progress', 'ProjectController@getProjectInProgress');
    Route::get('/business/{company_id}/project/done', 'ProjectController@getProjectDone');

    /**
     * Task list
     */
    Route::get('/business/{company_id}/task/open', 'TaskController@getTaskOpen');
    Route::get('/business/{company_id}/task/in-progress', 'TaskController@getTaskInProgress');
    Route::get('/business/{company_id}/task/done', 'TaskController@getTaskDone');

    /*
     * PROJECT
     */
    Route::post('/business/{company_id}/project', 'ProjectController@store');
    Route::post('/business/{company_id}/project/{project_id}/edit', 'ProjectController@update');
    Route::get('/business/{company_id}/project/{project_id}', 'ProjectController@detail');
    Route::delete('/business/{company_id}/project/{project_id}', 'ProjectController@destroy');
    // Task of project
    Route::delete('/business/{company_id}/project/{project_id}/task/{task_id}', 'ProjectController@destroyTask');
    Route::post('/business/{company_id}/project/{project_id}/task', 'ProjectController@createTask');
    Route::post('/business/{company_id}/project/{project_id}/task/{task_id}/edit', 'ProjectController@updateTask');

    /*
     * TASK
     */
    Route::post('/business/{company_id}/task', 'TaskController@store');
    Route::post('/business/{company_id}/task/{task_id}/edit', 'TaskController@update');
    Route::get('/business/{company_id}/task/{task_id}', 'TaskController@detail');
    Route::delete('/business/{company_id}/task/{task_id}', 'TaskController@destroy');

    // get list roster
    Route::get('business/rosters', 'RosterController@index');
    // create roster
    Route::post('business/rosters/create', 'RosterController@create');
    // update roster by id
    Route::post('business/rosters/{id}/update', 'RosterController@update');
    // get status by week/month roster
    Route::get('business/rosters/status', 'RosterController@statusByWeekMonth');
    // get roster by id
    Route::get('business/rosters/{id}', 'RosterController@show');
    // delete roster by id
    Route::delete('business/rosters/{id}/delete', 'RosterController@destroy');

    // employee accept request add roster
    Route::post('personal/rosters/{id}/accept', 'RosterController@acceptRoster');
    // employee decline request add roster
    Route::post('personal/rosters/{id}/decline', 'RosterController@declineRoster');

    // Store management
    Route::resource('business/stores', StoreController::class)->except(['show', 'create', 'edit']);

    // Notification Personal
    Route::get('notifications', [NotificationController::class, 'list']);
    Route::get('notifications/count', [NotificationController::class, 'count']);
    //merge project and task to 1 api
    Route::get('/business/general ', [BusinessAndPersonalController::class, 'getListByBusiness']);
    Route::get('/personal/general ', [BusinessAndPersonalController::class, 'getListByPersonal']);
});

/** Group prefix v1 without authenticate */
Route::prefix('v1')->group(function () {

    /** Group change phone */
    Route::prefix('change-phone')->group(function () {
        // send mail verify before change phone number
        Route::post('send-mail', 'Auth\LoginController@sendVerifyEmailChangePhone');
    });
});
