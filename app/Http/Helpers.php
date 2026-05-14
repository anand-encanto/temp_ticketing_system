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
if (!function_exists('logTicketHistory')) {
    function logTicketHistory($ticketId, $userId, $data = [])
    {
        return \App\Models\TicketHistory::create([
            'ticket_id'       => $ticketId,
            'user_id'         => $userId,
            'status_from'     => $data['status_from'] ?? null,
            'status_to'       => $data['status_to'] ?? null,
            'assignment_from' => $data['assignment_from'] ?? null,
            'assignment_to'   => $data['assignment_to'] ?? null,
            'priority_from'   => $data['priority_from'] ?? null,
            'priority_to'     => $data['priority_to'] ?? null,
            'change_type'     => $data['change_type'] ?? 'status_change',
            'message'         => $data['message'] ?? null,
        ]);
    }
}

/**
 * Calculate and set the due_at timestamp for a ticket based on its priority SLA
 */
if (!function_exists('calculateTicketDueDate')) {
    function calculateTicketDueDate($ticketId) {
        $ticket = \App\Models\Tickets::find($ticketId);
        if (!$ticket) return null;

        $sla = \App\Models\SlaLevel::where('priority', $ticket->priority)->first();
        if (!$sla) return null;

        // Calculate resolution deadline (resolution_time_minutes)
        $dueAt = \Illuminate\Support\Carbon::parse($ticket->created_at)->addMinutes($sla->resolution_time_minutes);
        
        $ticket->due_at = $dueAt;
        $ticket->save();

        return $dueAt;
    }
}
?>
