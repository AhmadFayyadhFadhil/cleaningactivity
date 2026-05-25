<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-schedules') || $this->user()->can('manage-schedules');
    }

    public function rules(): array
    {
        return [
            'area_id' => 'sometimes|required|exists:areas,id',
            'schedule_date' => 'sometimes|required|date|after_or_equal:today',
            'schedule_time' => 'sometimes|required|date_format:H:i',
            'assigned_to_id' => 'sometimes|required|exists:users,id',
            'supervisor_id' => 'nullable|exists:users,id',
            'status' => 'sometimes|required|in:scheduled,in-progress,completed,cancelled',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'area_id.exists' => 'Area tidak ditemukan',
            'assigned_to_id.exists' => 'User yang ditugaskan tidak ditemukan',
            'supervisor_id.exists' => 'Supervisor tidak ditemukan',
            'schedule_date.after_or_equal' => 'Tanggal jadwal harus hari ini atau yang akan datang',
        ];
    }
}
