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
            $canvases = Canvas::with(['creator', 'request_to_order', 'files', 'approvals.user',])
                ->whereIn('status', ['pending_approval', 'submitted'])
                ->whereNotNull('request_to_order_id')
                ->latest()
                ->get();
        } elseif ($user->role === 'accounting') {
            // accounting sees canvases waiting for audit that they haven't approved
            $canvases = Canvas::with(['creator', 'request_to_order', 'files', 'approvals.user',])
                ->whereNotIn('status', ['draft', 'pending'])
                ->whereDoesntHave('approvals', function ($query) {
                    $query->where('role', 'accounting');
                })
                ->latest()
                ->get();
        } else {
            // Purchasing officers see their own canvases
            $canvases = Canvas::with(['creator', 'request_to_order', 'files', 'approvals.user',])
                ->where('created_by', $user->id)->where('status', 'draft')
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
        $status = request()->query('status', 'pending_approval');

        $query = Canvas::with([
            'creator', 
            'request_to_order', 
            'files',
            'approvals.user',
            'selected_files.file'
        ])->where('created_by', $user->id);

        // Filter based on status
        switch ($status) {
            case 'pending_approval':
                $query->where('status', 'pending_approval');
                break;
            case 'approved':
                $query->where('status', 'approved');
                break;
            case 'poCreated':
                $query->where('status', 'poCreated');
                break;
            case 'submitted':
                $query->where('status', 'submitted');
                break;
            default:
                $query->whereIn('status', ['pending_approval', 'approved']);
        }

        $canvases = $query->latest()->get();

        return Inertia::render('Canvas/CanvasApproval', [
            'canvases' => $canvases,
            'authUserRole' => $user->role,
            'status' => $status
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
            'files' => 'required|array|min:1',
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
            'status' => 'draft',
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
            'status' => 'required|in:draft,submitted,pending_approval,approved,rejected,poCreated',
            'comments' => 'nullable|string|max:1000',
            'selected_file' => 'nullable|integer|exists:canvas_files,id',
            'file_remarks' => 'nullable|string|max:500'
        ]);

        if ($user->role === 'purchasing') {
            // Handle purchasing approval/comment
            $approval = CanvasApproval::updateOrCreate(
                [
                    'canvas_id' => $canvas->id,
                    'user_id' => $user->id,
                    'role' => 'purchasing'
                ],
                [
                    'comments' => $validated['comments'] ?? null,
                    'approved' => $validated['status'] === 'submitted',
                    'approved_at' => now()
                ]
            );

            $canvas->update([
                'status' => 'submitted',
                'remarks' => $validated['remarks'] ?? null
            ]);
        } 
        elseif ($user->role === 'accounting') {
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
            $canvas->update($validated);
        }

        return back()->with('success', 'Canvas updated successfully');
    }

    public function downloadFile(Canvas $canvas, CanvasFile $file)
    {
        // Verify the file belongs to this canvas
        if ($file->canvas_id !== $canvas->id) {
            abort(403, 'This file does not belong to the specified canvas');
        }

        $filePath = "canvases/{$file->file_path}";
        
        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($filePath, $file->original_filename);
    }

    public function preview($canvasId, $fileId)
    {
        $file = CanvasFile::where('canvas_id', $canvasId)->findOrFail($fileId);

        if (empty($file->file_path)) {
            abort(404, 'File path is missing.');
        }

        $fullPath = Storage::disk('public')->path('canvases/' . $file->file_path);

        if (!file_exists($fullPath)) {
            abort(404, 'File not found.');
        }

        $mime = mime_content_type($fullPath);

        if (!str_contains($mime, 'pdf')) {
            abort(400, 'Preview only available for PDF files.');
        }

        // Use raw file content and return a Response object
        return response()->make(file_get_contents($fullPath), 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $file->original_filename . '"',
            'Content-Transfer-Encoding' => 'binary',
            'Accept-Ranges' => 'bytes',
        ]);
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