<?php
// app/Http/Controllers/Web/InventoryController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\InventoryApiService;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Inventory/Index');
    }
}