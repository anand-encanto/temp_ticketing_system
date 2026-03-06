<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Notification;

class UserController extends BaseController
{

    public function profile()
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return $this->sendError('Unauthorized', [], 401);
        }

        $user = User::with([
                    'department:id,name',
                    'location:id,name',
                ])->where('id',$user->id)->first();

        $defaultProfileImg = 'default/avatar.png';
        $profileImgPath = $user->profile_img ?? $defaultProfileImg;

        $user->profile_img = getProfileImageUrl($user->profile_img);

        $get_notification_count = Notification::where(['user_id'=>$user->id,'status'=>'unread'])->count();
        return $this->sendResponse([
            'user' => $user,
            'notification_count' => $get_notification_count,
        ], 'Profile retrieved successfully');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('api')->user();

        // Validate the input
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        // Check and update user basic information if provided
        if ($request->filled('name')) {
            $user->name = $request->name;
        }
        if ($request->filled('phone_number')) {
            $user->phone_number = $request->phone_number;
        }
        
        if ($request->hasFile('profile_img')) {
            $image = $request->file('profile_img');
            $directory = public_path('profiles');
            $imageFileName = time() . '_' . $image->getClientOriginalName();

            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
            $image->move($directory, $imageFileName);

            $user->profile_img = $imageFileName;
        }
     
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user,
        ]);
    }

}