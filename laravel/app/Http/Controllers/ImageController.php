<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    /**
     * Upload a new image
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|file|mimes:png,gif,jpg,jpeg|max:102400', // 100MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid image file. Only PNG, GIF, and JPG files are allowed.',
                'details' => $validator->errors()
            ], 422);
        }

        $file = $request->file('image');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('images', $filename, 'public');

        $image = Image::create([
            'filename' => $filename,
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return response()->json([
            'message' => 'Image uploaded successfully',
            'image' => $image
        ], 201);
    }

    /**
     * List the last 50 images
     */
    public function list()
    {
        $images = Image::orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($image) {
                return [
                    'id' => $image->id,
                    'filename' => $image->filename,
                    'url' => asset('storage/' . $image->path),
                    'mime_type' => $image->mime_type,
                    'size' => $image->size,
                    'created_at' => $image->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'images' => $images
        ]);
    }

    /**
     * Delete an image by ID (soft delete)
     */
    public function delete($id)
    {
        $image = Image::find($id);

        if (!$image) {
            return response()->json([
                'error' => 'Image not found'
            ], 404);
        }

        // Soft delete the database record (keeps the file on server)
        $image->delete();

        return response()->json([
            'message' => 'Image deleted successfully'
        ]);
    }

    /**
     * Server-Sent Events endpoint for real-time updates
     */
    public function events(Request $request)
    {
        return response()->stream(function () {
            // Disable PHP output buffering
            if (ob_get_level()) ob_end_clean();
            
            // Set no time limit
            set_time_limit(0);
            
            // Disable default disconnect check
            ignore_user_abort(true);
            
            while (true) {
                // Keep connection alive with heartbeat
                echo ": heartbeat\n\n";
                if (ob_get_level()) ob_flush();
                flush();
                
                // Sleep for a short time to prevent excessive CPU usage
                usleep(500000); // 0.5 seconds
                
                // Check if client disconnected
                if (connection_aborted()) {
                    break;
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }
}
