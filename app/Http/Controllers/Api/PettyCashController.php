<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PettyCash;
use App\Models\PettyCashItem;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PettyCashController extends Controller
{
    public function index()
    {
        // Generate next PCV number
        $nextPcvNo = $this->generateNextPcvNo();

        return Inertia::render('PettyCash/Index', [
            'nextPcvNo' => $nextPcvNo,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pcv_no' => 'required|string|unique:petty_cash,pcv_no',
            'paid_to' => 'required|string',
            'type' => 'required|string',
            'status' => 'required|string',
            'date' => 'required|date',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.particulars' => 'required|string',
            'items.*.date' => 'required|date',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $pettyCash = PettyCash::create([
            'pcv_no' => $validated['pcv_no'],
            'user_id' => Auth::id(),
            'paid_to' => $validated['paid_to'],
            'type' => $validated['type'],
            'status' => $validated['status'],
            'date' => $validated['date'],
            'remarks' => $validated['remarks'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            $receiptPath = null;
            if (isset($item['receipt'])) {
                $receiptPath = $item['receipt']->store('petty_cash_receipts', 'public');
            }

            PettyCashItem::create([
                'petty_cash_id' => $pettyCash->id,
                'particulars' => $item['particulars'],
                'date' => $item['date'],
                'amount' => $item['amount'],
                'receipt' => $receiptPath,
            ]);
        }

        return back()->with('success', 'Petty cash voucher created successfully.');
    }

    private function generateNextPcvNo(): string
    {
        $latest = PettyCash::latest('id')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        return 'PCV-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }
}
