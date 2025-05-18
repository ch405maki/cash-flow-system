<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Request;
use App\Models\RequestDetail;
use App\Models\Department;
use App\Models\PurchaseOrderDetail;
use App\Models\Account;

class ApprovedRequestController extends Controller
{
    public function index()
    {
        $requests = Request::with(['department', 'user', 'details'])
            ->where('status', 'approved')
            ->get();

        return Inertia::render('Request/Approved/Index', [
            'requests' => $requests,
            'departments' => Department::all(),
            'authUser' => [
                'id' => Auth::id(),
                'department_id' => Auth::user()->department_id,
            ],
        ]);
    }

    public function show(request $request)
    {
        return Inertia::render('Request/Approved/Show', [
            'request' => $request->load(['user', 'department', 'details']),
            'accounts' => Account::all(['id', 'account_title']),
        ]);
    }

     public function updateTagging(Request $request, HttpRequest $httpRequest)
    {
        try {
            $validated = $httpRequest->validate([
                'tagging' => 'required|array',
                'tagging.*' => 'nullable|in:no_canvas,with_canvas'
            ]);

            foreach ($validated['tagging'] as $detailId => $taggingValue) {
                RequestDetail::where('id', $detailId)
                    ->where('request_id', $request->id)
                    ->update(['tagging' => $taggingValue]);
            }

            return response()->json(['message' => 'Tagging updated successfully']);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update tagging',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
