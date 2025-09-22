<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ImageUploadController extends Controller
{
    /**
     * Handle multiple image uploads for properties
     */
    public function uploadPropertyImages(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|max:20',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240', // 10MB max per image
            'property_id' => 'nullable|exists:properties,id',
            'resize' => 'nullable|boolean',
            'watermark' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $uploadedImages = [];
        $propertyId = $request->property_id ?? 'temp_' . time();
        $resize = $request->get('resize', true);
        $watermark = $request->get('watermark', false);

        try {
            foreach ($request->file('images') as $index => $image) {
                $uploadResult = $this->processAndStoreImage($image, $propertyId, $index, $resize, $watermark);
                $uploadedImages[] = $uploadResult;
            }

            return response()->json([
                'success' => true,
                'message' => 'Images uploaded successfully',
                'data' => [
                    'images' => $uploadedImages,
                    'count' => count($uploadedImages)
                ]
            ]);

        } catch (\Exception $e) {
            // Clean up any uploaded files on error
            foreach ($uploadedImages as $image) {
                Storage::disk('public')->delete($image['path']);
                if (isset($image['thumbnail_path'])) {
                    Storage::disk('public')->delete($image['thumbnail_path']);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload images: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle single image upload
     */
    public function uploadSingleImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
            'property_id' => 'nullable|exists:properties,id',
            'type' => 'nullable|string|in:property,profile,document',
            'resize' => 'nullable|boolean',
            'watermark' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $propertyId = $request->property_id ?? 'temp_' . time();
        $type = $request->get('type', 'property');
        $resize = $request->get('resize', true);
        $watermark = $request->get('watermark', false);

        try {
            $uploadResult = $this->processAndStoreImage(
                $request->file('image'),
                $propertyId,
                0,
                $resize,
                $watermark,
                $type
            );

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => $uploadResult
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an uploaded image
     */
    public function deleteImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image_path' => 'required|string',
            'property_id' => 'nullable|exists:properties,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $imagePath = $request->image_path;

            // Security check - ensure the path is within allowed directories
            if (!str_starts_with($imagePath, 'properties/') && !str_starts_with($imagePath, 'temp/')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid image path'
                ], 403);
            }

            // Delete main image
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            // Delete thumbnail if exists
            $thumbnailPath = str_replace('/images/', '/thumbnails/', $imagePath);
            if (Storage::disk('public')->exists($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder images for a property
     */
    public function reorderImages(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'image_order' => 'required|array',
            'image_order.*' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $property = \App\Models\Property::find($request->property_id);
            $currentImages = json_decode($property->images ?? '[]', true);
            $newOrder = $request->image_order;

            // Reorder images based on the new order
            $reorderedImages = [];
            foreach ($newOrder as $imagePath) {
                foreach ($currentImages as $image) {
                    if ($image['path'] === $imagePath) {
                        $reorderedImages[] = $image;
                        break;
                    }
                }
            }

            // Update property with new image order
            $property->update(['images' => json_encode($reorderedImages)]);

            return response()->json([
                'success' => true,
                'message' => 'Images reordered successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder images: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get image upload progress (for large files)
     */
    public function getUploadProgress(Request $request): JsonResponse
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            return response()->json([
                'success' => false,
                'message' => 'Session ID required'
            ], 400);
        }

        // In a real implementation, you'd track upload progress in cache/database
        $progress = cache()->get("upload_progress_{$sessionId}", 0);

        return response()->json([
            'success' => true,
            'progress' => $progress
        ]);
    }

    /**
     * Process and store image with various optimizations
     */
    private function processAndStoreImage($image, $propertyId, $index, $resize = true, $watermark = false, $type = 'property')
    {
        $originalName = $image->getClientOriginalName();
        $extension = $image->getClientOriginalExtension();
        $filename = $propertyId . '_' . time() . '_' . $index . '.' . $extension;

        // Different storage paths based on type
        $storagePath = match($type) {
            'profile' => "profiles/{$propertyId}",
            'document' => "documents/{$propertyId}",
            default => "properties/{$propertyId}/images"
        };

        $fullPath = $storagePath . '/' . $filename;
        $thumbnailPath = str_replace('/images/', '/thumbnails/', $storagePath) . '/thumb_' . $filename;

        // Store original image
        $image->storeAs($storagePath, $filename, 'public');

        $imageInfo = [
            'original_name' => $originalName,
            'filename' => $filename,
            'path' => $fullPath,
            'size' => $image->getSize(),
            'mime_type' => $image->getMimeType(),
            'width' => null,
            'height' => null,
            'thumbnail_path' => null
        ];

        // Process image if needed
        if ($resize || $watermark) {
            try {
                $processedImage = Image::read(storage_path('app/public/' . $fullPath));

                // Get original dimensions
                $imageInfo['width'] = $processedImage->width();
                $imageInfo['height'] = $processedImage->height();

                // Resize if requested and image is large
                if ($resize && ($processedImage->width() > 1920 || $processedImage->height() > 1920)) {
                    $processedImage->scale(width: 1920, height: 1920);
                    $processedImage->save(storage_path('app/public/' . $fullPath), quality: 85);
                }

                // Add watermark if requested
                if ($watermark) {
                    $this->addWatermark($processedImage);
                    $processedImage->save(storage_path('app/public/' . $fullPath), quality: 85);
                }

                // Create thumbnail
                $thumbnail = clone $processedImage;
                $thumbnail->scale(width: 300, height: 300);

                // Ensure thumbnail directory exists
                $thumbnailDir = dirname(storage_path('app/public/' . $thumbnailPath));
                if (!file_exists($thumbnailDir)) {
                    mkdir($thumbnailDir, 0755, true);
                }

                $thumbnail->save(storage_path('app/public/' . $thumbnailPath), quality: 80);
                $imageInfo['thumbnail_path'] = $thumbnailPath;

            } catch (\Exception $e) {
                // If image processing fails, continue with original
                Log::warning("Image processing failed: " . $e->getMessage());
            }
        }

        return $imageInfo;
    }

    /**
     * Add watermark to image
     */
    private function addWatermark($image)
    {
        // You would implement watermark logic here
        // For now, we'll just add a simple text watermark
        try {
            $watermarkText = config('app.name', 'Property Portal');

            // Add text watermark in bottom right corner
            $image->text($watermarkText, $image->width() - 10, $image->height() - 10, function ($font) {
                $font->file(public_path('fonts/arial.ttf')); // Make sure font exists
                $font->size(20);
                $font->color('#ffffff');
                $font->align('right');
                $font->valign('bottom');
                $font->angle(0);
            });
        } catch (\Exception $e) {
            Log::warning("Watermark failed: " . $e->getMessage());
        }
    }

    /**
     * Clean up temporary images older than 24 hours
     */
    public function cleanupTempImages(): JsonResponse
    {
        try {
            $tempDirectories = Storage::disk('public')->directories('temp');
            $cleaned = 0;

            foreach ($tempDirectories as $dir) {
                $dirName = basename($dir);

                // Check if directory name starts with 'temp_' and extract timestamp
                if (str_starts_with($dirName, 'temp_')) {
                    $timestamp = (int) str_replace('temp_', '', $dirName);
                    $ageInHours = (time() - $timestamp) / 3600;

                    // Delete directories older than 24 hours
                    if ($ageInHours > 24) {
                        Storage::disk('public')->deleteDirectory($dir);
                        $cleaned++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Cleaned up {$cleaned} temporary directories"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cleanup failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
