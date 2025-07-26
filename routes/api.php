<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\QuickReplyController;
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
    //routes for questionare for user and agent
    Route::post('questionnaire/assign', [QuestionareController::class, 'assignToUser']);
    Route::post('questionnaire/user/section', [QuestionareController::class, 'submitSection']);
    Route::post('questionnaire/user/answers', [QuestionareController::class, 'getUserAnswers']);
    Route::post('questionnaire/assignment/close', [QuestionareController::class, 'closeAssignment']);
    Route::post('questionnaire/assignment/reopen', [QuestionareController::class, 'reopenAssignment']);
    Route::get('questionnaire/assignment/answers/{user_id}', [QuestionareController::class, 'getAnswersByUser']);
    Route::get('questionnaire/assignment/progress/{assignment_id}', [QuestionareController::class, 'getAssignmentProgress']);
    Route::get('/questionnaire/get-assigned-form', [QuestionareController::class, 'getAssignedForm']); //for user to get assigned form

    //quick reply 
    Route::get('/quick-replies', [QuickReplyController::class, 'index']);
    Route::post('/quick-replies', [QuickReplyController::class, 'store']);
    Route::put('/quick-replies/{id}', [QuickReplyController::class, 'update']);
    Route::delete('/quick-replies/{id}', [QuickReplyController::class, 'destroy']);

    //feed creation
    Route::get('/feeds', [FeedController::class, 'index']);
    Route::post('/feeds/{feedId}/toggle-like', [FeedController::class, 'toggleLike']);
    Route::post('/feeds/store', [FeedController::class, 'store']);
});
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::post('/create-user', [UserController::class, 'createUser']);
    Route::post('/questionnaire', [QuestionareController::class, 'storeOrUpdateQuestionnaire']);
    Route::get('questionnaire', [QuestionareController::class, 'getQuestionnaire']);
});

// for messages first call /assign-agent then call /send-message and for chat details you can use /chat/{id} and for all chats you can use /chats