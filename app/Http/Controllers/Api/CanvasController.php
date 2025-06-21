<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Canvas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // Add this import
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CanvasController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'executive_director') {
            // Executive Director sees every canvas waiting for EOD sign‑off
            $canvases = Canvas::with('creator')
                ->where('status', 'forEOD')
                ->latest()
                ->get();
        } else {
            // Everyone else sees only the canvases they created
            $canvases = Canvas::with('creator')
                ->whereIn('status', ['forEOD', 'pending'])
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

        $canvases = Canvas::with('creator')
            ->whereIn('status', ['approved', 'poCreated','forEOD'])
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
        $validated = $request->validate([
            'file' => 'required|file|max:10240',
            'note' => 'nullable|string|max:500',
            'remarks' => 'nullable|string|max:500',
            'request_to_order_id' => 'nullable|exists:request_to_orders,id',
        ]);

        $file = $request->file('file');
        
        // Generate safe filename while preserving original
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filename = $this->generateUniqueFilename($originalName, $extension);
        
        // Store in public disk (we use $filename but keep $filePath for potential logging)
        $filePath = $file->storeAs('canvases', $filename, 'public');

        $canvasData = [
            'status' => 'pending',
            'note' => $validated['note'] ?? null, // Safe access with null coalescing
            'remarks' => $validated['remarks'] ?? null, // Safe access with null coalescing
            'file_path' => $filename, // Using just the filename is fine if that's your schema
            'original_filename' => $file->getClientOriginalName(),
            'created_by' => Auth::id(),
        ];

        // Only add request_to_order_id if provided
        if (!empty($validated['request_to_order_id'])) {
            $canvasData['request_to_order_id'] = $validated['request_to_order_id'];
        }

        // We keep the $canvas variable as it might be useful for logging/events
        $canvas = Canvas::create($canvasData);

        return redirect()->route('canvas.index')
            ->with('success', 'Canvas uploaded successfully!');
    }

    public function update(Request $request, Canvas $canvas)
    {
        $validated = $request->validate([
            'remarks' => 'nullable|string',
            'status' => 'sometimes|in:pending,approved,rejected,forEOD,poCreated'
        ]);

        $canvas->update($validated);

        return back();
    }

    public function download(Canvas $canvas)
    {
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
        return Inertia::render('Canvas/Show', [
            'canvas' => $canvas,
        ]);
    }
}