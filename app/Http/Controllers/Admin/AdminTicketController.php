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
use App\Models\Locations;

class AdminTicketController extends BaseController{
    
    public function weeklySummary(Request $request)
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $weeklyTickets = Tickets::whereBetween('created_at', [$startOfWeek, $endOfWeek])->get();

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

    // public function urgentTickets(Request $request)
    // {
    //     $tickets = Tickets::with([
    //             'department:id,name',
    //             'location:id,name',
    //             'submit_by:id,name',
    //             'assign_to:id,name',
    //         ])
    //     ->where('priority', 'Urgent')->paginate(10);
    //     return response()->json($tickets);
    // }

    // public function resolutionTimes(Request $request)
    // {
    //     $tickets = Tickets::with([
    //             'department:id,name',
    //             'location:id,name',
    //             'submit_by:id,name',
    //             'assign_to:id,name',
    //         ])
    //         ->where('status', 'Closed')
    //         ->paginate(10);

    //     $tickets->getCollection()->transform(function ($ticket) {
    //         $ticket->resolution_time_minutes = Carbon::parse($ticket->created_at)->diffInMinutes(Carbon::parse($ticket->updated_at));
    //         return $ticket;
    //     });

    //     return response()->json($tickets);
    // }

    // public function topLocations(Request $request)
    // {
    //     $perPage = $request->input('per_page', 10);
    //     $page = $request->input('page', 1);

    //     // Step 1: Get top location IDs by ticket count
    //     $topLocations = Tickets::select('location_id', DB::raw('COUNT(*) as total'))
    //         ->groupBy('location_id')
    //         ->orderByDesc('total')
    //         ->pluck('location_id');

    //     // Step 2: Paginate over locations (not tickets yet)
    //     $paginatedLocationIds = $topLocations->slice(($page - 1) * $perPage, $perPage)->values();

    //     $result = [];

    //     foreach ($paginatedLocationIds as $locationId) {
    //         $location = Locations::find($locationId);

    //         if (!$location) continue;

    //         $tickets = Tickets::with([
    //                 'department:id,name',
    //                 'location:id,name',
    //                 'submit_by:id,name',
    //                 'assign_to:id,name',
    //             ])
    //             ->where('location_id', $locationId)
    //             ->orderByDesc('created_at')
    //             ->take(5) // Show top 5 latest tickets per location
    //             ->get();

    //         $result[] = [
    //             'location_id' => $locationId,
    //             'location_name' => $location->name,
    //             'ticket_count' => $tickets->count(),
    //             'tickets' => $tickets
    //         ];
    //     }

    //     return response()->json([
    //         'current_page' => $page,
    //         'per_page' => $perPage,
    //         'total_locations' => $topLocations->count(),
    //         'data' => $result
    //     ]);
    // }

    // public function openTickets(Request $request)
    // {
    //     $openStatuses = ['New', 'Assigned', 'In Progress'];

    //     $tickets = Tickets::with([
    //             'department:id,name',
    //             'location:id,name',
    //             'submit_by:id,name',
    //             'assign_to:id,name',
    //         ])->whereIn('status', $openStatuses)->paginate(10);

    //     return response()->json($tickets);
    // }

    // public function closedTickets(Request $request)
    // {
    //     $tickets = Tickets::with([
    //             'department:id,name',
    //             'location:id,name',
    //             'submit_by:id,name',
    //             'assign_to:id,name',
    //         ])->where('status', 'Closed')->paginate(10);

    //     return response()->json($tickets);
    // }
    
    public function urgentTickets(Request $request)
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $tickets = Tickets::with([
            'department:id,name',
            'location:id,name',
            'submit_by:id,name',
            'assign_to:id,name',
        ])
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->where('priority', 'Urgent')->paginate(10);
        return response()->json($tickets);
    }

    public function resolutionTimes(Request $request)
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $tickets = Tickets::with([
            'department:id,name',
            'location:id,name',
            'submit_by:id,name',
            'assign_to:id,name',
        ])
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->where('status', 'Closed')
            ->paginate(10);

        $tickets->getCollection()->transform(function ($ticket) {
            $ticket->resolution_time_minutes = Carbon::parse($ticket->created_at)->diffInMinutes(Carbon::parse($ticket->updated_at));
            return $ticket;
        });

        return response()->json($tickets);
    }

    public function topLocations(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Step 1: Get top location IDs by ticket count
        $topLocations = Tickets::select('location_id', DB::raw('COUNT(*) as total'))
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->groupBy('location_id')
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
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
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
        $openStatuses = ['New', 'Assigned', 'In Progress'];
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $tickets = Tickets::with([
            'department:id,name',
            'location:id,name',
            'submit_by:id,name',
            'assign_to:id,name',
        ])
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->whereIn('status', $openStatuses)->paginate(10);

        return response()->json($tickets);
    }

    public function closedTickets(Request $request)
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $tickets = Tickets::with([
            'department:id,name',
            'location:id,name',
            'submit_by:id,name',
            'assign_to:id,name',
        ])
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->where('status', 'Closed')->paginate(10);

        return response()->json($tickets);
    }


    /**
     * KPI: Technician and Department Performance Report
     */
    public function kpiReport(Request $request)
    {
        $closedTickets = Tickets::where('status', 'Closed')
            ->whereNotNull('assigned_at')
            ->whereNotNull('closed_at')
            ->get();

        $technicianStats = [];
        $departmentStats = [];

        foreach ($closedTickets as $ticket) {
            $duration = $ticket->assigned_at->diffInMinutes($ticket->closed_at);

            // Tech stats
            if ($ticket->assignee_id) {
                if (!isset($technicianStats[$ticket->assignee_id])) {
                    $technicianStats[$ticket->assignee_id] = ['name' => $ticket->assign_to->name ?? 'Unknown', 'total_minutes' => 0, 'count' => 0];
                }
                $technicianStats[$ticket->assignee_id]['total_minutes'] += $duration;
                $technicianStats[$ticket->assignee_id]['count']++;
            }

            // Dept stats
            if (!isset($departmentStats[$ticket->department_id])) {
                $departmentStats[$ticket->department_id] = ['name' => $ticket->department->name ?? 'Unknown', 'total_minutes' => 0, 'count' => 0];
            }
            $departmentStats[$ticket->department_id]['total_minutes'] += $duration;
            $departmentStats[$ticket->department_id]['count']++;
        }

        // Format for response
        $techFormatted = collect($technicianStats)->map(function($stat) {
            $avg = $stat['count'] > 0 ? round($stat['total_minutes'] / $stat['count']) : 0;
            return [
                'technician' => $stat['name'],
                'tickets_resolved' => $stat['count'],
                'avg_resolution_minutes' => $avg,
                'avg_resolution_human' => floor($avg / 60) . 'h ' . ($avg % 60) . 'm'
            ];
        })->values();

        return response()->json([
            'technicians' => $techFormatted,
            'departments' => collect($departmentStats)->map(function($stat) {
                $avg = $stat['count'] > 0 ? round($stat['total_minutes'] / $stat['count']) : 0;
                return [
                    'department' => $stat['name'],
                    'avg_resolution_minutes' => $avg
                ];
            })->values()
        ]);
    }

    /**
     * SLA Breach Report: List all overdue tickets
     */
    public function slaBreachReport(Request $request)
    {
        $overdue = Tickets::with(['department:id,name', 'location:id,name', 'assign_to:id,name'])
            ->where('is_overdue', 1)
            ->orderBy('due_at', 'asc')
            ->paginate(20);

        return response()->json($overdue);
    }

    /**
     * Trend Analysis: Ticket volume over last 6 months
     */
    public function volumeTrendReport(Request $request)
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6)->startOfMonth();
        
        $trends = Tickets::select(
                DB::raw('count(id) as total'), 
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month")
            )
            ->where('created_at', '>=', $sixMonthsAgo)
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json($trends);
    }

}

