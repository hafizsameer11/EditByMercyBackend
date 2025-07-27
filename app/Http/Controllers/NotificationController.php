<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(){
        $user=Auth::user();
        $notifications=Notification::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        return ResponseHelper::success($notifications, "Notifications fetched successfully.");
    }
    public function markAsRead($id){
        $notification=Notification::find($id);
        if($notification){
            $notification->is_read=1;
            $notification->save();
            return ResponseHelper::success(null, "Notification marked as read successfully.");
        }
        return ResponseHelper::error("Notification not found.", 404);
    }
    public function count(){
        $user=Auth::user();
        $count=Notification::where('user_id', $user->id)->where('is_read', 0)->count();
        return ResponseHelper::success($count, "Notification count fetched successfully.");
    }
}
