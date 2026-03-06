<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionOrder;
use Illuminate\Http\Request;
use Validator;

class SubscriptionPlanController extends BaseController
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'features' => 'nullable|array',
            'duration_in_days' => 'nullable|integer|min:1',
            'status' => 'nullable|boolean', // can be 0 or 1
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 400,
                'error' => true,
            ], 422);
        }

        $validated = $validator->validated();

        // Convert features array to JSON if needed
        // if (isset($validated['features'])) {
        //     $validated['features'] = json_encode($validated['features']);
        // }

        // If status is not sent, default to 1
        if (!isset($validated['status'])) {
            $validated['status'] = 1;
        }

        $plan = SubscriptionPlan::create($validated);

        return response()->json(['status' => true, 'data' => $plan], 201);
    }


    public function index()
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        // Fetch all plans
        $plans = SubscriptionPlan::all();

        // Fetch purchased plan IDs for this user
        $purchasedPlans = SubscriptionOrder::where('user_id', $user->id)
            ->pluck('plan_id')
            ->toArray();

        // Add is_purchased field dynamically
        $plans = $plans->map(function ($plan) use ($purchasedPlans) {
            $plan->is_purchased = in_array($plan->id, $purchasedPlans) ? 1 : 0;
            return $plan;
        });

        return response()->json([
            'status' => true,
            'data' => $plans
        ]);
    }


    public function show($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        return response()->json(['status' => true, 'data' => $plan]);
    }

    public function update(Request $request, $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        $validated = $request->validate([
            'plan_name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'features' => 'nullable|array',
            'duration_in_days' => 'nullable|integer|min:1',
            'status' => 'boolean',
        ]);

        $plan->update($validated);
        return response()->json(['status' => true, 'data' => $plan , 'message' => 'Plan updated successfully']);
    }

    public function destroy($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->delete();

        return response()->json(['status' => true, 'message' => 'Plan deleted successfully']);
    }
}
?>