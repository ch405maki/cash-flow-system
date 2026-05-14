<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $voucherId = $this->route('voucher')?->id;

        return [
            'voucher_no' => [
                'required',
                'string',
                Rule::unique('vouchers', 'voucher_no')->ignore($voucherId),
            ],
            'issue_date' => 'nullable|date',
            'payment_date' => 'nullable|date',
            'check_date' => 'nullable|date',
            'delivery_date' => 'nullable|date',
            'voucher_date' => 'required|date',
            'purpose' => 'required|string|max:500',
            'payee' => 'required|string|max:255',
            'check_no' => 'nullable|string|max:500',
            'tin_no' => 'nullable|string|max:500',
            'remarks' => 'nullable|string|max:500',
            'check_payable_to' => 'required|string|max:500',
            'check_amount' => 'required|numeric|min:0',
            'status' => 'required|in:forAudit,forCheck,rejected,draft,return',
            'type' => 'required|in:cash,salary',
            'user_id' => 'required|exists:users,id',
            'check' => 'nullable|array',
            'check.*.amount' => 'nullable|numeric|min:0',
            'check.*.rate' => 'nullable|numeric|min:0',
            'check.*.hours' => 'nullable|numeric|min:0|max:24',
            'check.*.charging_tag' => 'nullable|in:C,D',
            'check.*.account_id' => 'required_with:check.*|exists:accounts,id',
        ];
    }
}
