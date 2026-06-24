<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Configuration\StoreDepartmentRequest;
use App\Http\Requests\Configuration\UpdateDepartmentRequest;
use App\Models\Department;
use App\Services\ConfigurationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function __construct(
        protected ConfigurationService $configService
    ) {}

    public function index()
    {
        return Inertia::render('Configuration/Departments', [
            'departments' => Department::all()
        ]);
    }

    public function store(StoreDepartmentRequest $request)
    {
        $department = $this->configService->create(
            Department::class,
            $request->validated(),
            $request,
            'department_name'
        );

        return response()->json($department, 201);
    }

    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $department = $this->configService->update(
            $department,
            $request->validated(),
            $request,
            'department_name'
        );

        return response()->json($department);
    }

    public function destroy(Request $request, Department $department)
    {
        $this->configService->delete($department, $request, 'department_name');

        return response()->json(null, 204);
    }
}