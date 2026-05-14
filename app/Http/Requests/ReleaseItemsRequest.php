<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReleaseItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $requestId = $this->route('request')?->id;

        return [
            'items' => 'required|array|min:1',
            'items.*.request_detail_id' => [
                'required',
                Rule::exists('request_details', 'id')->where('request_id', $requestId),
            ],
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'signature' => 'nullable|array',
            'signature.image' => 'nullable|string',
            'signature.signer_id' => 'nullable|integer',
            'signature.signer_name' => 'nullable|string',
            'signature.signed_at' => 'nullable|date',
        ];
    }
}
