<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'area' => [
                'id' => $this->area?->id,
                'area_code' => $this->area?->area_code,
                'area_name' => $this->area?->area_name,
            ],
            'schedule_date' => $this->schedule_date?->format('Y-m-d'),
            'schedule_time' => $this->schedule_time?->format('H:i'),
            'assigned_to' => [
                'id' => $this->assignedTo?->id,
                'name' => $this->assignedTo?->name,
                'email' => $this->assignedTo?->email,
            ],
            'supervisor' => [
                'id' => $this->supervisor?->id,
                'name' => $this->supervisor?->name,
                'email' => $this->supervisor?->email,
            ],
            'status' => $this->status,
            'priority' => $this->priority,
            'notes' => $this->notes,
            'total_checklists' => $this->checklists_count ?? 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
