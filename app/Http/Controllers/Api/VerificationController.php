<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduleResource;
use App\Models\CleaningSchedule;
use App\Models\CleaningVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VerificationController extends Controller
{
    /**
     * Display pending verifications.
     */
    public function pending(): AnonymousResourceCollection
    {
        $schedules = CleaningSchedule::doesntHave('verification')
            ->where('status', 'completed')
            ->with('area', 'assignedTo', 'supervisor')
            ->withCount('checklists')
            ->orderByDesc('schedule_date')
            ->paginate(15);

        return ScheduleResource::collection($schedules);
    }

    /**
     * Approve verification.
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $verified = $request->validate([
            'notes' => 'nullable|string',
            'findings' => 'nullable|string',
        ]);

        $schedule = CleaningSchedule::findOrFail($id);

        if ($schedule->verification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jadwal ini sudah diverifikasi',
            ], 422);
        }

        $verification = CleaningVerification::create([
            'schedule_id' => $schedule->id,
            'verified_by_id' => auth()->id(),
            'verification_status' => 'approved',
            'notes' => $verified['notes'] ?? null,
            'findings' => $verified['findings'] ?? null,
            'verified_at' => now(),
        ]);

        $schedule->update(['status' => 'completed']);

        return response()->json([
            'status' => 'success',
            'message' => 'Verifikasi berhasil disetujui',
            'data' => [
                'verification_id' => $verification->id,
                'schedule' => new ScheduleResource($schedule->fresh('area', 'assignedTo', 'supervisor')),
            ],
        ], 200);
    }

    /**
     * Reject verification.
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'required|string',
            'findings' => 'nullable|string',
        ]);

        $schedule = CleaningSchedule::findOrFail($id);

        if ($schedule->verification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jadwal ini sudah diverifikasi',
            ], 422);
        }

        $verification = CleaningVerification::create([
            'schedule_id' => $schedule->id,
            'verified_by_id' => auth()->id(),
            'verification_status' => 'rejected',
            'notes' => $validated['notes'],
            'findings' => $validated['findings'] ?? null,
            'verified_at' => now(),
        ]);

        $schedule->update(['status' => 'in-progress']);

        return response()->json([
            'status' => 'success',
            'message' => 'Verifikasi berhasil ditolak, jadwal kembali ke status in-progress',
            'data' => [
                'verification_id' => $verification->id,
                'schedule' => new ScheduleResource($schedule->fresh('area', 'assignedTo', 'supervisor')),
            ],
        ], 200);
    }
}
