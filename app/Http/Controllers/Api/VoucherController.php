<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
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
        'status' => 'required|in:pending,approved,rejected',
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
            'vouchers' => Voucher::with(['user', 'details'])->get(),
            'accounts' => Account::all(),
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
                'access' => $user->access_id,
            ]
        ]);
    }

    public function create()
    {
        return Inertia::render('Vouchers/Create', [
            'accounts' => Account::orderBy('account_title')->get()
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $validated = $this->validateRequest($request);
            
            // Explicitly remove voucher_no if somehow provided
            unset($validated['voucher_no']);
            
            $this->handleCashVoucherDetails($validated);
            $voucher = $this->createVoucher($validated);
            
            return $this->successResponse(
                'Voucher created successfully',
                $voucher->load(['user', 'details']),
                201,
                ['items_count' => $validated['type'] === 'cash' ? count($validated['check'] ?? []) : 0]
            );
        });
    }

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
        
        return inertia('Vouchers/View', [
            'voucher' => $voucher,
            'accounts' => Account::all(),
        ]);
    }

    public function approve(Voucher $voucher)
    {
        // Add any authorization checks here (e.g., only certain roles can approve)
        // if (!auth()->user()->can('approve', $voucher)) {
        //     abort(403);
        // }

        $voucher->update([
            'status' => 'approved',
        ]);

        return redirect()->back()->with('success', 'Voucher approved successfully');
    }


    protected function generateVoucherNumber(): string
    {
        $prefix = 'V-' . now()->format('Y') . '-';
        $lastVoucher = Voucher::where('voucher_no', 'like', $prefix . '%')
                            ->orderBy('voucher_no', 'desc')
                            ->first();
        
        $sequence = $lastVoucher 
            ? (int) str_replace($prefix, '', $lastVoucher->voucher_no) + 1
            : 1;
        
        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Validate the request data
     */
    protected function validateRequest(Request $request): array
    {
        $rules = $this->voucherValidationRules;
        
        // If updating, ignore unique rule for current voucher
        if ($request->isMethod('patch') || $request->isMethod('put')) {
            $voucherId = $request->route('voucher')?->id;
            if ($voucherId) {
                $rules['voucher_no'] = 'required|string|unique:vouchers,voucher_no,'.$voucherId;
            }
        }
        
        return $request->validate($rules);
    }

    /**
     * Handle cash voucher details (amount distribution)
     */
    protected function handleCashVoucherDetails(array &$validated): void
    {
        if ($validated['type'] !== 'cash' || empty($validated['check'])) {
            return;
        }

        $checkCollection = collect($validated['check']);
        
        // Distribute amount evenly if any amount is null
        if ($checkCollection->contains(fn ($item) => is_null($item['amount']))) {
            $evenAmount = round($validated['check_amount'] / $checkCollection->count(), 2);
            $validated['check'] = $checkCollection->map(fn ($item) => [
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

    /**
     * Create a new voucher with details
     */
    protected function createVoucher(array $validated): Voucher
    {
        $voucherData = collect($validated)->except('check')->toArray();
        $voucherData['voucher_no'] = $this->generateVoucherNumber();

        $voucher = Voucher::create($voucherData);

        // Modified condition to handle both cash and salary vouchers
        if (!empty($validated['check'])) {
            $this->createVoucherDetails($voucher, $validated['check']);
        }

        return $voucher;
    }

    /**
     * Update an existing voucher
     */
    protected function updateType(Voucher $voucher, array $validated): void
    {
        // For salary vouchers, calculate check_amount from details
        if ($validated['type'] === 'salary' && !empty($validated['check'])) {
            $validated['check_amount'] = collect($validated['check'])->sum('amount');
        }
        
        $voucher->update(collect($validated)->except('check')->toArray());
    }

    /**
     * Sync voucher details (create/update/delete as needed)
     */
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

    /**
     * Create multiple voucher details
     */
    protected function createVoucherDetails(Voucher $voucher, array $details): void
    {
        $voucher->details()->createMany(
            array_map(fn ($detail) => $this->mapDetailAttributes($detail), $details)
        );
    }

    /**
     * Map detail attributes for consistent structure
     */
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