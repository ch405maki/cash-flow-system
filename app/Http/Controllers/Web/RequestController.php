<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;

use App\Models\Department;
use App\Models\Request;
use App\Models\Account;

use App\Services\InventoryApiService;

class RequestController extends Controller
{
    public function index(HttpRequest $request)
    {
        return Inertia::render('Request/Index', [
            'pageType' => $request->query('pageType', 'index'),
        ]);
    }

    public function show(Request $request, InventoryApiService $inventoryApi)
    {
        $user = Auth::user();

        // Load relationships
        $request->load([
            'user', 
            'department', 
            'details',
            'approvals.user',
            'releases.details.requestDetail',
            'releases.user'
        ]);

        // Check inventory for each item that has item_id
        $inventoryStatus = [];
        foreach ($request->details as $detail) {
            if ($detail->item_id) {
                $result = $inventoryApi->checkProductQuantity($detail->item_id);
                $inventoryStatus[$detail->id] = [
                    'has_item_id' => true,
                    'exists_in_inventory' => $result['exists'],
                    'available_quantity' => $result['quantity'],
                    'has_sufficient' => $result['exists'] && $result['quantity'] > 0
                ];
            } else {
                $inventoryStatus[$detail->id] = [
                    'has_item_id' => false,
                    'exists_in_inventory' => false,
                    'available_quantity' => 0,
                    'has_sufficient' => false
                ];
            }
        }

        return Inertia::render('Request/Show', [
            'request' => $request,
            'accounts' => Account::all(['id', 'account_title']),
            'inventoryStatus' => $inventoryStatus,
            'user' => [
                'id' => $user->id,
                'role' => $user->role,
                'access' => $user->access_id,
                'department_id' => $user->department_id,
            ],
        ]);
    }

    public function create(HttpRequest $request)
    {
        $reorderRequestId = $request->query('reorder_from');
        $reorderRequest = null;

        // If reordering from an existing request, load it properly
        if ($reorderRequestId) {
            $reorderRequest = Request::with(['department', 'user', 'details'])
                ->findOrFail($reorderRequestId);
        }

        $requests = Request::with(['department', 'user', 'details'])->get();

        return Inertia::render('Request/Create', [
            'requests' => $requests,
            'departments' => Department::all(),
            'authUser' => [
                'id' => Auth::id(),
                'department_id' => Auth::user()->department_id,
            ],
            'reorderRequest' => $reorderRequest,
        ]);
    }

    public function edit(Request $request)
    {
        return Inertia::render('Request/Edit', [
            'request' => $request->load(['details','user', 'department']),
            'departments' => Department::all(),
        ]);
    }

    public function release(Request $request)
    {
        return Inertia::render('Request/Release/Index', [
            'requestId' => $request->id,
        ]);
    }

}
