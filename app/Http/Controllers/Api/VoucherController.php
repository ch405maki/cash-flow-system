<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\PurchaseOrder;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\Account;
use App\Models\Signatory;
use App\Models\User;
use App\Models\VoucherApproval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class VoucherController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        return Inertia::render('Vouchers/Index', [
            'vouchers' => Voucher::with(['user', 'details'])
                                ->whereIn('status', ['draft', 'rejected', 'unreleased', 'released', 'completed'])
                                ->get(),
            'accounts' => Account::all(),
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
                'access' => $user->access_id,
            ]
        ]);
    }

    public function create(Request $request)
    {
        $voucherNumber = $this->generateVoucherNumber();
        $poId = $request->query('po_id');
        
        $data = [
            'accounts' => Account::orderBy('account_title')->get(),
            'voucher_number' => $voucherNumber,
        ];
        
        if ($poId) {
            $purchaseOrder = PurchaseOrder::with(['department', 'account'])->find($poId);
            if ($purchaseOrder) {
                $data['purchase_order'] = $purchaseOrder;
            }
        }
        
        return Inertia::render('Vouchers/Create', $data);
    }

    // voucher store start
    public function store(Request $request): JsonResponse
    {
        try {
            $validationRules = [
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

            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            return DB::transaction(function () use ($request, $validator) {
            $validated = $validator->validated();

            // Create voucher with optional PO reference
            $voucherData = [
                'po_id' => $validated['po_id'] ?? null,  
                'voucher_no' => $validated['voucher_no'],
                'issue_date' => $validated['issue_date'] ?? null,
                'tin_no' => $validated['tin_no'] ?? null,
                'payment_date' => $validated['payment_date'] ?? null,
                'delivery_date' => $validated['delivery_date'] ?? null,
                'voucher_date' => $validated['voucher_date'],
                'purpose' => $validated['purpose'],
                'payee' => $validated['payee'],
                'check_no' => $validated['check_no'] ?? null,
                'check_payable_to' => $validated['check_payable_to'],
                'check_amount' => $validated['check_amount'],
                'status' => $validated['status'],
                'type' => $validated['type'],
                'user_id' => $validated['user_id'],
            ];

            $voucher = Voucher::create($voucherData);  
            $creatorUser = User::find($validated['user_id']); 

            // Create details if salary voucher
            if (!empty($validated['check'])) {
                foreach ($validated['check'] as $item) {
                    $voucher->details()->create([
                        'account_id' => $item['account_id'],
                        'amount' => $item['amount'],
                        'charging_tag' => $item['charging_tag'] ?? null,
                        'hours' => $item['hours'] ?? null,
                        'rate' => $item['rate'] ?? null,
                    ]);
                }
            }

            if (!empty($validated['po_id'])) {
                PurchaseOrder::where('id', $validated['po_id'])
                    ->update(['status' => 'voucherCreated']);
            }

            // Log activity
            activity()
                ->performedOn($voucher)
                ->causedBy($creatorUser)
                ->useLog('Voucher Created')
                ->withProperties([
                    'voucher_no' => $voucher->voucher_no,
                    'check_amount' => $voucher->check_amount,
                    'check_payable_to' => $voucher->check_payable_to,
                    'type' => $voucher->type,
                ])
                ->log("Created Voucher {$voucher->voucher_no}");

            // Create approval entry
            VoucherApproval::create([
                'voucher_id' => $voucher->id,
                'user_id' => $validated['user_id'],
                'status' => $validated['status'],
                'approved_at' => now(),
                'remarks' => "Voucher created #{$voucher->voucher_no}",
            ]);

            return response()->json([
                'message' => 'Voucher created successfully',
                'data' => $voucher->load(['user', 'details', 'purchaseOrder']),
                'voucher_no' => $validated['voucher_no'],
                'items_count' => $validated['type'] === 'salary' 
                    ? count($validated['check']) 
                    : 0,
                'po_id' => $validated['po_id'] ?? null
            ], 201);
        });

    } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'message' => 'Server Error',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }

    protected function createVoucher(array $data)
    {
        return Voucher::create([
            'issue_date' => $data['issue_date'],
            'payment_date' => $data['payment_date'],
            'tin_no' => $data['tin_no'] ?? null,
            'check_date' => $data['check_date'],
            'delivery_date' => $data['delivery_date'],
            'voucher_date' => $data['voucher_date'],    
            'purpose' => $data['purpose'],
            'payee' => $data['payee'],
            'check_no' => $data['check_no'],
            'check_payable_to' => $data['check_payable_to'],    
            'check_amount' => $data['check_amount'],
            'status' => $data['status'],
            'type' => $data['type'],
            'user_id' => $data['user_id'],
        ]);
    }

    protected function createVoucherDetails($voucher, array $items)
    {
        return $voucher->details()->createMany(
            collect($items)->map(function ($item) {
                return [
                    'account_id' => $item['account_id'],
                    'amount' => $item['amount'],
                    'charging_tag' => $item['charging_tag'] ?? null,
                    'hours' => $item['hours'] ?? null,
                    'rate' => $item['rate'] ?? null,
                ];
            })->toArray()
        );
    }

    protected function generateVoucherNumber(): string
    {
        $prefix = 'V-' . now()->format('Ym') . '-'; 
        $lastVoucher = Voucher::where('voucher_no', 'like', $prefix . '%')
            ->orderBy('voucher_no', 'desc')
            ->first();

        $sequence = $lastVoucher
            ? (int) str_replace($prefix, '', $lastVoucher->voucher_no) + 1
            : 1;

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
    // voucher store end

    public function edit(Voucher $voucher)
    {
        return Inertia::render('Vouchers/Edit', [
            'voucher' => $voucher->load('details.account'),
            'accounts' => Account::orderBy('account_title')->get()
        ]);
    }

    public function update(Request $request, Voucher $voucher): JsonResponse
    {
        return DB::transaction(function () use ($request, $voucher) {
            $validated = $request->validate([
                'voucher_no' => [
                    'required',
                    'string',
                    Rule::unique('vouchers', 'voucher_no')->ignore($voucher->id),
                ],
                'issue_date' => 'nullable|date',
                'payment_date' => 'nullable|date',
                'check_date' => 'required|date',
                'delivery_date' => 'nullable|date',
                'voucher_date' => 'required|date',
                'purpose' => 'required|string|max:500',
                'payee' => 'required|string|max:255',
                'check_no' => 'nullable|string|max:500',
                'remarks' => 'nullable|string|max:500',
                'check_payable_to' => 'required|string|max:500',
                'check_amount' => 'required|numeric|min:0',
                'status' => 'required|in:forAudit,forCheck,rejected,draft',
                'type' => 'required|in:cash,salary',
                'user_id' => 'required|exists:users,id',
                'check' => 'nullable|array',
                'check.*.amount' => 'nullable|numeric|min:0',
                'check.*.rate' => 'nullable|numeric|min:0',
                'check.*.hours' => 'nullable|numeric|min:0|max:24',
                'check.*.charging_tag' => 'nullable|in:C,D',
                'check.*.account_id' => 'required_with:check.*|exists:accounts,id',
            ]);

            // Lock the voucher number to the existing value
            $validated['voucher_no'] = $voucher->voucher_no;

            // ✅ Automatically update status if check number is present
            if (!empty($validated['check_no'])) {
                $validated['status'] = 'unreleased';
            }

            // Apply updates
            $voucher->update($validated);

            $this->validateCashVoucherAmount($validated);
            $this->updateType($voucher, $validated);
            $this->syncVoucherDetails($voucher, $validated);

            return $this->successResponse(
                'Voucher updated successfully',
                $voucher->fresh(['user', 'details']),
                200
            );
        });
    }

    public function uploadReceipt(Request $request, Voucher $voucher): JsonResponse
    {
        $validated = $request->validate([
            'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'issue_date' => 'nullable|date',
            'delivery_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:500',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = $validated['user_id'];

        try {
            // Handle file upload
            $file = $request->file('receipt');
            $filename = 'receipt_' . $voucher->voucher_no . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('receipts', $filename, 'public');

            // Update voucher
            $voucher->update([
                'receipt' => 'receipts/' . $filename,
                'issue_date' => $validated['issue_date'],
                'delivery_date' => $validated['delivery_date'],
                'remarks' => $validated['remarks'],
                'status' => 'completed',
            ]);

            // Log approval status
            VoucherApproval::create([
                'voucher_id' => $voucher->id,
                'user_id' => $user,
                'status' => 'completed',
                'remarks' => "Receipt uploaded for Voucher: {$voucher->voucher_no}",
                'approved_at' => now(),
            ]);

            // Activity log
            activity()
                ->performedOn($voucher)
                ->causedBy($user)
                ->useLog('Voucher Receipt Upload')
                ->withProperties([
                    'voucher_id' => $voucher->id,
                    'voucher_no' => $voucher->voucher_no,
                    'receipt' => 'receipts/' . $filename,
                    'issue_date' => $validated['issue_date'],
                    'delivery_date' => $validated['delivery_date'],
                    'remarks' => $validated['remarks'],
                    'status' => 'completed',
                ])
                ->log("Uploaded receipt and completed voucher {$voucher->voucher_no}");

            return $this->successResponse(
                'Receipt and details uploaded successfully',
                [
                    'receipt_path' => 'receipts/' . $filename,
                    'issue_date' => $validated['issue_date'],
                    'delivery_date' => $validated['delivery_date'],
                    'remarks' => $validated['remarks'],
                ],
                200
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to upload receipt: ' . $e->getMessage(), 500);
        }
    }

    public function addCheckDetails(Request $request, Voucher $voucher): JsonResponse
    {
        $validated = $request->validate([
            'check_no' => 'required|string|max:255',
            'check_date' => 'required|date',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = $validated['user_id'];

        try {
            // Update voucher with check details
            $voucher->update([
                'check_no' => $validated['check_no'],
                'check_date' => $validated['check_date'],
                'status' => 'unreleased'
            ]);

            // Fix: use $user->id instead of $user
            VoucherApproval::create([
                'voucher_id' => $voucher->id,
                'user_id' => $user,
                'status' => "unreleased",
                'remarks' => "Voucher: {$voucher->voucher_no} updated as unreleased",
                'approved_at' => now(),
            ]);

            activity()
                ->performedOn($voucher)
                ->causedBy($user)
                ->useLog('Voucher Update')
                ->withProperties([
                    'voucher_id' => $voucher->id,
                    'voucher_no' => $voucher->voucher_no,
                    'check_no' => $validated['check_no'],
                    'check_date' => $validated['check_date'],
                    'status' => 'unreleased'
                ])
                ->log("Added check details for {$voucher->voucher_no}");

            return $this->successResponse(
                'Check details added successfully',
                $voucher->fresh(),
                200
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to add check details: ' . $e->getMessage(), 500);
        }
    }
    
    public function downloadReceipt(Voucher $voucher)
    {
        if (!$voucher->receipt) {
            abort(404, 'No receipt found.');
        }

        $filePath = $voucher->receipt; // e.g., 'receipts/receipt_V-202507-0003.pdf'

        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found on disk.');
        }

        $filename = 'receipt_' . $voucher->voucher_no . '.' . pathinfo($filePath, PATHINFO_EXTENSION);

        return Storage::disk('public')->download($filePath, $filename);
    }

    public function show(Voucher $voucher): JsonResponse
    {
        return $this->successResponse(
            'Voucher retrieved successfully',
            $voucher->load(['user', 'details.account']),
            200,
            ['accounts' => Account::all()]
        );
    }

    public function view(Voucher $voucher)
    {
        $voucher->load(['user', 'details.account', 'approvals.user']);

        return Inertia::render('Vouchers/View', [ 
            'voucher' => $voucher,
            'accounts' => Account::all(),
            'authUser' => [ 
                'id' => Auth::user()->id,
                'name' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                'access_id' => Auth::user()->access_id,
                'role' => Auth::user()->role,
            ],
            'signatories' => Signatory::all(),
        ]);
    }

    public function viewWithPO($po_id)
    {
        $voucher = Voucher::with(['user', 'details.account', 'approvals.user'])->where('po_id', $po_id)->firstOrFail();

        return Inertia::render('Vouchers/View', [ 
            'voucher' => $voucher,
            'accounts' => Account::all(),
            'authUser' => [ 
                'id' => Auth::id(),
                'name' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                'access_id' => Auth::user()->access_id,
                'role' => Auth::user()->role,
            ],
            'signatories' => Signatory::all(),
        ]);
    }

    public function forDirector($id, Request $request)
    {
        $request->validate([
            'password' => 'required',
            'action' => 'required|in:forAudit,reject,released'
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        $voucher = Voucher::findOrFail($id);

        if ($voucher->status === 'forAudit') {
            return back()->withErrors(['status' => 'Voucher is already sent']);
        }

        $action = $request->input('action');
        $newStatus = match ($action) {
            'forAudit' => 'forAudit',
            'released' => 'released',
            default => 'rejected',
        };

        $message = match ($action) {
            'forAudit' => 'Voucher sent to Executive Director',
            'released' => 'Voucher marked as released',
            default => 'Voucher rejected successfully',
        };

        DB::transaction(function () use ($voucher, $newStatus, $user, $action) {
            $voucher->update(['status' => $newStatus]);

            // Update the approval entry for this user if it exists
            VoucherApproval::create([
                'voucher_id' => $voucher->id,
                'user_id' => $user->id,
                'status' => $newStatus,
                'remarks' => "Voucher: {$voucher->voucher_no} Update as {$newStatus}",
                'approved_at' => now(),
            ]);

            // Log the activity
            activity()
                ->performedOn($voucher)
                ->causedBy($user)
                ->useLog('Voucher Update')
                ->withProperties([
                    'voucher_no' => $voucher->voucher_no,
                    'action' => $action,
                    'new_status' => $newStatus,
                ])
                ->log("Voucher {$voucher->voucher_no} Updated: {$action} ");
        });

        return back()->with([
            'success' => $message,
            'voucher' => $voucher->fresh()
        ]);
    }


    public function auditreview($id, Request $request) 
    {
        $request->validate([
            'password' => 'required',
            'action' => 'required|in:approve,reject',
            'comment' => 'nullable|string|max:500', // 🆕 Optional comment
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        $voucher = Voucher::findOrFail($id);

        if ($voucher->status === 'forCheck') {
            return back()->withErrors(['status' => 'Voucher is already approved for check releasing']);
        }

        if ($voucher->status === 'rejected') {
            return back()->withErrors(['status' => 'Voucher is already rejected']);
        }

        $action = $request->input('action');
        $comment = $request->input('comment');
        $newStatus = $action === 'approve' ? 'forCheck' : 'rejected';
        $message = $action === 'approve'
            ? 'Voucher approved successfully'
            : 'Voucher rejected successfully';

        DB::transaction(function () use ($voucher, $newStatus, $user, $action, $comment) {
            $voucher->update(['status' => $newStatus]);

            VoucherApproval::create([
                'voucher_id' => $voucher->id,
                'user_id' => $user->id,
                'status' => $newStatus,
                'remarks' =>  "Voucher: {$voucher->voucher_no} updated to {$newStatus} Comment: {$comment}",
                'approved_at' => now(),
            ]);

            activity()
                ->performedOn($voucher)
                ->causedBy($user)
                ->useLog('Voucher Update')
                ->withProperties([
                    'voucher_no' => $voucher->voucher_no,
                    'action' => $action,
                    'new_status' => $newStatus,
                    'comment' => $comment,
                ])
                ->log("Voucher {$voucher->voucher_no} Updated: {$action}" . ($comment ? " | Comment: {$comment}" : ""));
        });

        return back()->with([
            'success' => $message,
            'voucher' => $voucher->fresh()
        ]);
    }


    protected function validateRequest(Request $request): array
    {
        $rules = $this->voucherValidationRules;

        // If updating, ignore unique rule for current voucher
        if ($request->isMethod('patch') || $request->isMethod('put')) {
            $voucherId = $request->route('voucher')?->id;
            if ($voucherId) {
                $rules['voucher_no'] = 'required|string|unique:vouchers,voucher_no,' . $voucherId;
            }
        }

        return $request->validate($rules);
    }

    protected function handleCashVoucherDetails(array &$validated): void
    {
        if ($validated['type'] !== 'cash' || empty($validated['check'])) {
            return;
        }

        $checkCollection = collect($validated['check']);

        // Distribute amount evenly if any amount is null
        if ($checkCollection->contains(fn($item) => is_null($item['amount']))) {
            $evenAmount = round($validated['check_amount'] / $checkCollection->count(), 2);
            $validated['check'] = $checkCollection->map(fn($item) => [
                ...$item,
                'amount' => $item['amount'] ?? $evenAmount
            ])->toArray();
        }

        // Validate total amount matches
        $sumAmount = collect($validated['check'])->sum('amount');
        if (abs($sumAmount - $validated['check_amount']) > 0.01) {
            throw ValidationException::withMessages([
                'check_amount' => 'Sum of item amounts does not match check amount'
            ]);
        }
    }

    protected function updateType(Voucher $voucher, array $validated): void
    {
        // For salary vouchers, calculate check_amount from details
        if ($validated['type'] === 'salary' && !empty($validated['check'])) {
            $validated['check_amount'] = collect($validated['check'])->sum('amount');
        }

        $voucher->update(collect($validated)->except('check')->toArray());
    }

    protected function syncVoucherDetails(Voucher $voucher, array $validated): void
    {
        $existingDetailIds = $voucher->details->pluck('id')->toArray();
        $updatedDetailIds = [];

        // Process details for both cash and salary vouchers
        if (!empty($validated['check'])) {
            foreach ($validated['check'] as $check) {
                if (isset($check['id']) && in_array($check['id'], $existingDetailIds)) {
                    // Update existing detail
                    VoucherDetail::where('id', $check['id'])
                        ->update($this->mapDetailAttributes($check));
                    $updatedDetailIds[] = $check['id'];
                } else {
                    // Create new detail
                    $newDetail = VoucherDetail::create([
                        'voucher_id' => $voucher->id,
                        ...$this->mapDetailAttributes($check)
                    ]);
                    $updatedDetailIds[] = $newDetail->id;
                }
            }
        }

        // Delete only details that weren't included in the request
        $detailsToDelete = array_diff($existingDetailIds, $updatedDetailIds);
        if (!empty($detailsToDelete)) {
            VoucherDetail::whereIn('id', $detailsToDelete)->delete();
        }
    }

    protected function mapDetailAttributes(array $detail): array
    {
        return [
            'account_id' => $detail['account_id'],
            'amount' => $detail['amount'],
            'rate' => $detail['rate'] ?? null,
            'hours' => $detail['hours'] ?? null,
            'charging_tag' => $detail['charging_tag'] ?? null
        ];
    }

    /**
     * Validate cash voucher amount matches sum of details
     */
    
    protected function validateCashVoucherAmount(array $validated): void
    {
        if (!empty($validated['check'])) {
            // Calculate net amount (C - D)
            $netAmount = collect($validated['check'])->reduce(function ($sum, $item) {
                $amount = (float) $item['amount'];
                return $item['charging_tag'] === 'D' ? $sum - $amount : $sum + $amount;
            }, 0);

            // For cash vouchers, amount must match exactly (C - D)
            if ($validated['type'] === 'cash' && abs($netAmount - $validated['check_amount']) > 0.01) {
                throw ValidationException::withMessages([
                    'check_amount' => [
                        'For cash vouchers, check amount must equal (C items - D items)',
                        'Calculated net amount: ' . number_format($netAmount, 2),
                        'Provided check amount: ' . number_format($validated['check_amount'], 2)
                    ]
                ]);
            }

            // For salary vouchers, update the check amount to match net details (C - D)
            if ($validated['type'] === 'salary') {
                $validated['check_amount'] = $netAmount;
            }
        }
    }

    /**
     * Standard success response format
     */
    protected function successResponse(
        string $message,
        mixed $data = null,
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    /**
     * Standard error response format
     */
    protected function errorResponse(
        string $message,
        mixed $errors = null,
        int $status = 500
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}