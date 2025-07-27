<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\QuickReplyController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QuestionareController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return response()->json(['message' => 'unauthenticated'], 401);
})->name('login');
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('/optimize-app', function () {
    Artisan::call('optimize:clear'); // Clears cache, config, route, and view caches
    Artisan::call('cache:clear');    // Clears application cache
    Artisan::call('config:clear');   // Clears configuration cache
    Artisan::call('route:clear');    // Clears route cache
    Artisan::call('view:clear');     // Clears compiled Blade views
    Artisan::call('config:cache');   // Rebuilds configuration cache
    Artisan::call('route:cache');    // Rebuilds route cache
    Artisan::call('view:cache');     // Precompiles Blade templates
    Artisan::call('optimize');       // Optimizes class loading

    return "Application optimized and caches cleared successfully!";
});
Route::get('/migrate', function () {
    Artisan::call('migrate');
    return response()->json(['message' => 'Migration successful'], 200);
});
Route::get('/migrate/rollback', function () {
    Artisan::call('migrate:rollback');
    return response()->json(['message' => 'Migration rollback successfully'], 200);
});
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
    Route::post('/verify-code', [AuthController::class, 'verifyCode']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});
Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('edit-profile', [AuthController::class, 'editProfile']);

    Route::post('/send-message', [ChatController::class, 'sendMessage']);
    Route::post('/assign-agent', [ChatController::class, 'assignAgent']);
    Route::get('/chat/{id}', [ChatController::class, 'getChatMessages']);
    Route::get('/chats', [ChatController::class, 'getChats']);
    //routes for handling payment with order
    Route::post('/create-payment', [ChatController::class, 'createPayment']);
    // Route::post('/check-current-order')
    //routes for questionare for user and agent
    Route::post('questionnaire/assign', [QuestionareController::class, 'assignToUser']);
    Route::post('questionnaire/user/section', [QuestionareController::class, 'submitSection']);
    Route::post('questionnaire/user/answers', [QuestionareController::class, 'getUserAnswers']);
    Route::post('questionnaire/assignment/close', [QuestionareController::class, 'closeAssignment']);
    Route::post('questionnaire/assignment/reopen', [QuestionareController::class, 'reopenAssignment']);
    Route::get('questionnaire/assignment/answers/{user_id}', [QuestionareController::class, 'getAnswersByUser']);
    Route::get('questionnaire/assignment/progress/{assignment_id}', [QuestionareController::class, 'getAssignmentProgress']);
    Route::get('questionnaire/get-assigned-form', [QuestionareController::class, 'getAssignedForm']);
    //quick reply 
    Route::get('/quick-replies', [QuickReplyController::class, 'index']);
    Route::post('/quick-replies', [QuickReplyController::class, 'store']);
    Route::put('/quick-replies/{id}', [QuickReplyController::class, 'update']);
    Route::delete('/quick-replies/{id}', [QuickReplyController::class, 'destroy']);
    //feed creation
    Route::get('/feeds', [FeedController::class, 'index']);
    Route::post('/feeds/{feedId}/toggle-like', [FeedController::class, 'toggleLike']);
    Route::post('/feeds/store', [FeedController::class, 'store']);
    //order routes for users
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/order/{id}', [OrderController::class, 'orderDetails']);
    //aggent to agent chat

    Route::get('/non-users', [ChatController::class, 'getNonUsers']);
    Route::get('/open-agent-chat/{id}', [ChatController::class, 'getChatWithUserByUserId']);

    //notification routes
    Route::get('/get-notifications', [NotificationController::class, 'index']);
    Route::get('/get-notifications-count', [NotificationController::class, 'count']);
    Route::post('/mark-notification-as-read/{id}', [NotificationController::class, 'markAsRead']);
});
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::post('/create-user', [UserController::class, 'createUser']);
    Route::post('/questionnaire', [QuestionareController::class, 'storeOrUpdateQuestionnaire']);
    Route::get('questionnaire', [QuestionareController::class, 'getQuestionnaire']);
});

// for messages first call /assign-agent then call /send-message and for chat details you can use /chat/{id} and for all chats you can use /chats