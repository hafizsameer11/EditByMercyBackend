<?php

use App\Models\Notification;

class NotifcationHelper {
    public static function create($data){
        $notification =Notification::create($data);
        return $notification;
    }
}