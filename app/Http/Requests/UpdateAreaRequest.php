<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-areas');
    }

    public function rules(): array
    {
        $area = $this->route('area');
        $areaId = $area instanceof \App\Models\Area ? $area->id : $area;

        return [
            'area_code' => 'sometimes|required|string|unique:areas,area_code,' . $areaId . '|max:50',
            'area_name' => 'sometimes|required|string|max:255',
            'location' => 'sometimes|required|string|max:255',
'floor' => 'sometimes|required',
            'building' => 'sometimes|required|string|max:255',
            'pic_user_id' => 'sometimes|required|exists:users,id',
'status' => 'sometimes|required|string',
'schedule_frequency' => 'sometimes|required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'area_code.unique' => 'Kode area sudah terdaftar',
            'pic_user_id.exists' => 'User PIC tidak ditemukan',
            'status.in' => 'Status harus active atau inactive',
            'schedule_frequency.in' => 'Frekuensi jadwal harus daily, weekly, atau monthly',
        ];
    }
}
