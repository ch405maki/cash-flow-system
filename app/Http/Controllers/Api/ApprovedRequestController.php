<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\Request as HttpRequest;
use App\Models\Request;
use App\Models\RequestDetail;

class ApprovedRequestController extends Controller
{

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

            ActivityLogger::make($httpRequest)
                ->on($request)
                ->log("Tagging updated for request #{$request->id}");

            return response()->json(['message' => 'Tagging updated successfully']);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update tagging',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
