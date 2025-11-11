<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function serve($filePath)
    {
        // Security: Check if the file exists on the 'public' disk.
        // The 'public' disk is mapped to 'storage/app/public'.
        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found.');
        }

        // Return the file as a response.
        // This handles MIME types and other headers automatically.
        return Storage::disk('public')->response($filePath);
    }
}
