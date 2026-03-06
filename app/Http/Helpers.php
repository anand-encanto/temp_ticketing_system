<?php

use App\Models\Notification;
use App\Models\Activities;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\FirebaseException;

if (!function_exists('print_die')) {
    function print_die($arr = [])
    {
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
        die();
    }
}


if (!function_exists('addNotification')) {
    function addNotification($ticket_id,$trigger_event,$recipient_id,$userId, $title, $message, $status = 'unread')
    {
        $notification = new Notification();
        $notification->ticket_id        = $ticket_id;
        $notification->trigger_event    = $trigger_event;
        $notification->recipient_id     = $recipient_id;
        $notification->user_id  = $userId;
        $notification->title    = $title;
        $notification->message  = $message;
        $notification->status   = $status;
        $notification->save();

        return $notification;
    }
}

if (!function_exists('getProfileImageUrl')) {
    function getProfileImageUrl($profileImg)
    {
        $defaultProfileImg = 'default/avatar.png';
        $path = $profileImg ?? $defaultProfileImg;

        return url('public/profiles/' . $path);
    }
}
?>
