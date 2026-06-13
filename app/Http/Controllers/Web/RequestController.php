<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request as HttpRequest;

use App\Models\Request;

class RequestController extends Controller
{
    public function index(HttpRequest $request)
    {
        return Inertia::render('Request/Index', [
            'pageType' => $request->query('pageType', 'index'),
        ]);
    }

    public function show(Request $request)
    {
        return Inertia::render('Request/Show', [
            'requestId' => $request->id,
        ]);
    }

    public function create()
    {
        return Inertia::render('Request/Create');
    }

    public function edit(Request $request)
    {
        return Inertia::render('Request/Edit', [
            'requestId' => $request->id,
        ]);
    }

    public function release(Request $request)
    {
        return Inertia::render('Request/Release/Index', [
            'requestId' => $request->id,
        ]);
    }

}
