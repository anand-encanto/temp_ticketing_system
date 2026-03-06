<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Notification;

class NotificationController extends BaseController{

	// My Notification

    public function getMyNotification(Request $request)
    {
        $user = Auth::guard('api')->user();

        try {
            if ($request->has('paginate') && $request->paginate == 'true') {
                $notifications = Notification::where('user_id', $user->id)->orderBy('id','desc')->paginate(10);

                // Add time_ago field
                $notifications->getCollection()->transform(function ($item) {
                    $item->time_ago = Carbon::parse($item->created_at)->diffForHumans();
                    return $item;
                });
            } else {
                $notifications = Notification::where('user_id', $user->id)->orderBy('id','desc')->get()->map(function ($item) {
                    $item->time_ago = Carbon::parse($item->created_at)->diffForHumans();
                    return $item;
                });
            }

            if ($notifications->isEmpty()) {
                return $this->sendError('No data found.', ['error' => 'No data found'], 404);
            }

            return $this->sendResponse($notifications, 'All Notifications');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), 422);
        }
    }



    // Update Notification
    public function updateNotification(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 400,
                    'error'   => true,
                ], 422);
            }

            $model = Notification::find($id);
            $model->status  = $request->status;
            $model->save();

            return $this->sendResponse([], 'Notification mark as read');
         
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while update Notification. Please try again later.',
                'status'  => 500,
                'error'   => true,
                'details' => $e->getMessage(), // For debugging
            ]);
        }
    }


    public function notificationDelete(Request $request,$id){
        try {
            $user = Auth::guard('api')->user();

            $notification  = Notification::find($id);

            if (!$notification) {
                return $this->sendError('Notification not found', ['error' => 'Notification not found'], 401);
            }

            $notification->delete();
            return $this->sendResponse('Delete', 'Notification deleted Successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error.', $e->getMessage());
        }    
    }
}
