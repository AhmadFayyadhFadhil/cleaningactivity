<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-areas');
    }

    public function rules(): array
    {
        return [
            'area_code' => 'required|string|unique:areas,area_code|max:50',
            'area_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
'floor' => 'required',
            'building' => 'required|string|max:255',
            'pic_user_id' => 'required|exists:users,id',
'status' => 'required|string',
'schedule_frequency' => 'required|string',
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
