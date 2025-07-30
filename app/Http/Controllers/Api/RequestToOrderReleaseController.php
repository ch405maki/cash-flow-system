<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RequestToOrder;
use App\Models\RequestToOrderDetail;
use App\Models\RequestToOrderRelease;
use Illuminate\Http\Request;
use App\Models\Request as RequestData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\RequestToOrderApproval;
use Inertia\Inertia;

class RequestToOrderReleaseController extends Controller
{
    public function index() 
    {
        $user = Auth::user();

        $requests = RequestToOrder::with('details')
            ->whereIn('status', ['completed'])
            ->get();

        $forOrders = RequestData::with(['department', 'user', 'details'])
            ->whereIn('status', [ 'to_order'])
            ->get();

        return Inertia::render('Request/Order/Release/Index', [
            'requests' => $requests,
            'forOrders' => $forOrders,
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
                'department_id' => $user->department_id,
            ],
        ]);
    }

    public function create(RequestToOrder $order)
    {
        // Reload the order with fresh data
        $order->load(['details' => function($query) {
            $query->whereRaw('quantity > COALESCE((SELECT SUM(quantity_released) FROM request_to_order_releases WHERE request_to_order_detail_id = request_to_order_details.id), 0)')
                ->withSum('releases', 'quantity_released');
        }, 'details.releases']);

        // Calculate remaining quantities
        $order->details->each(function ($detail) {
            $detail->remaining_quantity = $detail->quantity - ($detail->releases_sum_quantity_released ?? 0);
        });

        return Inertia::render('Request/Order/Release/Create', [
            'order' => $order
        ]);
    }

    public function store(Request $request, RequestToOrder $order)
    {
        $validated = $request->validate([
            'release_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.detail_id' => 'required|exists:request_to_order_details,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
        ]);

        \Log::info('Starting release process', ['order_id' => $order->id, 'items' => $validated['items']]);

        DB::transaction(function () use ($validated, $order) {
            foreach ($validated['items'] as $item) {
                $detail = RequestToOrderDetail::withSum('releases', 'quantity_released')
                    ->findOrFail($item['detail_id']);

                \Log::debug('Release detail check', [
                    'detail_id' => $detail->id,
                    'ordered_quantity' => $detail->quantity,
                    'already_released' => $detail->releases_sum_quantity_released,
                    'attempting_to_release' => $item['quantity'],
                    'calculated_remaining' => $detail->quantity - $detail->releases_sum_quantity_released
                ]);

                $alreadyReleased = $detail->releases_sum_quantity_released ?? 0;
                $attemptingToRelease = $item['quantity'];
                $totalAfterRelease = $alreadyReleased + $attemptingToRelease;

                if ($totalAfterRelease > $detail->quantity) {
                    $remaining = $detail->quantity - $alreadyReleased;
                    \Log::error('Release quantity exceeded', [
                        'detail_id' => $detail->id,
                        'ordered' => $detail->quantity,
                        'released' => $alreadyReleased,
                        'attempting' => $attemptingToRelease,
                        'remaining' => $remaining
                    ]);
                    throw new \Exception(
                        "Cannot release {$attemptingToRelease}. " .
                        "Already released {$alreadyReleased} of {$detail->quantity}. " .
                        "Only {$remaining} remaining for item: {$detail->item_description}"
                    );
                }

                $release = RequestToOrderRelease::create([
                    'request_to_order_id' => $order->id,
                    'request_to_order_detail_id' => $item['detail_id'],
                    'quantity_released' => $item['quantity'],
                    'release_date' => $validated['release_date'],
                    'notes' => $item['notes'] ?? null,
                    'released_by' => Auth::id(),
                ]);

                \Log::info('Item released', [
                    'release_id' => $release->id,
                    'quantity' => $item['quantity']
                ]);

                // Log activity
                activity()
                    ->performedOn($release)
                    ->causedBy(Auth::user())
                    ->useLog('RequestToOrderRelease')
                    ->withProperties([
                        'order_no' => $order->order_no,
                        'detail_id' => $detail->id,
                        'released_quantity' => $item['quantity'],
                    ])
                    ->log("Released {$item['quantity']} units for item: {$detail->item_description}");
            }

            // Create approval log (optional: check if already approved to avoid duplicate)
            RequestToOrderApproval::create([
                'request_to_order_id' => $order->id,
                'user_id' => Auth::id(),
                'status' => 'approved',
                'remarks' => "Released {$item['quantity']} units for item: {$detail->item_description}",
                'approved_at' => now(),
            ]);

            // Reload fresh details for checking
            $order->load('details.releases');

            $allReleased = true;
            foreach ($order->details as $detail) {
                $releasedQty = $detail->releases->sum('quantity_released');
                if ($releasedQty < $detail->quantity) {
                    $allReleased = false;
                    break;
                }
            }

            if ($allReleased) {
                $order->update(['status' => 'completed']);

                activity()
                    ->performedOn($order)
                    ->causedBy(Auth::user())
                    ->useLog('RequestToOrder')
                    ->withProperties(['order_no' => $order->order_no])
                    ->log('RequestToOrder marked as completed after full release');
            }
        });

        return redirect()->route('request-to-order.show', $order->id)
            ->with('success', 'Items released and approval recorded successfully.');
    }


    protected function checkOrderCompletion(RequestToOrder $order)
    {
        $allReleased = true;
        
        foreach ($order->details as $detail) {
            if ($detail->remaining_quantity > 0) {
                $allReleased = false;
                break;
            }
        }

        if ($allReleased) {
            $order->update(['status' => 'completed']);
        }
    }
}