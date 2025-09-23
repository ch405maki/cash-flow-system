<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PettyCash;
use App\Models\PettyCashItem;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Carbon;

class PettyCashController extends Controller
{
    public function index()
    {
        $pettyCash = PettyCash::with('items')->orderBy('date', 'desc')->get();
        return Inertia::render('PettyCash/Index', [ 'pettyCash' => $pettyCash ]);
    }

    public function create()
    {
        // Generate next PCV number
        $nextPcvNo = $this->generateNextPcvNo();

        return Inertia::render('PettyCash/Create', [
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
        $now = Carbon::now();
        $yearMonth = $now->format('Ym'); // e.g. 202509

        // Get last PCV number for this month
        $latest = PettyCash::whereYear('date', $now->year)
            ->whereMonth('date', $now->month)
            ->orderByDesc('pcv_no')
            ->first();

        $nextCounter = 1;

        if ($latest && preg_match('/PCV-\d{6}-(\d{4})$/', $latest->pcv_no, $matches)) {
            $lastCounter = (int) $matches[1];
            $nextCounter = $lastCounter + 1;
        }

        return 'PCV-' . $yearMonth . '-' . str_pad($nextCounter, 4, '0', STR_PAD_LEFT);
    }
}
