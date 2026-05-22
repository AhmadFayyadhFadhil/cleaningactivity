<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-schedules');
    }

    public function rules(): array
    {
        return [
            'area_id' => 'required|exists:areas,id',
            'schedule_date' => 'required|date|after_or_equal:today',
            'schedule_time' => 'required|date_format:H:i',
            'assigned_to_id' => 'required|exists:users,id',
            'supervisor_id' => 'nullable|exists:users,id',
            'status' => 'required|in:scheduled,in-progress,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
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
