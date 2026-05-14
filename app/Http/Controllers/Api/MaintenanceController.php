<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController as BaseController;
use App\Models\Tickets;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class MaintenanceController extends BaseController
{
    /**
     * Scan all open tickets and flag those that have breached their SLA resolution time.
     * This endpoint should be triggered every 30-60 minutes by an external cron service.
     */
    public function checkSlaBreaches(Request $request)
    {
        $openStatuses = ['New', 'Assigned', 'In Progress', 'Waiting for Parts'];
        
        // 1. Handle initial breaches
        $newBreaches = Tickets::whereIn('status', $openStatuses)
            ->where('due_at', '<', Carbon::now())
            ->where('is_overdue', 0)
            ->get();

        foreach ($newBreaches as $ticket) {
            $ticket->is_overdue = 1;
            $ticket->last_reminder_sent_at = Carbon::now();
            $ticket->save();

            logTicketHistory($ticket->id, null, [
                'change_type' => 'sla_breach',
                'message'     => 'SLA Resolution Time Breached! Initial reminder triggered.'
            ]);

            $this->sendSlaReminders($ticket);
        }

        // 2. Handle repeated reminders for overdue tickets
        $overdueTickets = Tickets::whereIn('status', $openStatuses)
            ->where('is_overdue', 1)
            ->get();

        $reminderCount = 0;
        foreach ($overdueTickets as $ticket) {
            // Get SLA settings for this priority
            $sla = \App\Models\SlaLevel::where('priority', $ticket->priority)->first();
            $interval = $sla->reminder_interval_minutes ?? 60; // default 1 hour

            $lastSent = $ticket->last_reminder_sent_at ? Carbon::parse($ticket->last_reminder_sent_at) : Carbon::parse($ticket->due_at);
            
            if ($lastSent->addMinutes($interval)->isPast()) {
                $this->sendSlaReminders($ticket);
                $ticket->last_reminder_sent_at = Carbon::now();
                $ticket->save();
                $reminderCount++;
            }
        }

        return $this->sendResponse([
            'initial_breaches' => $newBreaches->count(),
            'repeated_reminders' => $reminderCount,
            'time' => Carbon::now()->toDateTimeString()
        ], "SLA Audit complete.");
    }

    /**
     * Send email reminders to the Assignee and the Department Head.
     */
    private function sendSlaReminders($ticket)
    {
        $recipients = collect([]);

        // 1. Assignee (if any)
        if ($ticket->assignee_id) {
            $assignee = User::find($ticket->assignee_id);
            if ($assignee && $assignee->email) {
                $recipients->push($assignee);
            }
        }

        // 2. Department Head
        $deptHeads = User::where('department_id', $ticket->department_id)
            ->where('role', 'department_head')
            ->get();
        $recipients = $recipients->merge($deptHeads);

        // Remove duplicates and nulls
        $recipients = $recipients->filter()->unique('id');

        foreach ($recipients as $user) {
            $mailData = [
                'email'         => $user->email,
                'subject'       => "URGENT: Ticket #{$ticket->id} SLA Breached",
                'page'          => 'email.sla_reminder',
                'ticket_id'     => $ticket->id,
                'ticket_title'  => $ticket->title,
                'due_at'        => $ticket->due_at,
                'user_name'     => $user->name,
            ];

            // Use the static mailer in TicketController if available, or call directly
            // For now, assuming TicketController::send_mail is accessible or use internal
            $this->mailSender($mailData);
        }
    }

    private function mailSender($data)
    {
        Mail::send($data['page'], $data, function ($message) use ($data) {
            $message->to($data['email'])
                ->subject($data['subject'])
                ->from(env('MAIL_FROM_ADDRESS', 'support@mcdmauritius.com'), config('app.name'));
        });
    }
}
