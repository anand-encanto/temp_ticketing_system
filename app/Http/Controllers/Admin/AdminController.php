<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Tickets;

class AdminController extends BaseController{
    
    // User Add
    public function addUser(Request $request){
    
        $validator = Validator::make($request->all(), [
            'name'             => 'required|string|max:255',
            'username'         => 'required|unique:users,username',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|string|min:6',
            'role'             => 'required|in:standard,department_head,admin,executive',
            'location_id'      => 'required|exists:locations,id',
            'department_id'    => 'nullable|exists:departments,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 400,
                    'error'  => true,  // Set error to true
                ],
                422
            );
        }
    
        $model                  = new User();
        $model->name            = $request->name;
        $model->username        = $request->username;
        $model->email           = $request->email;
        $model->password        = Hash::make($request->password);
        $model->role            = $request->role;
        $model->location_id     = $request->location_id;
        $model->department_id   = $request->department_id;
        $model->save();
        

        // Add Notification
        $ticket_id      = $model->id;
        $trigger_event  = 'New User';
        $recipient_id   = null;
        $title          = 'New user registration';
        $message        = 'New user, '.$request->name.' has been register on panel';
        $result_add     = addNotification($ticket_id,$trigger_event,$recipient_id,'1',$title,$message,'unread'); 

        return $this->sendResponse($model, 'User Added Successfully');
    }

    // All User
    public function getAllUsers(Request $request)
    {
        try {
            $query = User::with(['department:id,name', 'location:id,name'])->where('id', '!=', 1);

            if ($request->has('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            if ($request->has('location_id')) {
                $query->where('location_id', $request->location_id);
            }

            if ($request->has('role')) {
                $query->where('role', $request->role);
            }

           if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                });
            }

            if ($request->has('paginate') && $request->paginate == 'true') {
                $get_users = $query->paginate(10);
            } else {
                $get_users = $query->get();
            }

            if ($get_users->isEmpty()) {
                return $this->sendError('No data found.', ['error' => 'No data found'], 404);
            }

            return $this->sendResponse($get_users, 'All User list');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), 422);
        }
    }

    // User Details
    public function userDetails(Request $request,$id){
        try{
            $get_user = User::where(['id' => $id])->first();
            if ($get_user) {
                return $this->sendResponse($get_user, 'Single User details');
            }else{
                return $this->sendError('Error.', ['error' => 'User not found'], 401);
            }
        } catch (\Exception $e) {
            return $this->sendError('Error.', $e->getMessage());
        }    
    }

    public function editUser(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
                'status'  => 404,
                'error'   => true,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'             => 'sometimes|required|string|max:255',
            'role'             => 'sometimes|required|in:standard,department_head,admin,executive',
            'location_id'      => 'sometimes|required|exists:locations,id',
            'department_id'    => 'nullable|exists:departments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status'  => 400,
                'error'   => true,
            ], 422);
        }

        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('role')) $user->role = $request->role;
        if ($request->has('email')) $user->email = $request->email;
        if ($request->has('username')) $user->username = $request->username;
        if ($request->has('location_id')) $user->location_id = $request->location_id;
        if ($request->has('department_id')) $user->department_id = $request->department_id;

        $user->save();

        return $this->sendResponse($user, 'User updated successfully.');
    }

    // Delete User
    public function userDelete(Request $request,$id){
        try {
            $user = Auth::guard('api')->user();
            $user  = User::find($id);

            if (!$user) {
                return $this->sendError('User not found', ['error' => 'User not found'], 401);
            }

            $user->delete();
            return $this->sendResponse('Delete', 'User deleted Successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error.', $e->getMessage());
        }    
    }

}

