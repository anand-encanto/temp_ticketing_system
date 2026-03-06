<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController as BaseController;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends BaseController
{
    // All Executive
    public function getAllExecutive(Request $request)
    {
        try {
            if ($request->has('paginate') && $request->paginate == 'true') {

                // $get_executive = User::with(['department:id,name','location:id,name'])->where(['role'=>'executive'])->paginate(10);

                $get_executive = User::with(['department:id,name', 'location:id,name'])
                    ->whereIn('role', ['executive', 'department_head', 'standard'])
                    ->paginate(10);
            } else {

                $get_executive = User::with([
                    'department:id,name',
                    'location:id,name',
                ])
                    ->whereIn('role', ['executive', 'department_head', 'standard'])
                    ->get();
            }

            if ($get_executive->isEmpty()) {
                return $this->sendError('No data found.', ['error' => 'No data found'], 404);
            }

            return $this->sendResponse($get_executive, 'All Executive list');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), 422);
        }
    }

    // All Countries
    public function get_countries(Request $request)
    {
        try {
            $get_countries = Country::all();
            return $this->sendResponse($get_countries, 'All Countries');
        } catch (\Exception $e) {
            return $this->sendError('Error.', $e->getMessage());
        }
    }

    // All States
    public function get_states(Request $request, $id)
    {
        try {
            $get_states = State::where(['country_id' => $id])->get();
            return $this->sendResponse($get_states, 'All States');
        } catch (\Exception $e) {
            return $this->sendError('Error.', $e->getMessage());
        }
    }

    // All Cities
    public function get_cities(Request $request, $id)
    {
        try {
            $get_cities = City::where(['state_id' => $id])->get();
            return $this->sendResponse($get_cities, 'All Cities');
        } catch (\Exception $e) {
            return $this->sendError('Error.', $e->getMessage());
        }
    }

}
