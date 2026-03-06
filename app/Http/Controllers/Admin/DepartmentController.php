<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Departments;

class DepartmentController extends BaseController{

    // All Department
    public function getAllDepartments(Request $request)
    {
        try {

            $query = Departments::orderBy('id','desc');

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%");
                });
            }

            if ($request->has('paginate') && $request->paginate == 'true') {
                $get_department = $query->paginate(10);
            } else {
                $get_department = $query->get();
            }

            if ($get_department->isEmpty()) {
                return $this->sendError('No data found.', ['error' => 'No data found'], 404);
            }

            return $this->sendResponse($get_department, 'All Department list');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), 422);
        }
    }

    // Department Details
    public function departmentDetails(Request $request,$id){
        try{
            $get_department = Departments::where(['id' => $id])->first();
            if ($get_department) {
                return $this->sendResponse($get_department, 'Single Department details');
            }else{
                return $this->sendError('Error.', ['error' => 'Department not found'], 401);
            }
        } catch (\Exception $e) {
            return $this->sendError('Error.', $e->getMessage());
        }    
    }

    // Department Add
    public function addDepartment(Request $request){

        $validator = Validator::make($request->all(), [
            'name'  => 'required|unique:departments',
            'description'  => 'required',
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

        $model               = new Departments();
        $model->name         = $request->name;
        $model->description  = $request->description;
        $model->save();

        return $this->sendResponse($model, 'Department Added Successfully');
    }

    public function editDepartment(Request $request, $id){
        $department = Departments::find($id);

        if (!$department) {
            return response()->json([
                'message' => 'Department not found',
                'status' => 404,
                'error' => true,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:departments,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 400,
                'error' => true,
            ], 422);
        }

        $department->name    = $request->name;
        $department->description = $request->description ?? $department->description;
        $department->save();

        return $this->sendResponse($department, 'Department updated successfully');
    }

    // Delete Department
    public function departmentDelete(Request $request,$id){
        try {
            $user = Auth::guard('api')->user();
            $department  = Departments::find($id);

            if (!$department) {
                return $this->sendError('Department not found', ['error' => 'Department not found'], 401);
            }

            $department->delete();
            return $this->sendResponse('Delete', 'Department deleted Successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error.', $e->getMessage());
        }    
    }

}

