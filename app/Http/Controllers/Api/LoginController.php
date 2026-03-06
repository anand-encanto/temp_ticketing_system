<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserVerification;
use Mail;

class LoginController extends BaseController
{

    // Register for user,admin
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'             => 'required|string|max:255',
            'username'            => 'required|unique:users,username',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|string|min:6',
            'confirm_password' => 'required_with:password|same:password',
            'role'             => 'required|in:standard,department_head,admin,executive',
            'location_id'      => 'required|exists:locations,id',
            'department_id'      => 'nullable|exists:departments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 400,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        // Create new user
        $user = User::create([
            'name'        => $request->name,
            'username'    => $request->username,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'role'        => $request->role,
            'location_id' => $request->location_id,
            'department_id' => $request->department_id,
        ]);


        // Add Notification
        $ticket_id      = $user->id;
        $trigger_event  = 'New User';
        $recipient_id   = null;
        $title          = 'New user registration';
        $message        = 'New user, '.$request->name.' has been register on panel';
        $result_add     = addNotification($ticket_id,$trigger_event,$recipient_id,'1',$title,$message,'unread'); 

        return response()->json([
            'status'  => 200,
            'message' => 'Registration successful!',
            'data'    => $user,
        ], 200);
    }

    public function login(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'email_or_username' => 'required|string',
            'password' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }
        
        $login_type = filter_var($request->email_or_username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        // return $this->sendResponse('successful', 'Successfully logged in');
        if (Auth::attempt([$login_type => $request->email_or_username, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('MyApp')->accessToken;

            $success['user']  = $user;
            $success['token'] = $token;

            return $this->sendResponse($success, 'Successfully logged in');
        } else {
            return $this->sendError('Unauthorized.', ['error' => 'Invalid credentials'], 401);
        }
    }


    //Forgot password
    public function forgot_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 400,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'status'  => 200,
                'message' => 'Password reset link sent successfully.',
            ]);
        } else {
            return response()->json([
                'status'  => 500,
                'message' => 'Unable to send reset link. Please try again.',
            ], 500);
        }
    }

    //Reset Password
    public function reset_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'            => 'required|email|exists:users,email',
            'password'         => 'required|min:6|different:old_password', // Enforces a different password
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 400,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'User not found or not approved.',
                ], 404);
            }

            if (Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status'  => 400,
                    'message' => 'Your new password must be different from the current password.',
                ], 400);
            }

            // Update the user's password
            $user->password             = Hash::make($request->password);
            $user->save();

            return response()->json([
                'status'  => 200,
                'message' => 'Password updated successfully!',
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Reset Password Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 422,
                'message' => 'An error occurred while processing your request. Please try again later.',
            ], 422);
        }
    }

}

