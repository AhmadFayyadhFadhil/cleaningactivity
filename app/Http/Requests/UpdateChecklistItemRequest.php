<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChecklistItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-checklist-items') || $this->user()->can('manage-checklists');
    }

    public function rules(): array
    {
        $item = $this->route('checklistItem');
        $itemId = $item instanceof \App\Models\ChecklistItem ? $item->id : $item;

        if ($this->has('status')) {
            $this->merge([
                'status' => strtolower((string) $this->input('status')),
            ]);
        }

        return [
            'item_code' => 'sometimes|required|string|unique:checklist_items,item_code,' . $itemId . '|max:50',

            'item_name' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
            'instruction' => 'nullable|string',

            'status' => 'sometimes|required|string|in:Active,Inactive,active,inactive',


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
