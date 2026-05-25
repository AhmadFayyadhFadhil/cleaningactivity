<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChecklistItemRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $this->merge([
                'status' => strtolower((string) $this->input('status')),
            ]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()->can('manage-checklist-items') || $this->user()->can('manage-checklists');
    }

    public function rules(): array
    {
        return [
            'item_code' => 'required|string|unique:checklist_items,item_code|max:50',
            'item_name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'instruction' => 'nullable|string',
'status' => 'required|string|in:active,inactive',




        ];
    }

    public function messages(): array
    {
        return [
            'item_code.unique' => 'Kode item sudah terdaftar',
            'status.in' => 'Status harus active atau inactive',
        ];
    }
}
