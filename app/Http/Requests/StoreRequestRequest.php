<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purpose' => 'required|string|max:500',
            'status' => 'required|in:pending,approved,rejected',
            'department_id' => 'required|exists:departments,id',
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'nullable|integer',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit' => 'required|string|max:20',
            'items.*.item_description' => 'required|string|max:255',
        ];
    }
}
