<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Inertia\Inertia;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::all();
        return Inertia::render('Vouchers/Index', ['vouchers' => $vouchers]);
    }
    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'issue_date' => 'required|date',
                'payment_date' => 'required|date',
                'check_date' => 'required|date',
                'delivery_date' => 'required|date',
                'voucher_date' => 'required|date',
                'purpose' => 'required|string|max:500',
                'payee' => 'required|string|max:255',
                'check_no' => 'required|string|max:500',
                'check_payable_to' => 'required|string|max:500',
                'check_amount' => 'required|numeric|min:0',
                'status' => 'required|in:pending,paid,rejected',
                'type' => 'required|in:payment',
                'user_id' => 'required|exists:users,id',
                'check' => 'required|array|min:1',
                'check.*.amount' => 'required|numeric|min:0',
                'check.*.rate' => 'nullable|numeric|min:0',
                'check.*.hours' => 'nullable|numeric|min:0|max:24',
                'check.*.charging_tag' => 'required|string|max:20',
                'check.*.account_id' => 'required|exists:accounts,id',
            ]);

            // Validate check amount equals sum of item amounts
            $sumAmount = collect($validated['check'])->sum('amount');
            if (abs($sumAmount - $validated['check_amount']) > 0.01) {
                throw ValidationException::withMessages([
                    'check_amount' => 'Check amount must equal the sum of all item amounts'
                ]);
            }

            // Auto-generate voucher number
            $validated['voucher_no'] = $this->generateVoucherNumber();

            // Create the voucher
            $voucher = Voucher::create(collect($validated)->except('check')->toArray());

            // Create voucher details
            foreach ($validated['check'] as $check) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $check['account_id'],
                    'amount' => $check['amount'],
                    'rate' => $check['rate'] ?? null,
                    'hours' => $check['hours'] ?? null,
                    'charging_tag' => $check['charging_tag']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Voucher created successfully',
                'data' => $voucher->load(['user', 'details']),
                'meta' => [
                    'created_at' => now()->toDateTimeString(),
                    'items_count' => count($validated['check'])
                ]
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    protected function generateVoucherNumber(): string
    {
        $prefix = 'V-' . now()->format('Y') . '-';
        $lastVoucher = Voucher::where('voucher_no', 'like', $prefix . '%')->latest()->first();
        
        $sequence = $lastVoucher 
            ? (int) str_replace($prefix, '', $lastVoucher->voucher_no) + 1
            : 1;
        
        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}