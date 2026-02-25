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
        $userId = $user->id;
        $today = now()->toDateString();

        $query = PettyCash::with(['items', 'user.department', 'approvals']);

        /**
         * ROLE-BASED VISIBILITY
         */
        if ($user->role === 'accounting') {

            // Accounting sees requested + approved liquidation
            $query->whereIn('status', ['requested', 'approved liquidation']);

        } elseif ($user->role === 'audit') {

            // Audit logic (merged from second controller)
            $query->whereNotIn('status', ['draft', 'for liquidation', 'requested'])
                ->where(function ($q) use ($userId) {

                    // Case 1: No rejection remarks yet
                    $q->whereDoesntHave('approvals', function ($sub) use ($userId) {
                        $sub->where('user_id', $userId)
                            ->whereNotNull('remarks');
                    })

                    // OR Case 2: Has remarks BUT status is submitted
                    ->orWhere(function ($sub) {
                        $sub->where('status', 'submitted');
                    });
                });

        } else {

            // Regular users → department only
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        $pettyCash = $query
            ->orderBy('created_at', 'desc')
            ->get();

        /**
         * TODAY'S THRESHOLDS (department-based)
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
        return Inertia::render('PettyCash/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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

        // Generate next voucher number (CAV or PCV)
        $pcvNo = $this->generateNextPcvNo($hasCashAdvance);

        // Determine status
        $status = $hasCashAdvance ? 'requested' : $validated['status'];

        // Create petty cash record
        $pettyCash = PettyCash::create([
            'pcv_no'   => $pcvNo,
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
        $pettyCash->load(['items', 'approvals.user']);

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

        $accounts = Account::orderBy('account_title')->get();

        return Inertia::render('PettyCash/View', [
            'pettyCash' => $pettyCash,
            'accounts' => $accounts,
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
            'items.*.id' => 'sometimes|exists:petty_cash_items,id',
            'items.*.type' => 'required_with:items|string',
            'items.*.particulars' => 'required_with:items|string',
            'items.*.date' => 'required_with:items|date',
            'items.*.liquidation_for_date' => 'nullable|date',
            'items.*.amount' => 'required_with:items|numeric|min:0',
            'items.*.receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'deleted_items' => 'nullable|array',
            'deleted_items.*' => 'exists:petty_cash_items,id',
        ]);

        // Update Petty Cash main fields
        $pettyCash->update([
            'paid_to' => $validated['paid_to'] ?? $pettyCash->paid_to,
            'status' => $validated['status'] ?? $pettyCash->status,
            'date' => $validated['date'] ?? $pettyCash->date,
            'remarks' => $validated['remarks'] ?? $pettyCash->remarks,
        ]);

        // Delete removed items
        if (!empty($validated['deleted_items'])) {
            PettyCashItem::whereIn('id', $validated['deleted_items'])->delete();
        }

        $savedItems = [];

        // Process items
        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $itemData) {
                // Handle receipt upload
                $receiptPath = null;
                if (isset($itemData['receipt']) && $itemData['receipt'] instanceof \Illuminate\Http\UploadedFile) {
                    $receiptPath = $itemData['receipt']->store('petty_cash_receipts', 'public');
                }

                $itemPayload = [
                    'type' => $itemData['type'],
                    'particulars' => $itemData['particulars'],
                    'date' => $itemData['date'],
                    'liquidation_for_date' => $itemData['liquidation_for_date'] ?? null,
                    'amount' => $itemData['amount'],
                ];

                if ($receiptPath) {
                    $itemPayload['receipt'] = $receiptPath;
                }

                // Update or create
                if (!empty($itemData['id'])) {
                    $item = PettyCashItem::find($itemData['id']);
                    if ($item) {
                        $item->update($itemPayload);
                        $savedItems[] = $item->fresh();
                    }
                } else {
                    $itemPayload['petty_cash_id'] = $pettyCash->id;
                    $item = PettyCashItem::create($itemPayload);
                    $savedItems[] = $item;
                }
            }
        }

        // Return Inertia response with flash message
        return redirect()->back()->with([
            'success' => 'Petty cash voucher updated successfully.',
            'petty_cash' => $pettyCash->fresh()->load('items'),
            'saved_item' => count($savedItems) === 1 ? $savedItems[0] : null
        ]);
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

    private function generateNextPcvNo(bool $isCashAdvance): string
    {
        $now = Carbon::now();
        $yearMonth = $now->format('Ym');
        $prefix = $isCashAdvance ? 'CAV' : 'PCV';
        
        // Get the latest record with this prefix (any date) to get the highest number
        $latest = PettyCash::where('pcv_no', 'like', "{$prefix}-%")
            ->orderBy('pcv_no', 'desc')
            ->first();
        
        $nextCounter = 1;
        
        if ($latest && preg_match("/{$prefix}-(\d{6})-(\d{4})$/", $latest->pcv_no, $matches)) {
            $lastYearMonth = $matches[1];
            $lastCounter = (int) $matches[2];
            
            // If it's a new month, reset counter to 1
            if ($lastYearMonth !== $yearMonth) {
                $nextCounter = 1;
            } else {
                $nextCounter = $lastCounter + 1;
            }
        }
        
        // Double-check if the generated number already exists (for race conditions)
        $generatedNo = "{$prefix}-{$yearMonth}-" . str_pad($nextCounter, 4, '0', STR_PAD_LEFT);
        
        // If it somehow exists, increment until we find a unique one
        while (PettyCash::where('pcv_no', $generatedNo)->exists()) {
            $nextCounter++;
            $generatedNo = "{$prefix}-{$yearMonth}-" . str_pad($nextCounter, 4, '0', STR_PAD_LEFT);
        }
        
        return $generatedNo;
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
