<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Release;
use Inertia\Inertia;

class RequestReportController extends Controller
{
    public function releasedItems()
    {
        $releases = Release::with([
            'request.department',
            'details.requestDetail'
        ])
        ->orderBy('release_date', 'desc')
        ->get();

        return Inertia::render('Reports/Requests/ReleasedItems', [
            'releases' => $releases
        ]);
    }
}
