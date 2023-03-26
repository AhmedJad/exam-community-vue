<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

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

Route::prefix("auth")->group(function () {
    Route::post("register", "AuthController@register");
    Route::post("login", "AuthController@login");
    Route::get("verify-token", "AuthController@verifyToken");
    Route::post("verify-email", "AuthController@verifyEmail");
    Route::get("resend-verification-code", "AuthController@resendVerificationCode");
    Route::get("user-verified", "AuthController@userVerified");
    Route::get('forget-password/{user:email}', "AuthController@forgetPassword");
    Route::post('reset-password', "AuthController@resetPassword");
    Route::get("logout", "AuthController@logout");
    Route::get('current-user', "AuthController@getCurrentUser");
    Route::post('update-profile', "AuthController@updateProfile");
});

Route::prefix("hellos")->group(function () {
    Route::post("", "HelloController@store");
    Route::post("update", "HelloController@update");
    Route::delete("{id}", "HelloController@delete");
    Route::get("", "HelloController@index");
});

Route::prefix("many-image")->group(function () {
    Route::post("", "ManyImageController@store");
    Route::post("update", "ManyImageController@update");
    Route::post("add-image", "ManyImageController@addImage");
    Route::delete("delete-image", "ManyImageController@deleteImage");
    Route::delete("{id}", "ManyImageController@delete");
    Route::get("", "ManyImageController@index");
});

Route::prefix("chat")->group(function () {
    Route::get("rooms", "ChatController@getRooms");
    Route::get("messages/{roomId}", "ChatController@getMessages");
    Route::post("", "ChatController@newMesage");
});

Route::prefix("folders")->group(function () {
    Route::post("", "FolderController@store");
    Route::post("update", "FolderController@update");
    Route::delete("{id}", "FolderController@delete");
    Route::get("", "FolderController@index");
});


Route::prefix("exams")->group(function () {
    Route::post("", "ExamAdminController@store");
    Route::post("update", "ExamAdminController@update");
    Route::delete("{id}", "ExamAdminController@delete");
    Route::get("", "ExamAdminController@index");
});

Route::prefix("exam-solutions")->group(function () {
    Route::get("", "ExamSolutionController@index");
    Route::post("solve", "ExamSolutionController@solve");
    Route::get("folders", "ExamSolutionController@getFolders");
    Route::get("exams", "ExamSolutionController@getExams");
});
