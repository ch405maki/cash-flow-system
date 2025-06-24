<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\PurchaseOrder;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\Account;
use App\Models\Signatory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

use Illuminate\Database\QueryException;
use Inertia\Inertia;

class VoucherController extends Controller
{

    // Validation rules shared between store and update
    protected array $voucherValidationRules = [
        'issue_date' => 'required|date',
        'payment_date' => 'required|date',
        'check_date' => 'required|date',
        'delivery_date' => 'required|date',
        'voucher_date' => 'required|date',
        'purpose' => 'required|string|max:500',
        'payee' => 'required|string|max:255',
        'check_no' => 'nullable|string|max:500',
        'check_payable_to' => 'required|string|max:500',
        'check_amount' => 'required|numeric|min:0',
        'status' => 'required|in:forEOD,forCheck,rejected,draft',
        'type' => 'required|in:cash,salary',
        'user_id' => 'required|exists:users,id',
        'check' => 'nullable|array',
        'check.*.amount' => 'nullable|numeric|min:0',
        'check.*.rate' => 'nullable|numeric|min:0',
        'check.*.hours' => 'nullable|numeric|min:0|max:24',
        'check.*.charging_tag' => 'nullable|in:C,D',
        'check.*.account_id' => 'required_with:check.*|exists:accounts,id',
    ];
    
    public function index()
    {
        $user = Auth::user();
        return Inertia::render('Vouchers/Index', [
            'vouchers' => Voucher::with(['user', 'details'])
                                ->whereIn('status', ['draft', 'rejected'])
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
                'payment_date' => 'nullable|date',
                'check_date' => 'required|date',
                'delivery_date' => 'nullable|date',
                'voucher_date' => 'required|date',
                'purpose' => 'required|string|max:500',
                'payee' => 'required|string|max:255',
                'check_no' => 'nullable|string|max:500',
                'check_payable_to' => 'required|string|max:500',
                'check_amount' => 'required|numeric|min:0',
                'status' => 'required|in:forEOD,forCheck,rejected,draft',
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
                'payment_date' => $validated['payment_date'] ?? null,
                'check_date' => $validated['check_date'],
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

            return response()->json([
                'message' => 'Voucher created successfully',
                'data' => $voucher->load(['user', 'details', 'purchaseOrder']), // Load PO relation
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
        $prefix = 'V-' . now()->format('Ym') . '-';  // Format: V-YYYYMM-
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
            $validated = $this->validateRequest($request);

            // Lock the voucher number to the existing value
            $validated['voucher_no'] = $voucher->voucher_no;

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
        $voucher->load(['user', 'details.account']);

        return Inertia::render('Vouchers/View', [ 
            'voucher' => $voucher,
            'accounts' => Account::all(),
            'authUser' => [ 
                'id' => Auth::user()->id,
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
            'action' => 'required|in:forEod,reject'
        ]);

        $user = Auth::user();

        // Password verification
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        $voucher = Voucher::findOrFail($id);

        // Check current status
        if ($voucher->status === 'forEOD') {
            return back()->withErrors(['status' => 'Voucher is already sent']);
        }

        if ($voucher->status === 'rejected') {
            return back()->withErrors(['status' => 'Voucher is already rejected']);
        }

        // Determine the action
        $action = $request->input('action');
        $newStatus = $action === 'forEod' ? 'forEOD' : 'rejected';
        $message = $action === 'ForEod'
            ? 'Voucher sent to Executive Director'
            : 'Voucher rejected successfully';

        // Update the voucher
        $voucher->update(['status' => $newStatus]);

        return back()->with([
            'success' => $message,
            'voucher' => $voucher->fresh()
        ]);
    }

    public function forEod($id, Request $request)
    {
        $request->validate([
            'password' => 'required',
            'action' => 'required|in:approve,reject'
        ]);

        $user = Auth::user();

        // Password verification
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        $voucher = Voucher::findOrFail($id);

        // Check current status
        if ($voucher->status === 'forCheck') {
            return back()->withErrors(['status' => 'Voucher is already approved for check releasing']);
        }

        if ($voucher->status === 'rejected') {
            return back()->withErrors(['status' => 'Voucher is already rejected']);
        }

        // Determine the action
        $action = $request->input('action');
        $newStatus = $action === 'approve' ? 'forCheck' : 'rejected';
        $message = $action === 'approve'
            ? 'Voucher approved successfully'
            : 'Voucher rejected successfully';

        // Update the voucher
        $voucher->update(['status' => $newStatus]);

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
            $sumAmount = collect($validated['check'])->sum('amount');

            // For cash vouchers, amount must match exactly
            if ($validated['type'] === 'cash' && abs($sumAmount - $validated['check_amount']) > 0.01) {
                throw ValidationException::withMessages([
                    'check_amount' => 'For cash vouchers, check amount must equal the sum of all item amounts'
                ]);
            }

            // For salary vouchers, update the check amount to match details
            if ($validated['type'] === 'salary') {
                $validated['check_amount'] = $sumAmount;
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