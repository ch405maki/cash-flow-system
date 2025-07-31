<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class TermsController extends Controller
{
    public function accept(Request $request)
    {
        $request->validate([
            'version' => 'required|string',
        ]);

        $user = Auth::user();
        $user->update([
            'terms_accepted_at' => now(),
            'terms_version' => $request->version,
        ]);

        return response()->json(['message' => 'Terms accepted successfully.']);
    }
}
