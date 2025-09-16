<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImageController extends Controller
{
    /**
     * Upload images for a property
     */
    public function upload(Request $request, Property $property)
    {
        // Check if user owns the property
        if ($property->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // Max 10MB per image
        ]);

        $uploadedImages = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                // Generate unique filename
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                // Store original image
                $path = $file->storeAs('properties/' . $property->id, $filename, 'public');
                
                // Create thumbnail (optional - using intervention/image if available)
                $this->createThumbnail($file, $property->id, $filename);
                
                // Save to database
                $image = PropertyImage::create([
                    'property_id' => $property->id,
                    'file_name' => $filename,
                    'file_path' => $path,
                    'file_url' => Storage::url($path),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'alt_text' => 'Property image for ' . $property->title,
                    'is_primary' => PropertyImage::where('property_id', $property->id)->count() === 0, // First image is primary
                ]);
                
                $uploadedImages[] = [
                    'id' => $image->id,
                    'filename' => $filename,
                    'url' => Storage::url($path),
                    'thumbnail_url' => Storage::url('properties/' . $property->id . '/thumbnails/' . $filename),
                    'is_primary' => $image->is_primary,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'images' => $uploadedImages,
            'message' => count($uploadedImages) . ' image(s) uploaded successfully!'
        ]);
    }

    /**
     * Delete an image
     */
    public function delete(Request $request, PropertyImage $image)
    {
        // Check if user owns the property
        if ($image->property->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Delete files from storage
        Storage::disk('public')->delete($image->path);
        Storage::disk('public')->delete('properties/' . $image->property_id . '/thumbnails/' . $image->filename);

        // If this was the primary image, make another image primary
        if ($image->is_primary) {
            $nextImage = PropertyImage::where('property_id', $image->property_id)
                ->where('id', '!=', $image->id)
                ->first();
            
            if ($nextImage) {
                $nextImage->update(['is_primary' => true]);
            }
        }

        // Delete from database
        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully!'
        ]);
    }

    /**
     * Set image as primary
     */
    public function setPrimary(Request $request, PropertyImage $image)
    {
        // Check if user owns the property
        if ($image->property->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Remove primary status from other images
        PropertyImage::where('property_id', $image->property_id)
            ->update(['is_primary' => false]);

        // Set this image as primary
        $image->update(['is_primary' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Primary image updated successfully!'
        ]);
    }

    /**
     * Get images for a property
     */
    public function getImages(Property $property)
    {
        $images = $property->images()->orderBy('is_primary', 'desc')->orderBy('created_at')->get();
        
        $imageData = $images->map(function ($image) {
            return [
                'id' => $image->id,
                'filename' => $image->filename,
                'url' => Storage::url($image->path),
                'thumbnail_url' => Storage::url('properties/' . $image->property_id . '/thumbnails/' . $image->filename),
                'is_primary' => $image->is_primary,
                'original_name' => $image->original_name,
                'file_size' => $this->formatFileSize($image->file_size),
            ];
        });

        return response()->json([
            'success' => true,
            'images' => $imageData
        ]);
    }

    /**
     * Create thumbnail for image
     */
    private function createThumbnail($file, $propertyId, $filename)
    {
        try {
            // Create thumbnails directory if it doesn't exist
            $thumbnailDir = storage_path('app/public/properties/' . $propertyId . '/thumbnails');
            if (!file_exists($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }

            // Create thumbnail (300x200)
            $thumbnailPath = $thumbnailDir . '/' . $filename;
            
            // Simple thumbnail creation without intervention/image
            $sourceImage = imagecreatefromstring(file_get_contents($file->getPathname()));
            if ($sourceImage) {
                $width = imagesx($sourceImage);
                $height = imagesy($sourceImage);
                
                // Calculate thumbnail dimensions
                $thumbWidth = 300;
                $thumbHeight = 200;
                
                // Create thumbnail image
                $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
                imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
                
                // Save thumbnail
                $extension = strtolower($file->getClientOriginalExtension());
                switch ($extension) {
                    case 'jpg':
                    case 'jpeg':
                        imagejpeg($thumbnail, $thumbnailPath, 85);
                        break;
                    case 'png':
                        imagepng($thumbnail, $thumbnailPath, 8);
                        break;
                    case 'gif':
                        imagegif($thumbnail, $thumbnailPath);
                        break;
                }
                
                imagedestroy($sourceImage);
                imagedestroy($thumbnail);
            }
        } catch (\Exception $e) {
            // Thumbnail creation failed, but continue without it
            Log::warning('Thumbnail creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Format file size for display
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
