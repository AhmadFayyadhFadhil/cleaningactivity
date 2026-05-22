<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AreaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'area_code' => $this->area_code,
            'area_name' => $this->area_name,
            'location' => $this->location,
            'floor' => $this->floor,
            'building' => $this->building,
            'pic' => [
                'id' => $this->pic?->id,
                'name' => $this->pic?->name,
                'email' => $this->pic?->email,
            ],
            'status' => $this->status,
            'schedule_frequency' => $this->schedule_frequency,
            'total_schedules' => $this->schedules_count ?? 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
