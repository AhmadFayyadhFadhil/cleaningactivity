<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChecklistItemRequest;
use App\Http\Requests\UpdateChecklistItemRequest;
use App\Http\Resources\ChecklistItemResource;
use App\Models\ChecklistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChecklistItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $items = ChecklistItem::withCount('submissions')
            ->paginate(15);

        return ChecklistItemResource::collection($items);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChecklistItemRequest $request): JsonResponse
    {
        $item = ChecklistItem::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Item checklist berhasil dibuat',
            'data' => new ChecklistItemResource($item),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ChecklistItem $checklistItem): JsonResponse
    {
        $checklistItem->loadCount('submissions');

        return response()->json([
            'status' => 'success',
            'data' => new ChecklistItemResource($checklistItem),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChecklistItemRequest $request, ChecklistItem $checklistItem): JsonResponse
    {
        $checklistItem->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Item checklist berhasil diperbarui',
            'data' => new ChecklistItemResource($checklistItem),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChecklistItem $checklistItem): JsonResponse
    {
        $checklistItem->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Item checklist berhasil dihapus',
        ], 200);
    }
}
