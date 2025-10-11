<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Carbon;

use App\Models\PettyCash;
use App\Models\PettyCashItem;
use App\Models\PettyCashFund;
use App\Models\User;
use App\Models\Department;

class PettyCashController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = now()->toDateString();

        $query = PettyCash::with(['items', 'user.department']);

        if ($user->role === 'accounting') {
            // Accounting sees all requested petty cash
            $query->whereIn('status', ['requested', 'approved liquidation']);
        } else {
            // Other users only see their own department’s
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        $pettyCash = $query->orderBy('created_at', 'desc')->get();

        /**
         * ✅ Compute today's totals for this user's department
         */
        $departmentPettyCashToday = PettyCash::whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            })
            ->whereDate('date', $today)
            ->with('items')
            ->get();

        $cashAdvanceTotal = $departmentPettyCashToday->flatMap->items
            ->where('type', 'Cash Advance')
            ->sum('amount');

        $reimbursementTotal = $departmentPettyCashToday->flatMap->items
            ->where('type', 'Reimbursement')
            ->sum('amount');

        /**
         * ✅ Define thresholds
         */
        $thresholds = [
            'cash_advance' => [
                'limit' => 150000,
                'used' => $cashAdvanceTotal,
                'remaining' => max(0, 150000 - $cashAdvanceTotal),
            ],
            'reimbursement' => [
                'limit' => 5000,
                'used' => $reimbursementTotal,
                'remaining' => max(0, 5000 - $reimbursementTotal),
            ],
        ];

        return Inertia::render('PettyCash/Index', [
            'pettyCash' => $pettyCash,
            'thresholds' => $thresholds,
            'today' => $today,
        ]);
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
            'status' => 'required|string',
            'date' => 'required|date',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|string',
            'items.*.particulars' => 'required|string',
            'items.*.date' => 'required|date',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $user = Auth::user();
        $departmentId = $user->department_id;
        $date = $validated['date'];

        // Load department's petty cash records for the day
        $pettyCashToday = PettyCash::whereHas('user', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })
            ->whereDate('date', $date)
            ->with('items')
            ->get();

        // Calculate totals for each type separately
        $totalCashAdvance = $pettyCashToday->flatMap->items
            ->where('type', 'Cash Advance')
            ->sum('amount');

        $totalReimbursement = $pettyCashToday->flatMap->items
            ->where('type', 'Reimbursement')
            ->sum('amount');

        // New totals (include submitted items)
        $newCashAdvanceTotal = $totalCashAdvance + collect($validated['items'])
            ->where('type', 'Cash Advance')
            ->sum('amount');

        $newReimbursementTotal = $totalReimbursement + collect($validated['items'])
            ->where('type', 'Reimbursement')
            ->sum('amount');

        // Threshold validation per type
        if ($newCashAdvanceTotal > 150000) {
            return back()->withErrors([
                'threshold' => 'Department daily Cash Advance threshold of ₱150,000 exceeded.'
            ]);
        }

        if ($newReimbursementTotal > 5000) {
            return back()->withErrors([
                'threshold' => 'Department daily Reimbursement threshold of ₱5,000 exceeded.'
            ]);
        }

        // Determine if this request contains any cash advance
        $hasCashAdvance = collect($validated['items'])
            ->contains(fn($item) => strtolower($item['type']) === 'cash advance');

        $status = $hasCashAdvance ? 'requested' : $validated['status'];

        // Create petty cash record
        $pettyCash = PettyCash::create([
            'pcv_no'   => $validated['pcv_no'],
            'user_id'  => $user->id,
            'paid_to'  => $validated['paid_to'],
            'status'   => $status,
            'date'     => $validated['date'],
            'remarks'  => $validated['remarks'] ?? null,
        ]);

        // Save each item
        foreach ($validated['items'] as $item) {
            $receiptPath = isset($item['receipt'])
                ? $item['receipt']->store('petty_cash_receipts', 'public')
                : null;

            $pettyCash->items()->create([
                'particulars' => $item['particulars'],
                'type' => $item['type'],
                'date' => $item['date'],
                'amount' => $item['amount'],
                'receipt' => $receiptPath,
            ]);
        }

        return back()->with('success', 'Petty cash voucher created successfully.');
    }


    public function edit(PettyCash $pettyCash)
    {
        $pettyCash->load('items');

        return Inertia::render('PettyCash/Edit', [
            'pettyCash' => $pettyCash,
        ]);
    }

    public function view(PettyCash $pettyCash)
    {
        $pettyCash->load([
            'items',
            'approvals.user', 
            'distributionExpenses',
        ]);

        return Inertia::render('PettyCash/View', [
            'pettyCash' => $pettyCash,
        ]);
    }

    public function update(Request $request, PettyCash $pettyCash)
    {
        $validated = $request->validate([
            'paid_to' => 'sometimes|string',
            'status' => 'sometimes|string',
            'date' => 'sometimes|date',
            'remarks' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.type' => 'required_with:items|string',
            'items.*.particulars' => 'required_with:items|string',
            'items.*.date' => 'required_with:items|date',
            'items.*.liquidation_for_date' => 'nullable:items|date',
            'items.*.amount' => 'required_with:items|numeric|min:0',
            'items.*.receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // 🔄 Only update fields that belong to PettyCash
        $pettyCash->update(array_filter([
            'paid_to' => $validated['paid_to'] ?? null,
            'status' => $validated['status'] ?? null,
            'date' => $validated['date'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
        ]));

        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $item) {
                $receiptPath = isset($item['receipt'])
                    ? $item['receipt']->store('petty_cash_receipts', 'public')
                    : null;

                PettyCashItem::create([
                    'petty_cash_id' => $pettyCash->id,
                    'type' => $item['type'],
                    'particulars' => $item['particulars'],
                    'date' => $item['date'],
                    'liquidation_for_date' => $item['liquidation_for_date'],
                    'amount' => $item['amount'],
                    'receipt' => $receiptPath,
                ]);
            }
        }

        return back()->with('success', 'Petty cash voucher updated successfully.');
    }

    public function destroy(PettyCashItem $item)
    {
        if ($item->receipt) {
            \Storage::disk('public')->delete($item->receipt);
        }
        $item->delete();

        return back()->with('success', 'Item deleted successfully.');
    }

    public function pettyCashFund()
    {
        $user = auth()->user();

        if (!in_array($user->role, ['admin', 'accounting'])) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $pettyCashFunds = PettyCashFund::with(['user', 'department'])
            ->orderBy('created_at', 'desc')
            ->get();

        $users = User::select('id', 'first_name', 'last_name')->get();
        $departments = Department::select('id', 'department_name')->get();

        return Inertia::render('PettyCash/Fund/Index', [
            'pettyCashFunds' => $pettyCashFunds,
            'users' => $users,
            'departments' => $departments,
        ]);
    }

    public function storeFund(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'fund_amount' => 'required|numeric|min:1',
        ]);

        // Assign fund_amount to fund_balance initially
        $validated['fund_balance'] = $validated['fund_amount'];

        PettyCashFund::create($validated);

        return redirect()->back()->with('success', 'Petty Cash Fund assigned successfully.');
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

    public function submit(Request $request, $id)
    {
        $pettyCash = PettyCash::findOrFail($id);
        $pettyCash->status = 'submitted';
        $pettyCash->updated_at = now();
        $pettyCash->save();

        return redirect()->back()->with('success', 'Petty Cash submitted for audit.');
    }

}
