<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CanvasController extends Controller
{
    public function create(){
        return Inertia::render('Canvas/Create');
    }
}
