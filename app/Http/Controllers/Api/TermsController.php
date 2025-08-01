<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;

class TermsController extends Controller
{
    public function accept(Request $request)
{
    $request->validate([
        'accepted' => 'required|boolean',
        'user_id' => 'required|exists:users,id'
    ]);

    // Get the user with their username
    $user = User::find($request->user_id);

    if ($request->accepted) {
        // Update the terms acceptance
        $user->update([
            'terms_accepted_at' => now()
        ]);

        // Log the activity
        activity()
            ->performedOn($user)
            ->causedBy($user)
            ->useLog('Terms and Conditions')
            ->withProperties([
                'action' => 'Accepted',
                'event' => 'Terms and Conditions',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'username' => $user->username,
            ])
            ->log("User {$user->username} accepted Terms and Conditions");
        
        return response()->json(['success' => true]);
    }

    return response()->json(['success' => false], 400);
}
}