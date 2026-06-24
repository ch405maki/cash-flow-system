<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Configuration\StoreSignatoryRequest;
use App\Http\Requests\Configuration\UpdateSignatoryRequest;
use App\Models\Signatory;
use App\Services\ConfigurationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SignatoryController extends Controller
{
    public function __construct(
        protected ConfigurationService $configService
    ) {}

    public function index()
    {
        return Inertia::render('Configuration/Signatory', [
            'signatories' => Signatory::all()
        ]);
    }

    public function store(StoreSignatoryRequest $request)
    {
        $signatory = $this->configService->create(
            Signatory::class,
            $request->validated(),
            $request,
            'full_name'
        );

        return response()->json($signatory, 201);
    }

    public function update(UpdateSignatoryRequest $request, Signatory $signatory)
    {
        $signatory = $this->configService->update(
            $signatory,
            $request->validated(),
            $request,
            'full_name'
        );

        return response()->json($signatory);
    }

    public function destroy(Request $request, Signatory $signatory)
    {
        $this->configService->delete($signatory, $request, 'full_name');

        return response()->json(null, 204);
    }
}