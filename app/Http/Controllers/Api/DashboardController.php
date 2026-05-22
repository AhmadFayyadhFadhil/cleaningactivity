<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\CleaningSchedule;
use App\Models\CleaningVerification;
use App\Models\FollowUpTask;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Get dashboard summary statistics.
     */
    public function summary(): JsonResponse
    {
        $totalAreas = Area::count();
        $totalSchedules = CleaningSchedule::count();
        $completedSchedules = CleaningSchedule::where('status', 'completed')->count();
        $pendingVerifications = CleaningSchedule::doesntHave('verification')
            ->where('status', 'completed')
            ->count();
        $totalFollowUps = FollowUpTask::count();
        $openFollowUps = FollowUpTask::where('status', '!=', 'resolved')->count();

        // Calculate completion rate
        $completionRate = $totalSchedules > 0 
            ? round(($completedSchedules / $totalSchedules) * 100, 2)
            : 0;

        // Get recent schedules
        $recentSchedules = CleaningSchedule::with('area', 'assignedTo')
            ->orderByDesc('schedule_date')
            ->limit(5)
            ->get()
            ->map(fn($schedule) => [
                'id' => $schedule->id,
                'area' => $schedule->area?->area_name,
                'assigned_to' => $schedule->assignedTo?->name,
                'status' => $schedule->status,
                'date' => $schedule->schedule_date?->format('Y-m-d'),
            ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'summary' => [
                    'total_areas' => $totalAreas,
                    'total_schedules' => $totalSchedules,
                    'completed_schedules' => $completedSchedules,
                    'completion_rate' => $completionRate . '%',
                    'pending_verifications' => $pendingVerifications,
                    'total_follow_ups' => $totalFollowUps,
                    'open_follow_ups' => $openFollowUps,
                ],
                'recent_schedules' => $recentSchedules,
            ],
        ], 200);
    }

    /**
     * Get area status summary.
     */
    public function areaStatus(): JsonResponse
    {
        $areaStatus = Area::with('pic')
            ->withCount([
                'schedules',
                'schedules as completed_schedules' => function ($query) {
                    $query->where('status', 'completed');
                },
                'schedules as in_progress_schedules' => function ($query) {
                    $query->where('status', 'in-progress');
                },
            ])
            ->get()
            ->map(function ($area) {
                $totalSchedules = $area->schedules_count ?? 0;
                $completedSchedules = $area->completed_schedules_count ?? 0;
                $completionRate = $totalSchedules > 0 
                    ? round(($completedSchedules / $totalSchedules) * 100, 2)
                    : 0;

                return [
                    'id' => $area->id,
                    'area_code' => $area->area_code,
                    'area_name' => $area->area_name,
                    'building' => $area->building,
                    'floor' => $area->floor,
                    'pic' => $area->pic?->name,
                    'status' => $area->status,
                    'schedule_frequency' => $area->schedule_frequency,
                    'total_schedules' => $totalSchedules,
                    'completed_schedules' => $completedSchedules,
                    'in_progress_schedules' => $area->in_progress_schedules_count ?? 0,
                    'completion_rate' => $completionRate . '%',
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $areaStatus,
        ], 200);
    }
}
