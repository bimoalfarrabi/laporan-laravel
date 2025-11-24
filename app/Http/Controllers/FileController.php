<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Serve a file from storage, with on-the-fly thumbnail generation.
     *
     * For better performance and more features, consider installing the Intervention Image library:
     * composer require intervention/image
     *
     * @param Request $request
     * @param string $path
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function serve(Request $request, $path)
    {
        $finalPath = $path;

        // Backward compatibility check for old paths
        if (!Storage::disk('nextcloud')->exists($finalPath)) {
            if (!Str::startsWith($finalPath, 'satpam/')) {
                $prefixedPath = 'satpam/' . $finalPath;
                if (Storage::disk('nextcloud')->exists($prefixedPath)) {
                    $finalPath = $prefixedPath;
                }
            }
        }
        
        // Security: Final check if the file exists on the 'nextcloud' disk.
        if (!Storage::disk('nextcloud')->exists($finalPath)) {
            abort(404, 'File not found.');
        }

        // Check if a specific size is requested for an image
        $size = $request->query('size');
        $extension = pathinfo($finalPath, PATHINFO_EXTENSION);

        if ($size && in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
            // Validate size format (e.g., 300x300)
            if (!preg_match('/^\d+x\d+$/', $size)) {
                abort(400, 'Invalid size format. Use WxH (e.g., 300x300).');
            }

            $dimensions = explode('x', $size);
            $width = (int) $dimensions[0];
            $height = (int) $dimensions[1];
            $thumbnailPath = $this->getThumbnailPath($finalPath, $width, $height);

            // If thumbnail exists, serve it directly
            if (Storage::disk('public')->exists($thumbnailPath)) {
                return response()->file(Storage::disk('public')->path($thumbnailPath));
            }

            // If not, generate the thumbnail
            try {
                $this->generateThumbnail($finalPath, $thumbnailPath, $width, $height);
                return response()->file(Storage::disk('public')->path($thumbnailPath));
            } catch (\Exception $e) {
                // If thumbnail generation fails, log the error and consider falling back
                \Illuminate\Support\Facades\Log::error("Thumbnail generation failed for {$finalPath}: " . $e->getMessage());
                // Fallback to serving the original image
            }
        }

        // Return the original file if no size is requested or if it's not an image
        // Return the original file from Nextcloud
        $content = Storage::disk('nextcloud')->get($finalPath);
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($content);

        if (!$mimeType) {
            $extension = pathinfo($finalPath, PATHINFO_EXTENSION);
            $mimeType = match (strtolower($extension)) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'mp4' => 'video/mp4',
                'pdf' => 'application/pdf',
                default => 'application/octet-stream',
            };
        }
        
        return response($content)->header('Content-Type', $mimeType);
    }

    /**
     * Generate the path for the thumbnail.
     *
     * @param string $originalPath
     * @param int $width
     * @param int $height
     * @return string
     */
    private function getThumbnailPath($originalPath, $width, $height)
    {
        $directory = pathinfo($originalPath, PATHINFO_DIRNAME);
        $filename = pathinfo($originalPath, PATHINFO_FILENAME);
        $extension = pathinfo($originalPath, PATHINFO_EXTENSION);

        return "{$directory}/thumbnails/{$filename}_{$width}x{$height}.{$extension}";
    }

    /**
     * Generate and save a thumbnail using the GD library.
     *
     * @param string $originalPath
     * @param string $thumbnailPath
     * @param int $width
     * @param int $height
     * @throws \Exception
     */
    private function generateThumbnail($originalPath, $thumbnailPath, $width, $height)
    {
        // Get image content from Nextcloud
        $imageContent = Storage::disk('nextcloud')->get($originalPath);
        $imageResource = @imagecreatefromstring($imageContent);

        if ($imageResource === false) {
            throw new \Exception("Failed to create image resource from path: {$originalPath}");
        }

        $originalWidth = imagesx($imageResource);
        $originalHeight = imagesy($imageResource);

        // Calculate new dimensions while maintaining aspect ratio
        $ratio = min($width / $originalWidth, $height / $originalHeight);
        $newWidth = (int) ($originalWidth * $ratio);
        $newHeight = (int) ($originalHeight * $ratio);

        $newImageResource = imagecreatetruecolor($newWidth, $newHeight);

        // Handle transparency for PNG and GIF
        $extension = strtolower(pathinfo($originalPath, PATHINFO_EXTENSION));
        if ($extension === 'png' || $extension === 'gif') {
            imagecolortransparent($newImageResource, imagecolorallocatealpha($newImageResource, 0, 0, 0, 127));
            imagealphablending($newImageResource, false);
            imagesavealpha($newImageResource, true);
        }

        imagecopyresampled(
            $newImageResource,
            $imageResource,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );

        // Get the full path for the thumbnail to save it.
        $fullThumbnailPath = Storage::disk('public')->path($thumbnailPath);

        // Ensure the directory exists.
        $thumbnailDir = dirname($fullThumbnailPath);
        if (!file_exists($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }

        ob_start();
        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                imagejpeg($newImageResource, null, 90);
                break;
            case 'png':
                imagepng($newImageResource, null, 9);
                break;
            case 'gif':
                imagegif($newImageResource);
                break;
        }
        $thumbnailContent = ob_get_clean();

        // Save the thumbnail.
        Storage::disk('public')->put($thumbnailPath, $thumbnailContent);

        imagedestroy($imageResource);
        imagedestroy($newImageResource);
    }
}
