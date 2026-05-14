<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'po_no' => 'required|string|unique:purchase_orders',
            'payee' => 'required|string|max:255',
            'tin_no' => 'nullable|string|max:255',
            'check_payable_to' => 'nullable|string|max:255',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'purpose' => 'required|string|max:500',
            'status' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'account_id' => 'required|exists:accounts,id',
            'canvas_id' => 'nullable|exists:canvases,id',
            'user_id' => 'required|exists:users,id',
        ];
    }
}
