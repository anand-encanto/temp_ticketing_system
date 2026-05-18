<?php

namespace App\Http\Controllers\Api;

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
use App\Models\Locations;

class TicketSummaryController extends BaseController
{

    public function weeklySummary(Request $request)
    {
        $user = Auth::guard('api')->user();

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $weeklyTickets = Tickets::whereBetween('created_at', [$startOfWeek, $endOfWeek])->where(['submitter_id' => $user->id])->get();

        $openStatuses = ['New', 'Assigned', 'In Progress'];

        $openCount = $weeklyTickets->whereIn('status', $openStatuses)->count();
        $closedTickets = $weeklyTickets->where('status', 'Closed');
        $closedCount = $closedTickets->count();

        $overdueCount = $weeklyTickets->filter(function ($ticket) {
            return $ticket->status !== 'Closed' &&
                $ticket->expected_resolution_time !== null &&
                Carbon::parse($ticket->expected_resolution_time)->isPast();
        })->count();

        $resolutionTimes = $closedTickets->map(function ($ticket) {
            if ($ticket->created_at && $ticket->updated_at) {
                return [
                    'ticket_id' => $ticket->id,
                    'title' => $ticket->title,
                    'created_at' => $ticket->created_at,
                    'updated_at' => $ticket->updated_at,
                    'resolution_time_minutes' => Carbon::parse($ticket->created_at)->diffInMinutes(Carbon::parse($ticket->updated_at)),
                ];
            }
            return null;
        })->filter()->values();

        $quickestTicket = $resolutionTimes->sortBy('resolution_time_minutes')->first();

        $minResolutionTime = $quickestTicket['resolution_time_minutes'] ?? 0;
        $hours = intdiv($minResolutionTime, 60);
        $minutes = $minResolutionTime % 60;
        $humanReadable = ($hours > 0 ? "$hours hour" . ($hours > 1 ? 's ' : ' ') : '') . "$minutes minutes";

        return response()->json([
            'week_start' => $startOfWeek->toDateTimeString(),
            'week_end'   => $endOfWeek->toDateTimeString(),
            'total_open_tickets'    => $openCount,
            'total_closed_tickets'  => $closedCount,
            'total_overdue_tickets' => $overdueCount,
            'min_resolution_time_minutes' => $humanReadable,
        ]);
    }

    public function urgentTickets(Request $request)
    {
        $user = Auth::guard('api')->user();

        $tickets = Tickets::with([
            'department:id,name',
            'location:id,name',
            'submit_by:id,name',
            'assign_to:id,name',
        ])
            ->where('priority', 'Urgent')->where(['submitter_id' => $user->id])->paginate(10);
        return response()->json($tickets);
    }

    public function resolutionTimes(Request $request)
    {
        $user = Auth::guard('api')->user();

        $tickets = Tickets::with([
            'department:id,name',
            'location:id,name',
            'submit_by:id,name',
            'assign_to:id,name',
        ])
            ->where('status', 'Closed')
            ->where(['submitter_id' => $user->id])
            ->paginate(10);

        $tickets->getCollection()->transform(function ($ticket) {
            $ticket->resolution_time_minutes = Carbon::parse($ticket->created_at)->diffInMinutes(Carbon::parse($ticket->updated_at));
            return $ticket;
        });

        return response()->json($tickets);
    }

    public function topLocations(Request $request)
    {
        $user = Auth::guard('api')->user();

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        // Step 1: Get top location IDs by ticket count
        $topLocations = Tickets::select('location_id', DB::raw('COUNT(*) as total'))
            ->groupBy('location_id')
            ->where(['submitter_id' => $user->id])
            ->orderByDesc('total')
            ->pluck('location_id');

        // Step 2: Paginate over locations (not tickets yet)
        $paginatedLocationIds = $topLocations->slice(($page - 1) * $perPage, $perPage)->values();

        $result = [];

        foreach ($paginatedLocationIds as $locationId) {
            $location = Locations::find($locationId);

            if (!$location) continue;

            $tickets = Tickets::with([
                'department:id,name',
                'location:id,name',
                'submit_by:id,name',
                'assign_to:id,name',
            ])
                ->where('location_id', $locationId)
                ->where(['submitter_id' => $user->id])
                ->orderByDesc('created_at')
                ->take(5) // Show top 5 latest tickets per location
                ->get();

            $result[] = [
                'location_id' => $locationId,
                'location_name' => $location->name,
                'ticket_count' => $tickets->count(),
                'tickets' => $tickets
            ];
        }

        return response()->json([
            'current_page' => $page,
            'per_page' => $perPage,
            'total_locations' => $topLocations->count(),
            'data' => $result
        ]);
    }

    public function openTickets(Request $request)
    {
        $user = Auth::guard('api')->user();

        $openStatuses = ['New', 'Assigned', 'In Progress'];

        $tickets = Tickets::with([
            'department:id,name',
            'location:id,name',
            'submit_by:id,name',
            'assign_to:id,name',
        ])->whereIn('status', $openStatuses)->where(['submitter_id' => $user->id])->paginate(10);

        return response()->json($tickets);
    }

    public function closedTickets(Request $request)
    {
        $user = Auth::guard('api')->user();

        $tickets = Tickets::with([
            'department:id,name',
            'location:id,name',
            'submit_by:id,name',
            'assign_to:id,name',
        ])->where('status', 'Closed')->where(['submitter_id' => $user->id])->paginate(10);

        return response()->json($tickets);
    }

    public function trendsAnalysis(Request $request)
    {
        $user = Auth::guard('api')->user();

        $query = Tickets::where('submitter_id', $user->id);

        // Apply Filters
        if ($request->has('location_id')) {
            $query->where('location_id', $request->location_id);
        }
        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->has('is_overdue')) {
            $query->where('is_overdue', $request->is_overdue);
        }

        // Date Range Filter
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        $query->whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ]);

        // 1. Volume over time (Daily)
        $volumeOverTime = (clone $query)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // 2. Status Distribution
        $statusDistribution = (clone $query)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // 3. Department Distribution
        $deptDistribution = (clone $query)
            ->select('department_id', DB::raw('count(*) as total'))
            ->with('department:id,name')
            ->groupBy('department_id')
            ->get();

        // 4. SLA/Overdue Stats
        $overdueStats = (clone $query)
            ->select('is_overdue', DB::raw('count(*) as total'))
            ->groupBy('is_overdue')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->is_overdue ? 'Overdue' : 'On Time',
                    'total' => $item->total
                ];
            });

        return response()->json([
            'filters_applied' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'location_id' => $request->location_id,
                'department_id' => $request->department_id,
                'status' => $request->status,
                'priority' => $request->priority,
            ],
            'trends' => [
                'volume_over_time' => $volumeOverTime,
                'status_distribution' => $statusDistribution,
                'department_distribution' => $deptDistribution,
                'sla_compliance' => $overdueStats
            ]
        ]);
    }
}
