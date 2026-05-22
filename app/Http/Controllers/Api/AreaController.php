<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAreaRequest;
use App\Http\Requests\UpdateAreaRequest;
use App\Http\Resources\AreaResource;
use App\Models\Area;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $areas = Area::with('pic')
            ->withCount('schedules')
            ->paginate(15);

        return AreaResource::collection($areas);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAreaRequest $request): JsonResponse
    {
        $area = Area::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Area berhasil dibuat',
            'data' => new AreaResource($area->load('pic')),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Area $area): JsonResponse
    {
        $area->load('pic', 'schedules');

        return response()->json([
            'status' => 'success',
            'data' => new AreaResource($area),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAreaRequest $request, Area $area): JsonResponse
    {
        $area->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Area berhasil diperbarui',
            'data' => new AreaResource($area->fresh('pic')),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Area $area): JsonResponse
    {
        $area->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Area berhasil dihapus',
        ], 200);
    }
}
