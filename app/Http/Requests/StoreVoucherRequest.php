<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'po_id' => 'nullable|exists:purchase_orders,id',
            'voucher_no' => 'required|string|unique:vouchers,voucher_no',
            'issue_date' => 'nullable|date',
            'tin_no' => 'nullable|string',
            'payment_date' => 'nullable|date',
            'delivery_date' => 'nullable|date',
            'voucher_date' => 'required|date',
            'purpose' => 'required|string|max:500',
            'payee' => 'required|string|max:255',
            'check_no' => 'nullable|string|max:500',
            'check_payable_to' => 'required|string|max:500',
            'check_amount' => 'required|numeric|min:0',
            'status' => 'required|in:forAudit,forCheck,rejected,draft',
            'type' => 'required|in:cash,salary',
            'user_id' => 'required|exists:users,id',
            'check' => 'required|array|min:1',
            'check.*.amount' => 'required|numeric|min:0',
            'check.*.rate' => 'nullable|numeric|min:0',
            'check.*.hours' => 'nullable|numeric|min:0',
            'check.*.charging_tag' => 'nullable|in:C,D',
            'check.*.account_id' => 'required|exists:accounts,id',
        ];
    }
}
