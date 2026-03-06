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
use App\Models\Locations;

class LocationController extends BaseController{

    // All Location
   public function getAllLocations(Request $request)
    {
        try {
            $query = Locations::orderBy('id','desc');

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('code', 'like', "%$search%");
                });
            }

            if ($request->has('paginate') && $request->paginate == 'true') {
                $get_location = $query->paginate(10);
            } else {
                $get_location = $query->get();
            }

            if ($get_location->isEmpty()) {
                return $this->sendError('No data found.', ['error' => 'No data found'], 404);
            }

            return $this->sendResponse($get_location, 'All Location list');

        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), 422);
        }
    }


    // Location Details
    public function locationDetails(Request $request,$id){
        try{
            $get_location = Locations::where(['id' => $id])->first();
            if ($get_location) {
                return $this->sendResponse($get_location, 'Single Location details');
            }else{
                return $this->sendError('Error.', ['error' => 'Location not found'], 401);
            }
        } catch (\Exception $e) {
            return $this->sendError('Error.', $e->getMessage());
        }    
    }

    // Location Add
    public function addLocation(Request $request){

        $validator = Validator::make($request->all(), [
            'name'  => 'required|unique:locations',
            'code'  => 'required|unique:locations',
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

        $model           = new Locations();
        $model->name     = $request->name;
        $model->code     = $request->code;
        $model->address  = $request->address;
        $model->save();

        return $this->sendResponse($model, 'Locations Added Successfully');
    }

    public function editLocation(Request $request, $id){
        $location = Locations::find($id);

        if (!$location) {
            return response()->json([
                'message' => 'Location not found',
                'status' => 404,
                'error' => true,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:locations,name,' . $id,
            'code' => 'required|unique:locations,code,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 400,
                'error' => true,
            ], 422);
        }

        $location->name    = $request->name;
        $location->code    = $request->code;
        $location->address = $request->address ?? $location->address;
        $location->save();

        return $this->sendResponse($location, 'Location updated successfully');
    }


    // Delete Location
    public function locationDelete(Request $request,$id){
        try {
            $user = Auth::guard('api')->user();
            $Location  = Locations::find($id);

            if (!$Location) {
                return $this->sendError('Location not found', ['error' => 'Location not found'], 401);
            }

            $Location->delete();
            return $this->sendResponse('Delete', 'Location deleted Successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error.', $e->getMessage());
        }    
    }

}

