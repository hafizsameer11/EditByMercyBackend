<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\QuestionareController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return response()->json(['message' => 'unauthenticated'], 401);
})->name('login');
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
    Route::post('/verify-code', [AuthController::class, 'verifyCode']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/send-message', [ChatController::class, 'sendMessage']);
    Route::post('/assign-agent', [ChatController::class, 'assignAgent']);
    Route::get('/chat/{id}', [ChatController::class, 'getChatMessages']);
    Route::get('/chats', [ChatController::class, 'getChats']);
});
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::post('/create-user', [UserController::class, 'createUser']);
    Route::post('/questionnaire', [QuestionareController::class, 'storeOrUpdateQuestionnaire']);
    Route::get('questionnaire', [QuestionareController::class, 'getQuestionnaire']);

});
