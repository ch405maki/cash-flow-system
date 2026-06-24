<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequestItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'details' => 'required|array|min:1',
            'details.*.item_id' => 'nullable|integer',
            'details.*.quantity' => 'required|numeric|min:1',
            'details.*.unit' => 'required|string|max:20',
            'details.*.item_description' => 'required|string|max:255',
        ];
    }
}
