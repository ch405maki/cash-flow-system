<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;

use App\Http\Controllers\Controller;
use App\Notifications\NewRequestNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use App\Models\Department;
use App\Models\RequestDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Request;
use App\Models\Account;
use App\Models\Release;
use App\Models\User;
use App\Models\RequestApproval;
use App\Models\ReleaseDetail;

class RequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (in_array($user->role, ['admin', 'executive_director', 'property_custodian'])) {
            $requests = Request::with(['department', 'user', 'details'])
                ->whereIn('status', ['propertyCustodian'])
                ->get();
        } else {
            $requests = Request::with(['department', 'user', 'details'])
                ->where('status', 'pending')
                ->where('department_id', $user->department_id)
                ->get();
        }

        return Inertia::render('Request/Index', [
            'requests' => $requests,
            'departments' => Department::all(),
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
                'department_id' => $user->department_id,
            ],
        ]);
    }

    public function show(Request $request)
    {
        $user = Auth::user();

        $request->load([
            'user', 
            'department', 
            'details',
            'approvals.user',
            'releases.details.requestDetail',
            'releases.user'
        ]);

        return Inertia::render('Request/Show', [
            'request' => $request,
            'accounts' => Account::all(['id', 'account_title']),
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

    public function released()
    {
        $user = Auth::user();

        $requests = Request::with(['department', 'user', 'details'])
            ->where('status', 'released')
            ->where('department_id', $user->department_id)
            ->get();

        return Inertia::render('Request/Released', [
            'requests' => $requests,
            'departments' => Department::all(),
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
                'department_id' => $user->department_id,
            ],
        ]);
    }

    public function toReceive()
    {
        $user = Auth::user();

        $requests = Request::with(['department', 'user', 'details'])
            ->whereIn('status', ['propertyCustodian', 'to_order', 'partially_released'])
            ->where('department_id', $user->department_id)
            ->get();

        return Inertia::render('Request/ToReceive', [
            'requests' => $requests,
            'departments' => Department::all(),
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
                'department_id' => $user->department_id,
            ],
        ]);
    }

    public function rejected()
    {
        $user = Auth::user();

        $requests = Request::with(['department', 'user', 'details'])
            ->where('status', 'rejected')
            ->where('department_id', $user->department_id)
            ->get();

        return Inertia::render('Request/Rejected', [
            'requests' => $requests,
            'departments' => Department::all(),
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
                'department_id' => $user->department_id,
            ],
        ]);
    }

    public function release(Request $request)
    {
        return Inertia::render('Request/Release/Index', [
            'request' => $request->load([
                'details' => function ($query) {
                    $query->where('quantity', '!=', 0);
                },
                'user', 
                'department'
            ]),
            'departments' => Department::all(),
            'current_user' => [
                'id' => auth()->id(),
            ]
        ]);
    }

    public function updateStatus(HttpRequest $httpRequest, Request $request)
    {
        $validated = $httpRequest->validate([
            'status' => 'required|in:approved,rejected,propertyCustodian,to_order,released',
            'password' => 'required_if:status,approved,propertyCustodian,to_order,released',
        ]);

        $passwordRequiredStatuses = ['approved', 'propertyCustodian', 'to_order', 'released'];
        $authUser = auth()->user();

        // Check password if required
        if (in_array($validated['status'], $passwordRequiredStatuses)) {
            if (!Hash::check($validated['password'], $authUser->password)) {
                return back()->withErrors([
                    'password' => 'Invalid password',
                ]);
            }
        }

        // Store old status for logging
        $oldStatus = $request->status;

        // Base update data
        $updateData = ['status' => $validated['status']];

        // If approver is same department as request, assign them as request owner
        if ($authUser->department_id === $request->department_id) {
            $updateData['user_id'] = $authUser->id;
        }

        $validated['director_approved_at'] = now();

        $request->update($updateData);

        $description = "Request #{$request->request_no} status changed from {$oldStatus} to {$validated['status']} by {$authUser->username}";

        RequestApproval::create([
            'request_id' => $request->id,
            'user_id' => $authUser->id,
            'status' => $validated['status'],
            'approved_at' => now(),
            'remarks' => $description,
        ]);

        // ===== NOTIFICATION FOR PROPERTY CUSTODIAN ROLE =====
        // Only notify if status is propertyCustodian
        if ($validated['status'] === 'propertyCustodian') {
            // Get all users with role 'property_custodian'
            $custodians = User::where('role', 'property_custodian')->get();
            
            // Get the request creator
            $creator = User::find($request->user_id);
            
            foreach ($custodians as $custodian) {
                DB::table('notifications')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'RequestForCustodian',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $custodian->id,
                    'data' => json_encode([
                        'request_id' => $request->id,
                        'title' => 'Request Ready for Custodian',
                        'request_no' => $request->request_no,
                        'department' => $request->department->name ?? 'N/A',
                        'purpose' => $request->purpose,
                        'created_by' => $creator?->name ?? 'Unknown',
                        'previous_status' => $oldStatus,
                        'status' => $validated['status'],
                        'message' => "Request #{$request->request_no} from {$request->department->name} is ready for processing.",
                        'link' => route('request.show', $request->id),
                        'updated_by' => $authUser->name,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        // ===== NOTIFY CREATOR WHEN STATUS IS TO_ORDER =====
        if ($validated['status'] === 'to_order') {
            $creator = User::find($request->user_id);
            
            if ($creator) {
                DB::table('notifications')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'RequestReadyToOrder',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $creator->id,
                    'data' => json_encode([
                        'request_id' => $request->id,
                        'title' => 'Request Ready to Order',
                        'request_no' => $request->request_no,
                        'status' => $validated['status'],
                        'message' => "Your request #{$request->request_no} is now ready to order.",
                        'link' => route('request.show', $request->id),
                        'updated_by' => $authUser->name,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        // ===== END NOTIFICATIONS =====

        // Log activity
        activity()
            ->performedOn($request)
            ->causedBy($authUser)
            ->useLog('Status Updated')
            ->withProperties([
                'action' => 'status_update',
                'event' => 'Status Updated',
                'ip_address' => $httpRequest->ip(),
                'old_status' => $oldStatus,
                'new_status' => $validated['status'],
                'changed_by' => $authUser->username,
                'changed_by_role' => $authUser->role,
                'request_no' => $request->request_no,
            ])
            ->log($description);

        return back()->with('success', 'Request status updated successfully');
    }
}
