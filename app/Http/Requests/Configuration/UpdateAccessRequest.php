<?php

namespace App\Http\Requests\Configuration;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'program_name' => 'sometimes|string|max:255',
            'access_level' => 'sometimes|string|max:255',
        ];
    }
}
