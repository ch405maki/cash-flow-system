<?php

namespace App\Http\Requests\Configuration;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $accountId = $this->route('account')?->id;

        return [
            'account_title' => 'required|string|max:255|unique:accounts,account_title,' . $accountId,
        ];
    }
}
