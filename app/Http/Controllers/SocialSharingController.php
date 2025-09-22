<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SocialSharingController extends Controller
{
    /**
     * Generate social sharing links for a property
     */
    public function getShareLinks(Property $property): JsonResponse
    {
        $propertyUrl = route('properties.show', $property);
        $propertyUrl = route('properties.show', $property);
        $title = urlencode($property->title);
        $price = urlencode('$' . number_format($property->price));

        // Get first image for sharing
        $images = json_decode($property->images ?? '[]', true);
        $imageUrl = !empty($images) ? asset('storage/' . $images[0]['path']) : asset('images/default-property.jpg');
        $shareData = [
            'property' => [
                'id' => $property->id,
                'title' => $property->title,
                'price' => $property->price,
                'formatted_price' => '$' . number_format($property->price),
                'url' => $propertyUrl,
                'image' => $imageUrl
            ],
            'share_links' => [
                'facebook' => "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($propertyUrl) . "&quote=" . $title,
                'twitter' => "https://twitter.com/intent/tweet?url=" . urlencode($propertyUrl) . "&text=" . $title . " - " . $price,
                'linkedin' => "https://www.linkedin.com/sharing/share-offsite/?url=" . urlencode($propertyUrl),
                'whatsapp' => "https://wa.me/?text=" . $title . " - " . $price . " " . urlencode($propertyUrl),
                'telegram' => "https://t.me/share/url?url=" . urlencode($propertyUrl) . "&text=" . $title,
                'email' => "mailto:?subject=" . $title . "&body=Check out this property: " . urlencode($propertyUrl),
                'copy_link' => $propertyUrl
            ],
            'meta_tags' => [
                'og:title' => $property->title,
                'og:description' => substr($property->description, 0, 200) . '...',
                'og:image' => $imageUrl,
                'og:url' => $propertyUrl,
                'og:type' => 'website',
                'twitter:card' => 'summary_large_image',
                'twitter:title' => $property->title,
                'twitter:description' => substr($property->description, 0, 200) . '...',
                'twitter:image' => $imageUrl
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $shareData
        ]);
    }

    /**
     * Share property via email
     */
    public function shareViaEmail(Request $request, Property $property): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'recipient_email' => 'required|email',
            'recipient_name' => 'nullable|string|max:255',
            'sender_name' => 'required|string|max:255',
            'sender_email' => 'required|email',
            'message' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = [
                'property' => $property,
                'recipient_name' => $request->recipient_name ?: 'Friend',
                'sender_name' => $request->sender_name,
                'sender_email' => $request->sender_email,
                'personal_message' => $request->message,
                'property_url' => route('properties.show', $property),
                'property_images' => $this->getPropertyImages($property, 3)
            ];

            Mail::send('emails.property-share', $data, function ($message) use ($request, $property) {
                $message->to($request->recipient_email, $request->recipient_name)
                        ->subject('Check out this property: ' . $property->title)
                        ->replyTo($request->sender_email, $request->sender_name);
            });

            return response()->json([
                'success' => true,
                'message' => 'Property shared successfully via email'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate and track short URL for property sharing
     */
    public function generateShortUrl(Property $property): JsonResponse
    {
        // In a real application, you would use a URL shortening service like bit.ly
        // For now, we'll create a simple tracking system

        $shortCode = $this->generateShortCode();
        $originalUrl = route('properties.show', $property);

        // Store in cache for tracking (in production, use database)
        cache()->put("short_url_{$shortCode}", [
            'property_id' => $property->id,
            'original_url' => $originalUrl,
            'created_at' => now(),
            'clicks' => 0
        ], now()->addDays(30));

        $shortUrl = route('shared.redirect', $shortCode);

        return response()->json([
            'success' => true,
            'data' => [
                'short_url' => $shortUrl,
                'original_url' => $originalUrl,
                'short_code' => $shortCode,
                'expires_at' => now()->addDays(30)->toISOString()
            ]
        ]);
    }

    /**
     * Redirect from short URL and track clicks
     */
    public function redirectShortUrl($shortCode)
    {
        $urlData = cache()->get("short_url_{$shortCode}");

        if (!$urlData) {
            abort(404, 'Short URL not found or expired');
        }

        // Increment click count
        $urlData['clicks']++;
        cache()->put("short_url_{$shortCode}", $urlData, now()->addDays(30));

        // Track the click (in production, you'd store this in database)
        $this->trackClick($shortCode, $urlData['property_id']);

        return redirect($urlData['original_url']);
    }

    /**
     * Get sharing statistics for a property
     */
    public function getSharingStats(Property $property): JsonResponse
    {
        // In a real application, this would come from your analytics database
        $stats = [
            'total_shares' => rand(10, 100),
            'shares_by_platform' => [
                'facebook' => rand(5, 30),
                'twitter' => rand(2, 15),
                'whatsapp' => rand(8, 25),
                'email' => rand(3, 20),
                'copy_link' => rand(5, 35)
            ],
            'clicks_from_shares' => rand(20, 200),
            'share_to_view_ratio' => round(rand(15, 45) / 100, 2),
            'most_shared_platform' => 'facebook',
            'recent_shares' => [
                ['platform' => 'whatsapp', 'shared_at' => now()->subHours(2)],
                ['platform' => 'facebook', 'shared_at' => now()->subHours(5)],
                ['platform' => 'email', 'shared_at' => now()->subDays(1)]
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Share property on social media (track share action)
     */
    public function trackShare(Request $request, Property $property): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'platform' => 'required|string|in:facebook,twitter,linkedin,whatsapp,telegram,email,copy_link'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $platform = $request->platform;

        // Track the share action (in production, store in database)
        $shareData = [
            'property_id' => $property->id,
            'platform' => $platform,
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'shared_at' => now()
        ];

        // For demo, we'll just cache it
        $cacheKey = "property_shares_{$property->id}";
        $shares = cache()->get($cacheKey, []);
        $shares[] = $shareData;
        cache()->put($cacheKey, $shares, now()->addDays(30));

        return response()->json([
            'success' => true,
            'message' => 'Share tracked successfully',
            'data' => [
                'platform' => $platform,
                'total_shares' => count($shares)
            ]
        ]);
    }

    /**
     * Get popular properties based on shares
     */
    public function getPopularSharedProperties(): JsonResponse
    {
        // In a real application, this would be based on actual sharing data
        $popularProperties = Property::where('status', 'active')
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get(['id', 'title', 'price', 'address', 'images', 'views']);

        $transformed = $popularProperties->map(function ($property) {
            return [
                'id' => $property->id,
                'title' => $property->title,
                'price' => $property->price,
                'formatted_price' => '$' . number_format($property->price),
                'address' => $property->address,
                'views' => $property->views,
                'estimated_shares' => rand(5, 50),
                'share_url' => route('properties.show', $property),
                'image' => $this->getPropertyImages($property, 1)[0] ?? null
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'popular_properties' => $transformed,
                'total_count' => $transformed->count()
            ]
        ]);
    }

    private function generateShortCode()
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    }

    private function trackClick($shortCode, $propertyId)
    {
        // In production, you would store click analytics in database
        $clickData = [
            'short_code' => $shortCode,
            'property_id' => $propertyId,
            'clicked_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ];

        // For demo, store in cache
        $cacheKey = "short_url_clicks_{$shortCode}";
        $clicks = cache()->get($cacheKey, []);
        $clicks[] = $clickData;
        cache()->put($cacheKey, $clicks, now()->addDays(30));
    }

    private function getPropertyImages($property, $limit = 3)
    {
        if (!$property->images) {
            return [];
        }

        $images = json_decode($property->images, true) ?? [];

        return array_map(function ($image) {
            return [
                'url' => asset('storage/' . $image['path']),
                'thumbnail_url' => isset($image['thumbnail_path'])
                    ? asset('storage/' . $image['thumbnail_path'])
                    : asset('storage/' . $image['path'])
            ];
        }, array_slice($images, 0, $limit));
    }
}
