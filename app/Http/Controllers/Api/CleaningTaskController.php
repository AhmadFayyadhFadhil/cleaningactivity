<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduleResource;
use App\Models\CleaningSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CleaningTaskController extends Controller
{
    /**
     * Display my tasks (for cleaning service staff).
     */
    public function myTasks(): AnonymousResourceCollection
    {
        $tasks = CleaningSchedule::where('assigned_to_id', auth()->id())
            ->with('area', 'assignedTo', 'supervisor')
            ->withCount('checklists')
            ->orderByDesc('schedule_date')
            ->paginate(15);

        return ScheduleResource::collection($tasks);
    }

    /**
     * Display the specified task.
     */
    public function show(string $id): JsonResponse
    {
        $task = CleaningSchedule::where('assigned_to_id', auth()->id())
            ->with('area', 'assignedTo', 'supervisor', 'checklists', 'evidence', 'verification')
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => new ScheduleResource($task),
        ], 200);
    }

    /**
     * Mark task as completed.
     */
    public function complete(string $id): JsonResponse
    {
        $task = CleaningSchedule::where('assigned_to_id', auth()->id())
            ->findOrFail($id);

        if ($task->status === 'completed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Task sudah diselesaikan sebelumnya',
            ], 422);
        }

        $task->update(['status' => 'completed']);

        return response()->json([
            'status' => 'success',
            'message' => 'Task berhasil diselesaikan',
            'data' => new ScheduleResource($task->fresh('area', 'assignedTo', 'supervisor')),
        ], 200);
    }
}
