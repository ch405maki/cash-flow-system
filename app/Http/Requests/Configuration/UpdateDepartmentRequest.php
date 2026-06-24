<?php

namespace App\Http\Requests\Configuration;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $departmentId = $this->route('department')?->id;

        return [
            'department_name' => 'required|string|max:255|unique:departments,department_name,' . $departmentId,
            'department_description' => 'nullable|string|max:500',
        ];
    }
}
