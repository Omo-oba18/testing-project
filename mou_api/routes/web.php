<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/demo', function () {
    return view('demo');
});
//Route::get('/sendmail', function () {
//    $credentials = ['email' => 'khacnha.it@gmail.com'];
//    $response = Password::sendResetLink($credentials, function (Message $message) {
//        $message->subject($this->getEmailSubject());
//    });
//    return $response;
//});
Auth::routes();

//Route::get('/home', 'HomeController@index')->name('home');
//Route::get('/event/for-you-to-confirm', 'EventController@indexForYouToConfirm');
//Route::get('/event/waiting-to-confirm', 'EventController@indexWaitingToConfirm');
//Route::get('/event/confirmed', 'EventController@indexConfirmed');
//Route::get('/event/date', 'EventController@indexByDate');
//Route::get('/event/status/count', 'EventController@countEventStatus');
//Route::post('/event', 'EventController@store');
//Route::get('/event/month', 'EventController@checkEventDateOfMonth');
//Route::get('/user/search', 'UserController@search');
//Route::get('/contacts', 'ContactController@index');
//
Route::get('/test', function () {
    \Mail::send('welcome', [], function ($message) {
        $message->to('levantan.vn.it@gmail.com')->subject('Testing mails');
    });
    dd('OK');
});

Route::get('/exports/report/{id}', 'FeedbackController@report')->name('exports.report');
Route::get('/exports/report/project-task/{id}', 'FeedbackController@reportProjectTask')->name('exports.reportProjectTask');

Route::get('/avatar/{id}', 'UserController@getAvatar')->name('user.avatar');
