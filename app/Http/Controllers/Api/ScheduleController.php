<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\CleaningSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-schedules|manage-schedules|create-schedules')->only(['index', 'show']);
        $this->middleware('permission:manage-schedules|create-schedules')->except(['index', 'show']);


    }

    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $schedules = CleaningSchedule::with('area', 'assignedTo', 'supervisor')
            ->withCount('checklists')
            ->paginate(15);

        return ScheduleResource::collection($schedules);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreScheduleRequest $request): JsonResponse
    {
        $schedule = CleaningSchedule::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal cleaning berhasil dibuat',
            'data' => new ScheduleResource($schedule->load('area', 'assignedTo', 'supervisor')),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CleaningSchedule $schedule): JsonResponse
    {
        $schedule->load('area', 'assignedTo', 'supervisor', 'checklists', 'evidence', 'verification');

        return response()->json([
            'status' => 'success',
            'data' => new ScheduleResource($schedule),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateScheduleRequest $request, CleaningSchedule $schedule): JsonResponse
    {
        $schedule->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal cleaning berhasil diperbarui',
            'data' => new ScheduleResource($schedule->fresh('area', 'assignedTo', 'supervisor')),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CleaningSchedule $schedule): JsonResponse
    {
        $schedule->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal cleaning berhasil dihapus',
        ], 200);
    }
}
