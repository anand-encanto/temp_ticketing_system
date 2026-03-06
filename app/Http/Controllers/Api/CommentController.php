<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Tickets;
use App\Models\Comment;
use App\Models\Notification;

class CommentController extends BaseController{

    // Add Comment
    public function addComment(Request $request, $ticketId)
    {
        $user = Auth::guard('api')->user();

        $validator = Validator::make($request->all(), [
            'comment' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 422,
                'error' => true,
            ], 422);
        }

        // Create Comment
        $comment = Comment::create([
            'ticket_id' => $ticketId,
            'user_id'   => $user->id,
            'comment'   => $request->comment,
        ]);

        // Ticket Details
        $ticket = Tickets::find($ticketId);
        if (!$ticket) {
            return $this->sendError('Ticket not found', [], 404);
        }

        $ticket_id     = $ticket->id;
        $trigger_event = 'Comment';

      
        if ($ticket->submitter_id == $user->id) {
            $title   = 'New Comment Added!';
            $message = "A new comment has been added to your department's ticket.";
        } 
        elseif ($ticket->department_id == $user->department_id) {
            $title   = 'New Comment Added!';
            $message = 'A new comment has been added to your ticket.';
        } 
        else {
            // Fallback (if needed)
            $title   = 'New Comment Added!';
            $message = "A new comment has been added to your department's ticket";
        }
        addNotification($ticket_id, $trigger_event, null, $ticket->submitter_id, $title, $message, 'unread');

        if ($ticket->assignee_id) {
            addNotification($ticket_id, $trigger_event, null, $ticket->assignee_id, $title, $message, 'unread');
        }

        $emailUsers = collect([]);

        if ($ticket->submitter_id) {
            $emailUsers->push(User::find($ticket->submitter_id));
        }

        if ($ticket->assignee_id) {
            $emailUsers->push(User::find($ticket->assignee_id));
        }

        $admins = User::where('role', 'admin')->get();
        $emailUsers = $emailUsers->merge($admins);

        $deptHeads = User::where('department_id', $ticket->department_id)
                          ->where('role', 'department_head')
                          ->get();
        $emailUsers = $emailUsers->merge($deptHeads);

        $deptUsers = User::where('department_id', $ticket->department_id)->get();
        $emailUsers = $emailUsers->merge($deptUsers);

        $emailUsers->push($comment->user);

        $emailUsers = $emailUsers
            ->filter()
            ->unique('id');

        $emailUsers = $emailUsers->where('id', '!=', $user->id);

        $assigneeName = $ticket->assignee_id ? User::find($ticket->assignee_id)->name : 'Unassigned';

        foreach ($emailUsers as $emailUser) {

            if (!$emailUser->email) continue;

            $mailData = [
                'email'         => $emailUser->email,
                'subject'       => $message. '#' . $ticket->id,
                'page'          => 'email.ticket_comment',
                'ticket_id'     => $ticket->id,
                'ticket_title'  => $ticket->title ?? 'N/A',
                'comment_user'  => $user->name,
                'comment'       => $request->comment,
                'ticket_status' => $ticket->status,
                'assignee_name' => $assigneeName,
            ];

            self::send_mail($mailData);
        }

        return $this->sendResponse($comment, 'Comment added successfully.');
    }


    // Edit Comment
    public function editComment(Request $request, $id)
    {
		$user = Auth::guard('api')->user();

        $validator = Validator::make($request->all(), [
            'comment' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 422,
                    'error'  => true,
                ],
                422
            );
        }

        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        if ($comment->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->update(['comment' => $request->comment]);

        return $this->sendResponse($comment, 'Comment updated successfully.');
    }

    // Delete Comment
    public function commentDelete(Request $request,$id){
        try {
            $user = Auth::guard('api')->user();

            $comment  = Comment::find($id);

            if (!$comment) {
                return $this->sendError('Comment not found', ['error' => 'Comment not found'], 401);
            }

            if ($comment->user_id !== $user->id) {
	            return response()->json(['message' => 'Unauthorized'], 403);
	        }

            $comment->delete();
            return $this->sendResponse('Delete', 'Comment deleted Successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error.', $e->getMessage());
        }    
    }


    public static function send_mail($data)
    {
        Mail::send($data['page'], $data, function ($message) use ($data) {
            $message->to($data['email'])
                    ->subject($data['subject'])
                    ->from('admin@example.com', config('app.name'));
        });
    }

}