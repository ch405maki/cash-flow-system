<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Canvas;
use App\Models\CanvasFile;
use App\Models\CanvasApproval;
use App\Models\CanvasSelectedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CanvasController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'executive_director') {
            // Executive Director sees canvases pending final approval
            $canvases = Canvas::with(['creator', 'request_to_order', 'files'])
                ->where('status', 'pending_approval')
                ->latest()
                ->get();
        } elseif ($user->role === 'accounting') {
            // accounting sees canvases waiting for audit
            $canvases = Canvas::with(['creator', 'request_to_order', 'files'])
                ->where('status', 'submitted')
                ->latest()
                ->get();
        } else {
            // Purchasing officers see their own canvases
            $canvases = Canvas::with(['creator', 'request_to_order', 'files'])
                ->where('created_by', $user->id)
                ->latest()
                ->get();
        }

        return Inertia::render('Canvas/Index', [
            'canvases' => $canvases,
            'authUserRole' => $user->role,
        ]);
    }

    public function approval()
    {
        $user = Auth::user();

        $canvases = Canvas::with(['creator', 'request_to_order', 'files', 'approvals']) 
            ->whereIn('status', ['approved', 'pending_approval'])
            ->where('created_by', $user->id)
            ->latest()
            ->get();

        return Inertia::render('Canvas/CanvasApproval', [
            'canvases' => $canvases,
            'authUserRole' => $user->role,
        ]);
    }
    
    public function create()
    {
        return Inertia::render('Canvas/Create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'files' => 'required|array|min:3',
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
            'note' => 'nullable|string|max:500',
            'request_to_order_id' => 'nullable|exists:request_to_orders,id',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        $canvas = Canvas::create([
            'title' => $validated['title'],
            'status' => 'submitted',
            'note' => $validated['note'] ?? null,
            'created_by' => Auth::id(),
            'request_to_order_id' => $validated['request_to_order_id'] ?? null,
        ]);

        foreach ($request->file('files') as $file) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $filename = $this->generateUniqueFilename($originalName, $extension);
            
            $filePath = $file->storeAs('canvases', $filename, 'public');

            CanvasFile::create([
                'canvas_id' => $canvas->id,
                'file_path' => $filename,
                'original_filename' => $file->getClientOriginalName(),
            ]);
        }

        return redirect()->route('canvas.index')
            ->with('success', 'Canvas submitted with files!');
    }

public function update(Request $request, Canvas $canvas)
{
    $user = Auth::user();
    
    $validated = $request->validate([
        'remarks' => 'nullable|string|max:500',
        'status' => 'required|in:submitted,pending_approval,approved,rejected',
        'comments' => 'nullable|string|max:1000',
        'selected_file' => 'nullable|integer|exists:canvas_files,id',
        'file_remarks' => 'nullable|string|max:500'
    ]);

    if ($user->role === 'accounting') {
        // Handle accounting approval/comment
        $approval = CanvasApproval::updateOrCreate(
            [
                'canvas_id' => $canvas->id,
                'user_id' => $user->id,
                'role' => 'accounting'
            ],
            [
                'comments' => $validated['comments'] ?? null,
                'approved' => $validated['status'] === 'pending_approval',
                'approved_at' => now()
            ]
        );

        $canvas->update([
            'status' => 'pending_approval',
            'remarks' => $validated['remarks'] ?? null
        ]);
    } 
    elseif ($user->role === 'executive_director') {
        DB::transaction(function () use ($canvas, $validated, $user) {
            $approval = CanvasApproval::create([
                'canvas_id' => $canvas->id,
                'user_id' => $user->id,
                'role' => 'executive_director',
                'comments' => $validated['comments'] ?? null,
                'approved' => $validated['status'] === 'approved',
                'approved_at' => now()
            ]);

            // Save the selected file if provided
            if (isset($validated['selected_file'])) {
                CanvasSelectedFile::create([
                    'canvas_id' => $canvas->id,
                    'canvas_file_id' => $validated['selected_file'],
                    'approval_id' => $approval->id,
                    'remarks' => $validated['file_remarks'] ?? null
                ]);
            }

            $canvas->update([
                'status' => 'approved',
                'remarks' => $validated['remarks'] ?? null
            ]);
        });
    } 
    else {
        // Regular updates
        $canvas->update($validated);
    }

    return back()->with('success', 'Canvas updated successfully');
}

    public function download(Canvas $canvas, $fileId = null)
    {
        if ($fileId) {
            // Download specific file
            $file = CanvasFile::where('canvas_id', $canvas->id)
                ->findOrFail($fileId);

            if (!Storage::disk('public')->exists('canvases/'.$file->file_path)) {
                abort(404, 'File not found');
            }

            return response()->download(
                Storage::disk('public')->path('canvases/'.$file->file_path),
                $file->original_filename
            );
        }

        // Fallback to old single-file behavior (for backward compatibility)
        if (!Storage::disk('public')->exists('canvases/'.$canvas->file_path)) {
            abort(404, 'File not found');
        }

        return response()->download(
            Storage::disk('public')->path('canvases/'.$canvas->file_path),
            $canvas->original_filename
        );
    }

    protected function generateUniqueFilename($name, $extension)
    {
        $slug = Str::slug($name);
        $filename = "{$slug}.{$extension}";
        
        $counter = 1;
        while (Storage::disk('public')->exists("canvases/{$filename}")) {
            $filename = "{$slug}-{$counter}.{$extension}";
            $counter++;
        }
        
        return $filename;
    }


    public function show(Canvas $canvas)
    {
        $canvas->load(['files', 'approvals.user', 'selected_files.approval']);

        return Inertia::render('Canvas/Show', [
            'canvas' => $canvas,
            'authUserRole' => Auth::user()->role,
        ]);
    }
};