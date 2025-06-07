<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Canvas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // Add this import
use Inertia\Inertia;

class CanvasController extends Controller
{
    public function create()
    {
        return Inertia::render('Canvas/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'remarks' => 'nullable|string|max:500',
        ]);

        $file = $request->file('file');
        
        // Generate safe filename while preserving original
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filename = $this->generateUniqueFilename($originalName, $extension);
        
        // Store in public disk
        $filePath = $file->storeAs('canvases', $filename, 'public');

        $canvas = Canvas::create([
            'status' => 'pending',
            'remarks' => $request->remarks,
            'file_path' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('canvas.index')
            ->with('success', 'Canvas uploaded successfully!');
    }

    public function index()
    {
        $canvases = Canvas::with('creator')
            ->where('created_by', auth()->id())
            ->latest()
            ->get();

        return Inertia::render('Canvas/Index', [
            'canvases' => $canvases,
        ]);
    }

    public function download(Canvas $canvas)
    {
        if (!Storage::disk('public')->exists('canvases/'.$canvas->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')
            ->download(
                'canvases/'.$canvas->file_path,
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
}