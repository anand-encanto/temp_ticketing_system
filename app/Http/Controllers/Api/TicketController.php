<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController as BaseController;
use App\Models\Notification;
use App\Models\TicketImages;
use App\Models\Tickets;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Validator;

class TicketController extends BaseController
{



    public function createTicket(Request $request)
    {
        $user = Auth::guard('api')->user();

        $validator = Validator::make($request->all(), [
            'title'                    => 'required|string|max:255',
            'description'              => 'required|string',
            'reported_by'              => 'required|string|max:255',
            'department_id'            => 'required|exists:departments,id',
            'location_id'              => 'required|exists:locations,id',
            'priority'                 => 'required|in:Low,Medium,High,Urgent',
            'issue'                    => 'required|string',
            'contact_person'           => 'nullable|string|max:255',
            'contact_number'           => 'nullable|string|max:20',
            'assignee_id'              => 'nullable|exists:users,id',
            'expected_resolution_time' => 'nullable|date',
            'secondary_contact_id'     => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status'  => 422,
                'error'   => true,
            ], 422);
        }

        // CREATE TICKET
        $model                = new Tickets();
        $model->title         = $request->title;
        $model->description   = $request->description;
        $model->reported_by   = $request->reported_by;
        $model->department_id = $request->department_id;
        $model->location_id   = $request->location_id;
        $model->priority      = $request->priority;
        $model->submitter_id  = $user->id;
        $model->issue         = $request->issue;
        $model->status        = 'New';

        // VIDEO upload
        if ($request->hasFile('video')) {
            $video     = $request->file('video');
            $directory = public_path('uploads/tickets');
            $fileName  = time() . '_' . $video->getClientOriginalName();

            if (! file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
            $video->move($directory, $fileName);

            $model->video = $fileName;
        }

        $model->save();

        // LOG HISTORY: Ticket Created
        logTicketHistory($model->id, $user->id, [
            'status_to'   => 'New',
            'change_type' => 'creation',
            'message'     => 'Ticket created by ' . $user->name
        ]);

        // SLA: Calculate and set Due Date
        calculateTicketDueDate($model->id);

        // IMAGE upload
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $directory = public_path('uploads/tickets');
                $imagePath = $this->uploadFile($image, $directory);

                TicketImages::create([
                    'ticket_id'  => $model->id,
                    'image_path' => $imagePath,
                ]);
            }
        }

        /* =====================================================
              SEND NOTIFICATIONS (already present)
           ===================================================== */
        $title     = 'Ticket Submitted';
        $deptUsers = User::where('department_id', $request->department_id)->get();

        foreach ($deptUsers as $deptUser) {
            Notification::create([
                'user_id'       => $deptUser->id,
                'ticket_id'     => $model->id,
                'trigger_event' => 'Ticket Created',
                'title'         => "New Ticket Created",
                'message'       => "A new ticket {$title} has been created in your department.",
            ]);
        }

        addNotification(
            $model->id,
            'Ticket Created',
            null,
            $user->id,
            'Ticket Submitted',
            'Hello ' . $user->name . ', your ticket has been submitted successfully',
            'unread'
        );

        /* =====================================================
                SEND EMAIL TO ALL CONCERNED USERS
           ===================================================== */

        $emailUsers = collect([]);

        // 1️⃣ Submitter
        if ($user) {
            $emailUsers->push($user);
        }

        // 2️⃣ Admin users
        $admins     = User::where('role', 'admin')->get();
        $emailUsers = $emailUsers->merge($admins);

        // 3️⃣ Department Head
        $deptHeads = User::where('department_id', $model->department_id)
            ->where('role', 'department_head')
            ->get();
        $emailUsers = $emailUsers->merge($deptHeads);

        // OPTIONAL: MAIL TO ASSIGNEE
        /*
        if ($request->assignee_id) {
            $assignee = User::find($request->assignee_id);
            if ($assignee) {
                $emailUsers->push($assignee);
            }
        }
        */

        // REMOVE DUPLICATES + NULL
        $emailUsers = $emailUsers->filter()->unique('id');

        foreach ($emailUsers as $emailUser) {
            if (! $emailUser->email) {
                continue;
            }

            $mailData = [
                'email'              => $emailUser->email,
                'subject'            => "New Ticket #" . $model->id . " Created",
                'page'               => 'email.ticket_create',
                'ticket_id'          => $model->id,
                'ticket_title'       => $model->title,
                'ticket_status'      => $model->status,
                'ticket_description' => $model->description,
                'submitter_name'     => $user->name,
            ];

            self::send_mail($mailData);
        }

        return $this->sendResponse($model, 'Ticket Submitted');
    }



    public function getMyTickets(Request $request)
    {
        $user = Auth::guard('api')->user();

        try {
            $query = Tickets::with([
                'images:id,ticket_id,image_path',
                'department:id,name',
                'location:id,name',
                'submit_by:id,name',
                'assign_to:id,name',
                'comment' => function ($query) {
                    $query->select('id', 'ticket_id', 'comment', 'user_id', 'created_at')
                        ->with('user:id,name');
                },
            ]);

            // ✅ Automatically filter tickets by the logged-in user's department
            // if (!empty($user->department_id)) {
            //     $query->where('department_id', $user->department_id);
            // }

            // ✅ Other optional filters (still supported)
            if ($request->has('location_id')) {
                $query->where('location_id', $request->location_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('priority')) {
                $query->where('priority', $request->priority);
            }

            if ($request->has('search')) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    if (is_numeric($search)) {
                        $q->where('id', $search);
                    } else {
                        $q->where('title', 'like', "%$search%")
                            ->orWhere('description', 'like', "%$search%");
                    }
                });
            }

            // ✅ Tickets submitted by the logged-in user (optional)
            $get_ticket = $query->where('submitter_id', $user->id)->orWhere('department_id', $user->department_id)->orderBy('id', 'desc')->paginate(10);

            $baseUrl = url('');

            $get_ticket->getCollection()->transform(function ($ticket) use ($baseUrl) {
                // ✅ For multiple images (collection relationship)
                if ($ticket->images && $ticket->images->count()) {
                    $ticket->images->transform(function ($image) use ($baseUrl) {
                        $image->image_path = $baseUrl . '/public/' . ltrim($image->image_path, '/');
                        return $image;
                    });
                }

                // ✅ For single video field
                if (! empty($ticket->video)) {
                    $ticket->video = $baseUrl . '/public/uploads/tickets/' . ltrim($ticket->video, '/');
                }

                $this->addFrontendTicketFields($ticket);

                return $ticket;
            });

            if ($get_ticket->isEmpty()) {
                return $this->sendError('No data found.', ['error' => 'No data found'], 404);
            }

            return $this->sendResponse($get_ticket, 'Tickets filtered by your department');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), 422);
        }
    }



    public function getAllTickets(Request $request)
    {
        $user = Auth::guard('api')->user();

        try {
            $query = Tickets::with([
                'images:id,ticket_id,image_path',
                'department:id,name',
                'location:id,name',
                'submit_by:id,name',
                'assign_to:id,name',
                'comment' => function ($query) {
                    $query->select('id', 'ticket_id', 'comment', 'user_id', 'created_at')
                        ->with('user:id,name');
                },
            ]);

            // Search
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%$search%")
                        ->orWhere('description', 'like', "%$search%")
                        ->orWhere('id', $search);
                });
            }

            // Filters
            if ($request->has('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            if ($request->has('location_id')) {
                $query->where('location_id', $request->location_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('priority')) {
                $query->where('priority', $request->priority);
            }

            // Date range filter
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59',
                ]);
            }

            // Department head restriction
            if ($user->role == 'department_head') {
                $query->where('department_id', $user->department_id);
            }

            // Pagination
            if (! $request->has('page') && ! $request->has('per_page')) {
                $get_ticket = $query->orderBy('id', 'desc')->paginate(100000);
            } else {
                $get_ticket = $query->orderBy('id', 'desc')->paginate($request->input('per_page', 10));
            }

            // ================= REPORT CALCULATION =================
            $closedTickets = Tickets::where('status', 'Closed')->get();

            $deptStats     = [];
            $assigneeStats = [];
            $globalSum     = 0;
            $globalCount   = 0;

            foreach ($closedTickets as $ct) {
                if ($ct->created_at && $ct->updated_at) {

                    $minutes = Carbon::parse($ct->created_at)
                        ->diffInMinutes(Carbon::parse($ct->updated_at));

                    if (! isset($deptStats[$ct->department_id])) {
                        $deptStats[$ct->department_id] = ['sum' => 0, 'count' => 0];
                    }

                    $deptStats[$ct->department_id]['sum'] += $minutes;
                    $deptStats[$ct->department_id]['count']++;

                    if ($ct->assignee_id) {
                        if (! isset($assigneeStats[$ct->assignee_id])) {
                            $assigneeStats[$ct->assignee_id] = ['sum' => 0, 'count' => 0];
                        }

                        $assigneeStats[$ct->assignee_id]['sum'] += $minutes;
                        $assigneeStats[$ct->assignee_id]['count']++;
                    }

                    $globalSum += $minutes;
                    $globalCount++;
                }
            }

            $globalAvgHours = $globalCount > 0 ? floor(($globalSum / $globalCount) / 60) : 0;
            $globalAvgMins  = $globalCount > 0 ? round(($globalSum / $globalCount) % 60) : 0;

            $globalAvgFormatted = $globalAvgHours > 0
                ? "{$globalAvgHours} hrs {$globalAvgMins} mins"
                : "{$globalAvgMins} mins";

            $totalVolume = Tickets::count();

            // ================= TRANSFORM =================
            $baseUrl = url('public/');

            $get_ticket->getCollection()->transform(function ($ticket) use ($baseUrl, $deptStats, $assigneeStats, $globalAvgFormatted, $totalVolume) {

                // Images
                if ($ticket->images) {
                    $ticket->images->transform(function ($image) use ($baseUrl) {
                        $image->image_path = $baseUrl . '/' . $image->image_path;
                        return $image;
                    });
                }

                // Overdue
                $ticket->is_overdue = (
                    $ticket->status !== 'Closed' &&
                    $ticket->expected_resolution_time !== null &&
                    Carbon::parse($ticket->expected_resolution_time)->isPast()
                );

                // Department avg
                $deptAvg = ($ticket->department_id && isset($deptStats[$ticket->department_id]))
                    ? ($deptStats[$ticket->department_id]['sum'] / $deptStats[$ticket->department_id]['count'])
                    : 0;

                $ticket->avg_resolution_time_per_department = $deptAvg > 0
                    ? floor($deptAvg / 60) . ' hrs ' . round($deptAvg % 60) . ' mins'
                    : 'N/A';

                // Assignee avg
                $assgnAvg = ($ticket->assignee_id && isset($assigneeStats[$ticket->assignee_id]))
                    ? ($assigneeStats[$ticket->assignee_id]['sum'] / $assigneeStats[$ticket->assignee_id]['count'])
                    : 0;

                $ticket->avg_resolution_time_per_assignee = $assgnAvg > 0
                    ? floor($assgnAvg / 60) . ' hrs ' . round($assgnAvg % 60) . ' mins'
                    : 'N/A';

                // Global stats
                $ticket->average_resolution_time = $globalAvgFormatted;
                $ticket->ticket_volume_over_time = $totalVolume;

                // ✅ FINAL DATE FIX (IMPORTANT)
                $ticket->setAttribute(
                    'created_at',
                    $ticket->created_at ? $ticket->created_at->format('Y-m-d H:i:s') : null
                );

                $ticket->setAttribute(
                    'updated_at',
                    $ticket->updated_at ? $ticket->updated_at->format('Y-m-d H:i:s') : null
                );

                $ticket->setAttribute(
                    'expected_resolution_time',
                    $ticket->expected_resolution_time
                        ? Carbon::parse($ticket->expected_resolution_time)->format('Y-m-d H:i:s')
                        : null
                );

                // Comments date formatting
                if ($ticket->comment) {
                    $ticket->comment->transform(function ($c) {
                        $c->setAttribute(
                            'created_at',
                            $c->created_at ? $c->created_at->format('Y-m-d H:i:s') : null
                        );
                        return $c;
                    });
                }

                $this->addFrontendTicketFields($ticket);

                return $ticket;
            });



            // Empty check
            if ($get_ticket->isEmpty()) {
                return $this->sendError('No data found.', ['error' => 'No data found'], 404);
            }

            return $this->sendResponse($get_ticket, 'All Ticket list');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), 422);
        }
    }

    // Ticket Details
    public function ticketDetails(Request $request, $id)
    {
        try {
            $get_ticket = Tickets::with([
                'images:id,ticket_id,image_path',
                'department:id,name',
                'location:id,name',
                'submit_by:id,name',
                'assign_to:id,name',
                'comment' => function ($query) {
                    $query->select('id', 'ticket_id', 'comment', 'user_id', 'created_at')
                        ->with('user:id,name');
                },
            ])->where('id', $id)->first();

            $baseUrl = url('public');

            if ($get_ticket) {
                // ✅ Add full URL for images
                if ($get_ticket->images) {
                    $get_ticket->images->transform(function ($image) use ($baseUrl) {
                        $image->image_path = $baseUrl . '/' . ltrim($image->image_path, '/');
                        return $image;
                    });
                }

                // ✅ Add full video URL (single)
                if (! empty($get_ticket->video)) {
                    $get_ticket->video_full_url = $baseUrl . '/uploads/tickets/' . ltrim($get_ticket->video, '/');
                } else {
                    $get_ticket->video_full_url = null;
                }

                $this->addFrontendTicketFields($get_ticket);

                return $this->sendResponse($get_ticket, 'Single Ticket details');
            } else {
                return $this->sendError('Error.', ['error' => 'Ticket not found'], 401);
            }
        } catch (\Exception $e) {
            return $this->sendError('Error.', $e->getMessage());
        }
    }

    // Update Ticket
    public function updateTicket(Request $request, $id)
    {
        $user = Auth::guard('api')->user();

        $ticket = Tickets::find($id);

        if (! $ticket) {
            return response()->json([
                'message' => 'Ticket not found',
                'status'  => 404,
                'error'   => true,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status'  => 400,
                'error'   => true,
            ], 422);
        }

        $oldStatus = $ticket->status;
        $oldPriority = $ticket->priority;

        // Update Ticket
        $ticket->title         = $request->title ?? $ticket->title;
        $ticket->description   = $request->description ?? $ticket->description;
        $ticket->status        = $request->status ?? $ticket->status;
        $ticket->department_id = $request->department_id ?? $ticket->department_id;
        $ticket->save();

        // LOG HISTORY: General Update
        if ($oldStatus != $ticket->status || $oldPriority != $ticket->priority) {
            logTicketHistory($ticket->id, $user->id, [
                'status_from'   => $oldStatus,
                'status_to'     => $ticket->status,
                'priority_from' => $oldPriority,
                'priority_to'   => $ticket->priority,
                'message'       => 'Ticket details updated by ' . $user->name
            ]);
        }

        // SAVE IMAGES
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $directory = public_path('uploads/tickets');
                $imagePath = $this->uploadFile($image, $directory);

                TicketImages::create([
                    'ticket_id'  => $ticket->id,
                    'image_path' => $imagePath,
                ]);
            }
        }

        $emailUsers = collect([]);

        if ($ticket->submitter_id) {
            $emailUsers->push(User::find($ticket->submitter_id));
        }

        $admins     = User::where('role', 'admin')->get();
        $emailUsers = $emailUsers->merge($admins);

        $deptHeads = User::where('department_id', $ticket->department_id)
            ->where('role', 'department_head')
            ->get();
        $emailUsers = $emailUsers->merge($deptHeads);

        $emailUsers = $emailUsers->filter()->unique('id');

        $emailUsers = $emailUsers->where('id', '!=', $user->id);

        foreach ($emailUsers as $emailUser) {

            if (! $emailUser->email) {
                continue;
            }

            $mailData = [
                'email'         => $emailUser->email,
                'subject'       => 'Ticket #' . $ticket->id . ' Updated',
                'page'          => 'email.ticket_update',
                'ticket_id'     => $ticket->id,
                'ticket_title'  => $ticket->title,
                'updated_by'    => $user->name,
                'ticket_status' => $ticket->status,
                'description'   => $ticket->description,
            ];

            self::send_mail($mailData);
        }

        return $this->sendResponse($ticket, 'Ticket updated successfully');
    }

    private function uploadFile($file, $directory)
    {
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);
        return 'uploads/' . basename($directory) . '/' . $filename;
    }

    public function assignTicketOld(Request $request, $id)
    {
        $ticket = Tickets::find($id);

        if (! $ticket) {
            return response()->json([
                'message' => 'Ticket not found',
                'status'  => 404,
                'error'   => true,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'assignee_id'              => 'required|exists:users,id',
            'expected_resolution_time' => 'required|date', // assuming it's a date/time
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status'  => 400,
                'error'   => true,
            ], 422);
        }

        // Update ticket
        $ticket->assignee_id              = $request->assignee_id;
        $ticket->expected_resolution_time = $request->expected_resolution_time;
        $ticket->status                   = 'Assigned';
        $ticket->save();

        $ticket_id     = $ticket->id;
        $trigger_event = 'Ticket Assigned';
        $recipient_id  = $request->assignee_id;
        $title         = 'Ticket Assigned';

        $submitter_message = 'Your ticket has been assigned to our executive.';
        addNotification($ticket_id, $trigger_event, $recipient_id, $ticket->submitter_id, $title, $submitter_message, 'unread');

        $assignee_message = 'A new ticket has been assigned to you.';
        addNotification($ticket_id, $trigger_event, $recipient_id, $request->assignee_id, $title, $assignee_message, 'unread');

        return $this->sendResponse($ticket, 'Ticket assigned successfully');
    }

    // public function assignTicket(Request $request, $id)
    // {
    //     // Find ticket
    //     $ticket = Tickets::find($id);

    //     if (!$ticket) {
    //         return response()->json([
    //             'message' => 'Ticket not found',
    //             'status' => 404,
    //             'error' => true,
    //         ], 404);
    //     }

    //     // Validate request
    //     $validator = Validator::make($request->all(), [
    //         'assignee_id' => 'required|exists:users,id',
    //         'expected_resolution_time' => 'required|date',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'message' => $validator->errors()->first(),
    //             'status' => 400,
    //             'error' => true,
    //         ], 422);
    //     }

    //     // Update ticket details
    //     $ticket->assignee_id = $request->assignee_id;
    //     $ticket->expected_resolution_time = $request->expected_resolution_time;
    //     $ticket->status = 'Assigned';
    //     $ticket->save();

    //     $ticket_id = $ticket->id;
    //     $trigger_event  = 'Ticket Assigned';
    //     $recipient_id   = $request->assignee_id;
    //     $title = 'Ticket Assigned';

    //     // ✅ Notification to ticket submitter
    //     $submitter_message = 'Your ticket has been assigned to our executive.';
    //     addNotification($ticket_id, $trigger_event, $recipient_id, $ticket->submitter_id, $title, $submitter_message, 'unread');

    //     // ✅ Notification to assignee
    //     $assignee_message = 'A new ticket has been assigned to you.';
    //     addNotification($ticket_id, $trigger_event, $recipient_id, $request->assignee_id, $title, $assignee_message, 'unread');

    //     // ✅ Email sending logic
    //     $assignee = User::find($request->assignee_id);
    //     $submitter = User::find($ticket->submitter_id);

    //     if ($assignee && $assignee->email) {
    //         $mailData = [
    //             'email' => $assignee->email,
    //             'subject' => 'New Ticket Assigned - #' . $ticket->id,
    //             'page' => 'email.ticket_assigned',
    //             'assignee_name' => $assignee->name,
    //             'ticket_id' => $ticket->id,
    //             'ticket_title' => $ticket->title ?? 'N/A',
    //             'ticket_description' => $ticket->description ?? 'No description provided',
    //             'expected_resolution_time' => $ticket->expected_resolution_time,
    //             'assigned_by' => $submitter->name ?? 'System',
    //         ];

    //         self::send_mail($mailData);
    //     }

    //     return $this->sendResponse($ticket, 'Ticket assigned successfully');
    // }

    // new changes on 18 12 2025

    public function assignTicket(Request $request, $id)
    {

        $user = Auth::guard('api')->user();

        $ticket = Tickets::find($id);

        if (! $ticket) {
            return response()->json([
                'message' => 'Ticket not found',
                'status'  => 404,
                'error'   => true,
            ], 404);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'assignee_id'              => 'required|exists:users,id',
            'expected_resolution_time' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status'  => 400,
                'error'   => true,
            ], 422);
        }

        // Check if reassignment is requested
        $isReassign = $request->has('reassign') && $request->reassign === true;

        // If ticket already has assignee and no reassign flag → block
        if ($ticket->assignee_id && ! $isReassign) {
            return response()->json([
                'message' => 'Ticket already assigned. Use reassign flag to change assignee.',
                'status'  => 422,
                'error'   => true,
            ], 422);
        }

        // If reassignment requested, enforce role check
        if ($isReassign && Auth::guard('api')->user()?->role !== 'admin') {
            return response()->json([
                'message' => 'Only Admins can reassign tickets.',
                'status'  => 403,
                'error'   => true,
            ], 403);
        }

        // Validate department consistency
        $assignee = User::find($request->assignee_id);

        // Capture old values for history
        $oldAssignee = $ticket->assignee_id;
        $oldStatus   = $ticket->status;

        // Update ticket details
        $ticket->assignee_id              = $request->assignee_id;
        $ticket->expected_resolution_time = $request->expected_resolution_time;
        $ticket->status                   = 'Assigned';

        // KPI: Set assigned_at if this is the first assignment
        if (!$ticket->assigned_at) {
            $ticket->assigned_at = now();
        }

        $ticket->save();

        // LOG HISTORY: Assignment
        logTicketHistory($ticket->id, $user->id, [
            'assignment_from' => $oldAssignee,
            'assignment_to'   => $request->assignee_id,
            'status_from'     => $oldStatus,
            'status_to'       => 'Assigned',
            'change_type'     => $isReassign ? 'reassignment' : 'assignment',
            'message'         => $isReassign ? 'Ticket reassigned by ' . $user->name : 'Ticket assigned by ' . $user->name
        ]);

        $ticket_id     = $ticket->id;
        $trigger_event = $isReassign ? 'Ticket Assigned' : 'Ticket Assigned';
        $recipient_id  = $request->assignee_id;
        $title         = $trigger_event;

        // ✅ Notification to ticket submitter
        $submitter_message = $isReassign
            ? 'Your ticket has been reassigned to another executive.'
            : 'Your ticket has been assigned to our executive.';
        addNotification($ticket_id, $trigger_event, $recipient_id, $ticket->submitter_id, $title, $submitter_message, 'unread');

        // ✅ Notification to assignee
        $assignee_message = $isReassign
            ? 'A ticket has been reassigned to you.'
            : 'A new ticket has been assigned to you.';
        addNotification($ticket_id, $trigger_event, $recipient_id, $request->assignee_id, $title, $assignee_message, 'unread');

        // ✅ Email sending logic
        $assignee  = User::find($request->assignee_id);
        $submitter = User::find($ticket->submitter_id);

        if ($assignee && $assignee->email) {
            $mailData = [
                'email'                    => $assignee->email,
                'subject'                  => ($isReassign ? 'Ticket Reassigned - #' : 'New Ticket Assigned - #') . $ticket->id,
                'page'                     => 'email.ticket_assigned',
                'assignee_name'            => $assignee->name,
                'ticket_id'                => $ticket->id,
                'ticket_title'             => $ticket->title ?? 'N/A',
                'ticket_description'       => $ticket->description ?? 'No description provided',
                'expected_resolution_time' => $ticket->expected_resolution_time,
                'assigned_by'              => $submitter->name ?? 'System',
            ];

            self::send_mail($mailData);
        }

        return $this->sendResponse(
            $ticket,
            $isReassign ? 'Ticket reassigned successfully' : 'Ticket assigned successfully'
        );
    }

    // Executive Assigned Ticket
    public function getAssignedTickets(Request $request)
    {
        $user = Auth::guard('api')->user();

        try {

            $query = Tickets::with([
                'images:id,ticket_id,image_path',
                'department:id,name',
                'location:id,name',
                'submit_by:id,name',
                'assign_to:id,name',
                'comment' => function ($query) {
                    $query->select('id', 'ticket_id', 'comment', 'user_id', 'created_at')->with('user:id,name');
                },
            ]);

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%$search%")
                        ->orWhere('description', 'like', "%$search%");
                });
            }

            // Filter by Department
            if ($request->has('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            // Filter by Location
            if ($request->has('location_id')) {
                $query->where('location_id', $request->location_id);
            }

            // Filter by Status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by Priority
            if ($request->has('priority')) {
                $query->where('priority', $request->priority);
            }
            $get_ticket = $query->where(['assignee_id' => $user->id])->paginate(10);

            $baseUrl = url('public/');
            $get_ticket->getCollection()->transform(function ($ticket) use ($baseUrl) {
                if ($ticket->images) {
                    $ticket->images->transform(function ($image) use ($baseUrl) {
                        $image->image_path = $baseUrl . '/' . $image->image_path;
                        return $image;
                    });
                }

                $this->addFrontendTicketFields($ticket);

                return $ticket;
            });

            if ($get_ticket->isEmpty()) {
                return $this->sendError('No data found.', ['error' => 'No data found'], 422);
            }

            return $this->sendResponse($get_ticket, 'All Assigned Ticket list');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), 422);
        }
    }

    // Accept Ticket By Executive
    public function acceptTicket(Request $request, $id)
    {
        $user = Auth::guard('api')->user();

        $ticket = Tickets::find($id);

        if (! $ticket) {
            return response()->json([
                'message' => 'Ticket not found',
                'status'  => 404,
                'error'   => true,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:In Progress',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status'  => 400,
                'error'   => true,
            ], 422);
        }

        $oldStatus = $ticket->status;

        // Update status
        $ticket->status = $request->status;
        $ticket->save();

        // LOG HISTORY: Accepted
        logTicketHistory($ticket->id, $user->id, [
            'status_from' => $oldStatus,
            'status_to'   => $request->status,
            'message'     => 'Ticket accepted and moved to In Progress by ' . $user->name
        ]);

        $emailUsers = collect([]);

        if ($ticket->assignee_id) {
            $emailUsers->push(User::find($ticket->assignee_id));
        }

        $admins = User::where('role', 'admin')->get();
        $emailUsers->push($admins);

        $emailUsers = $emailUsers->flatten(1)
            ->filter()
            ->unique('id');

        $emailUsers = $emailUsers->where('id', '!=', $user->id);

        foreach ($emailUsers as $emailUser) {

            if (! isset($emailUser->email) || empty($emailUser->email)) {
                continue;
            }

            $mailData = [
                'email'   => $emailUser->email,
                'subject' => "Ticket #{$ticket->id} Accepted",
                'page'          => 'email.ticket_update', // <-- your new template
                'ticket_id'     => $ticket->id,
                'ticket_title'  => $ticket->title,
                'ticket_status' => $ticket->status,
                'updated_by'    => $user->name,
                'description'   => $ticket->description,
            ];

            self::send_mail($mailData);
        }

        return $this->sendResponse($ticket, 'Ticket accepted successfully');
    }

    // Update Status
    public function updateTicketStatus(Request $request, $id)
    {
        $ticket = Tickets::find($id);

        if (! $ticket) {
            return response()->json([
                'message' => 'Ticket not found',
                'status'  => 404,
                'error'   => true,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Pending Confirmation,Resolved,Closed,Waiting for Parts',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status'  => 400,
                'error'   => true,
            ], 422);
        }

        $oldStatus = $ticket->status;
        $newStatus = $request->status;

        $ticket->status = $newStatus;

        // KPI: Update timestamps based on status
        if ($newStatus == 'Resolved') {
            $ticket->resolved_at = now();
        } elseif ($newStatus == 'Closed') {
            $ticket->closed_at = now();
        }

        $ticket->save();

        // LOG HISTORY: Status Change
        logTicketHistory($ticket->id, Auth::guard('api')->id(), [
            'status_from' => $oldStatus,
            'status_to'   => $newStatus,
            'message'     => 'Status updated to ' . $newStatus
        ]);

        $ticket_id     = $ticket->id;
        $trigger_event = 'Status Updated';
        $title         = 'Ticket Status Changed';

        $submitter_message = 'Your ticket status has been updated to "' . $ticket->status . '".';
        addNotification($ticket_id, $trigger_event, $ticket->submitter_id, $ticket->submitter_id, $title, $submitter_message, 'unread');

        if ($ticket->assignee_id) {
            $assignee_message = 'Ticket #' . $ticket->id . ' status has been updated to "' . $ticket->status . '".';
            addNotification($ticket_id, $trigger_event, $ticket->assignee_id, $ticket->assignee_id, $title, $assignee_message, 'unread');
        }

        $submitter = User::find($ticket->submitter_id);
        $assignee  = $ticket->assignee_id ? User::find($ticket->assignee_id) : null;

        if ($submitter && $submitter->email) {
            $mailData = [
                'email'              => $submitter->email,
                'subject'            => 'Ticket #' . $ticket->id . ' Status Updated',
                'page'               => 'email.ticket_status_update',
                'ticket_id'          => $ticket->id,
                'ticket_title'       => $ticket->title ?? 'N/A',
                'ticket_status'      => $ticket->status,
                'ticket_description' => $ticket->description ?? 'No description provided',
                'assignee_name'      => $assignee->name ?? 'Unassigned',
            ];

            self::send_mail($mailData);
        }

        return $this->sendResponse($ticket, 'Ticket status updated successfully');
    }

    public function executiveWeeklySummary(Request $request)
    {
        $user = Auth::guard('api')->user();

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek   = Carbon::now()->endOfWeek();

        $weeklyTickets = Tickets::where(['assignee_id' => $user->id])->whereBetween('created_at', [$startOfWeek, $endOfWeek])->get();

        $openStatuses = ['New', 'Assigned', 'In Progress'];

        $openCount     = $weeklyTickets->whereIn('status', $openStatuses)->count();
        $closedTickets = $weeklyTickets->where('status', 'Closed');
        $closedCount   = $closedTickets->count();

        $overdueCount = $weeklyTickets->filter(function ($ticket) {
            return $ticket->status !== 'Closed' &&
                $ticket->expected_resolution_time !== null &&
                Carbon::parse($ticket->expected_resolution_time)->isPast();
        })->count();

        $resolutionTimes = $closedTickets->map(function ($ticket) {
            if ($ticket->created_at && $ticket->updated_at) {
                return [
                    'ticket_id'               => $ticket->id,
                    'title'                   => $ticket->title,
                    'created_at'              => $ticket->created_at,
                    'updated_at'              => $ticket->updated_at,
                    'resolution_time_minutes' => Carbon::parse($ticket->created_at)->diffInMinutes(Carbon::parse($ticket->updated_at)),
                ];
            }
            return null;
        })->filter()->values();

        $quickestTicket = $resolutionTimes->sortBy('resolution_time_minutes')->first();

        $minResolutionTime = $quickestTicket['resolution_time_minutes'] ?? 0;
        $hours             = intdiv($minResolutionTime, 60);
        $minutes           = $minResolutionTime % 60;
        $humanReadable     = ($hours > 0 ? "$hours hour" . ($hours > 1 ? 's ' : ' ') : '') . "$minutes minutes";

        return response()->json([
            'week_start'                  => $startOfWeek->toDateTimeString(),
            'week_end'                    => $endOfWeek->toDateTimeString(),
            'total_open_tickets'          => $openCount,
            'total_closed_tickets'        => $closedCount,
            'total_overdue_tickets'       => $overdueCount,
            'min_resolution_time_minutes' => $humanReadable,
        ]);
    }

    private function addFrontendTicketFields($ticket)
    {
        $ticket->frontend_ticket_url = $this->frontendTicketUrl($ticket->id);

        $targetMinutes = $this->slaResolutionMinutesForPriority($ticket->priority);
        $assignedAt    = $this->carbonValue($ticket->assigned_at ?? null);
        $resolvedAt    = $this->carbonValue($ticket->resolved_at ?? null);
        $closedAt      = $this->carbonValue($ticket->closed_at ?? null);
        $createdAt     = $this->carbonValue($ticket->created_at ?? null);
        $now           = Carbon::now();

        $ticket->post_assignment_sla_target_minutes = $targetMinutes;
        $ticket->post_assignment_deadline = null;
        $ticket->post_assignment_sla_status = 'not_started';
        $ticket->post_assignment_sla_elapsed_minutes = null;
        $ticket->post_assignment_sla_remaining_minutes = null;

        if ($assignedAt && $targetMinutes) {
            $deadline = $assignedAt->copy()->addMinutes($targetMinutes);
            $finishAt = $resolvedAt ?: $closedAt ?: $now;

            $ticket->post_assignment_deadline = $deadline->format('Y-m-d H:i:s');
            $ticket->post_assignment_sla_elapsed_minutes = max(0, $assignedAt->diffInMinutes($finishAt, false));
            $ticket->post_assignment_sla_remaining_minutes = max(0, $now->diffInMinutes($deadline, false));

            if ($resolvedAt || $closedAt) {
                $ticket->post_assignment_sla_status = $finishAt->lte($deadline) ? 'met' : 'breached';
            } else {
                $ticket->post_assignment_sla_status = $now->gt($deadline) ? 'breached' : 'in_progress';
            }
        }

        $technicianFinishAt = $resolvedAt ?: $closedAt;
        $technicianMinutes = ($assignedAt && $technicianFinishAt)
            ? max(0, $assignedAt->diffInMinutes($technicianFinishAt, false))
            : null;

        $lifecycleMinutes = ($createdAt && $closedAt)
            ? max(0, $createdAt->diffInMinutes($closedAt, false))
            : null;

        $ticket->technician_resolution_minutes = $technicianMinutes;
        $ticket->technician_resolution_time = $this->formatDuration($technicianMinutes);
        $ticket->ticket_lifecycle_minutes = $lifecycleMinutes;
        $ticket->ticket_lifecycle_time = $this->formatDuration($lifecycleMinutes);
    }

    private function frontendTicketUrl($ticketId)
    {
        return rtrim(env('FRONTEND_URL', config('app.url')), '/') . '/dashboard/tickets?id=' . $ticketId;
    }

    private function slaResolutionMinutesForPriority($priority)
    {
        static $slaMinutes = null;

        if ($slaMinutes === null) {
            $slaMinutes = \App\Models\SlaLevel::pluck('resolution_time_minutes', 'priority')->toArray();
        }

        return isset($slaMinutes[$priority]) ? (int) $slaMinutes[$priority] : null;
    }

    private function carbonValue($value)
    {
        if (empty($value)) {
            return null;
        }

        return $value instanceof Carbon ? $value->copy() : Carbon::parse($value);
    }

    private function formatDuration($minutes)
    {
        if ($minutes === null) {
            return null;
        }

        $hours = intdiv((int) $minutes, 60);
        $mins = (int) $minutes % 60;

        return $hours > 0 ? "{$hours} hrs {$mins} mins" : "{$mins} mins";
    }

    public static function send_mail($data)
    {
        // dd($data);
        // dd("test 12345");

        Mail::send($data['page'], $data, function ($message) use ($data) {
            $message->to($data['email'])
                ->subject($data['subject'])
                ->from('admin@example.com', config('app.name'));
        });
    }

    public function show($ticket_id)
    {
        $ticket = Tickets::findOrFail($ticket_id);

        // Optional: permission check
        // abort(403) if user cannot view this ticket

        return view('tickets.view', compact('ticket'));
    }

    public function getTicketHistory(Request $request, $id)
    {
        try {
            $history = \App\Models\TicketHistory::with(['user:id,name', 'from_assignee:id,name', 'to_assignee:id,name'])
                ->where('ticket_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($history->isEmpty()) {
                return $this->sendResponse([], 'No history found for this ticket.');
            }

            return $this->sendResponse($history, 'Ticket history retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), 422);
        }
    }
}
