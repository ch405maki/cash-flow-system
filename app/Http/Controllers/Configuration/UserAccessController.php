<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Configuration\StoreAccessRequest;
use App\Http\Requests\Configuration\UpdateAccessRequest;
use App\Models\Access;
use App\Services\ConfigurationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserAccessController extends Controller
{
    public function __construct(
        protected ConfigurationService $configService
    ) {}

    public function index()
    {
        return Inertia::render('Configuration/Access', [
            'accesses' => Access::all()
        ]);
    }

    public function store(StoreAccessRequest $request)
    {
        $access = $this->configService->create(
            Access::class,
            $request->validated(),
            $request,
            'program_name'
        );

        return response()->json($access, 201);
    }

    public function update(UpdateAccessRequest $request, Access $access)
    {
        $access = $this->configService->update(
            $access,
            $request->validated(),
            $request,
            'program_name'
        );

        return response()->json($access);
    }

    public function destroy(Request $request, Access $access)
    {
        $this->configService->delete($access, $request, 'program_name');

        return response()->json(null, 204);
    }
}