<?php

use App\Http\Controllers\Admin\BannersController;
use App\Http\Controllers\Admin\ChatsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ManageAdminController;
use App\Http\Controllers\Admin\NotificationsController;
use App\Http\Controllers\Admin\OrdersController;
use App\Http\Controllers\Admin\QuestionnaireManagementController;
use App\Http\Controllers\Admin\TransactionsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\QuestionareController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin API routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group with sanctum authentication.
|
*/

Route::middleware(['auth:sanctum'])->group(function () {

    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/analytics', [DashboardController::class, 'analytics']); // Detailed analytics (users, orders, revenue)

        // ============================================
        // User Management Routes
        // ============================================
        Route::prefix('users')->group(function () {
            Route::get('/', [UserManagementController::class, 'index']); // Get all users with stats
            Route::get('/{id}', [UserManagementController::class, 'show']); // Get single user details
            Route::post('/', [UserManagementController::class, 'store']); // Create new user
            Route::put('/{id}', [UserManagementController::class, 'update']); // Update user
            Route::delete('/{id}', [UserManagementController::class, 'destroy']); // Delete user
            Route::post('/{id}/toggle-block', [UserManagementController::class, 'toggleBlock']); // Block/Unblock user

            // User specific data
            Route::get('/{id}/chats', [UserManagementController::class, 'getUserChats']); // Get user's chats
            Route::get('/{id}/orders', [UserManagementController::class, 'getUserOrders']); // Get user's orders
            Route::get('/{id}/activity', [UserManagementController::class, 'getUserActivity']); // Get user's activity
        });

        // Legacy user creation endpoint (kept for backward compatibility)
        Route::post('/create-user', [UserController::class, 'createUser']);

        // ============================================
        // Chats Management Routes
        // ============================================
        Route::prefix('chats')->group(function () {
            Route::get('/', [ChatsController::class, 'index']); // Get all chats
            Route::get('/{id}', [ChatsController::class, 'show']); // Get single chat with messages
            Route::delete('/{id}', [ChatsController::class, 'destroy']); // Delete chat

            // Chat actions
            Route::post('/{id}/new-order', [ChatsController::class, 'createNewOrder']); // Create new order in chat
            Route::get('/available/list', [ChatsController::class, 'getAvailableChats']); // Get chats for sharing
            Route::post('/share', [ChatsController::class, 'shareToChat']); // Share/forward to chat
        });

        // ============================================
        // Orders Management Routes
        // ============================================
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrdersController::class, 'index']); // Get all orders with stats
            Route::get('/{id}', [OrdersController::class, 'show']); // Get single order details
            Route::put('/{id}', [OrdersController::class, 'update']); // Update order details
            Route::delete('/{id}', [OrdersController::class, 'destroy']); // Delete order

            // Order status updates
            Route::patch('/{id}/status', [OrdersController::class, 'updateStatus']); // Update order status
            Route::patch('/{id}/payment-status', [OrdersController::class, 'updatePaymentStatus']); // Update payment status

            // Bulk actions
            Route::post('/bulk-update', [OrdersController::class, 'bulkUpdate']); // Bulk update orders
        });

        // ============================================
        // Transactions Management Routes
        // ============================================
        Route::prefix('transactions')->group(function () {
            Route::get('/', [TransactionsController::class, 'index']); // Get all transactions with stats
            Route::get('/{id}', [TransactionsController::class, 'show']); // Get single transaction
            Route::patch('/{id}/status', [TransactionsController::class, 'updateStatus']); // Update status
            Route::get('/export/data', [TransactionsController::class, 'export']); // Export transactions
        });

        // ============================================
        // Manage Admin Routes
        // ============================================
        Route::prefix('manage-admin')->group(function () {
            Route::get('/', [ManageAdminController::class, 'index']); // Get all admin users with stats
            Route::get('/{id}', [ManageAdminController::class, 'show']); // Get single admin
            Route::post('/', [ManageAdminController::class, 'store']); // Create new admin
            Route::put('/{id}', [ManageAdminController::class, 'update']); // Update admin
            Route::delete('/{id}', [ManageAdminController::class, 'destroy']); // Delete admin
            Route::post('/bulk-action', [ManageAdminController::class, 'bulkAction']); // Bulk actions
        });

        // ============================================
        // Notifications Management Routes
        // ============================================
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationsController::class, 'index']); // Get all sent notifications
            Route::post('/send', [NotificationsController::class, 'send']); // Send new notification
            Route::get('/templates', [NotificationsController::class, 'getTemplates']); // Get templates
            Route::get('/users', [NotificationsController::class, 'getUsers']); // Get users for targeting
            Route::delete('/{id}', [NotificationsController::class, 'destroy']); // Delete notification
        });

        // ============================================
        // Banners/Feed Management Routes
        // ============================================
        Route::prefix('banners')->group(function () {
            Route::get('/', [BannersController::class, 'index']); // Get all banners
            Route::get('/{id}', [BannersController::class, 'show']); // Get single banner
            Route::post('/', [BannersController::class, 'store']); // Create new banner
            Route::put('/{id}', [BannersController::class, 'update']); // Update banner
            Route::delete('/{id}', [BannersController::class, 'destroy']); // Delete banner

            // Categories
            Route::get('/categories/list', [BannersController::class, 'getCategories']); // Get categories
            Route::post('/categories/create', [BannersController::class, 'createCategory']); // Create category
        });

        // ============================================
        // Questionnaire Management Routes
        // ============================================
        Route::prefix('questionnaire-management')->group(function () {
            // Questionnaire Categories CRUD
            Route::get('/', [QuestionnaireManagementController::class, 'index']); // Get all questionnaires with stats
            Route::get('/{id}', [QuestionnaireManagementController::class, 'show']); // Get single questionnaire
            Route::post('/', [QuestionnaireManagementController::class, 'store']); // Create new questionnaire
            Route::put('/{id}', [QuestionnaireManagementController::class, 'update']); // Update questionnaire
            Route::delete('/{id}', [QuestionnaireManagementController::class, 'destroy']); // Delete questionnaire
            Route::post('/{id}/toggle-status', [QuestionnaireManagementController::class, 'toggleStatus']); // Toggle active status

            // Questions Management
            Route::post('/{id}/questions', [QuestionnaireManagementController::class, 'addQuestion']); // Add question to questionnaire
            Route::put('/questions/{questionId}', [QuestionnaireManagementController::class, 'updateQuestion']); // Update question
            Route::delete('/questions/{questionId}', [QuestionnaireManagementController::class, 'deleteQuestion']); // Delete question
            Route::post('/{id}/reorder-questions', [QuestionnaireManagementController::class, 'reorderQuestions']); // Reorder questions

            // Helper Routes
            Route::get('/meta/question-types', [QuestionnaireManagementController::class, 'getQuestionTypes']); // Get available question types
        });

        // ============================================
        // Old Questionnaire Routes (Legacy - Keep for backward compatibility)
        // ============================================
        Route::prefix('questionnaire')->group(function () {
            Route::post('/', [QuestionareController::class, 'storeOrUpdateQuestionnaire']);
            Route::get('/', [QuestionareController::class, 'getQuestionnaire']);
        });
    });
    // ============================================
    // Dashboard Routes
    // ============================================

});
