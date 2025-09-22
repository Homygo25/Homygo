<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property string $make
 * @property string $model
 * @property int $year
 * @property string|null $trim
 * @property string $condition
 * @property int|null $mileage
 * @property string $mileage_unit
 * @property string $price
 * @property string $currency
 * @property string|null $exterior_color
 * @property string|null $interior_color
 * @property string|null $transmission
 * @property string|null $fuel_type
 * @property string|null $engine_size
 * @property int|null $doors
 * @property string|null $body_type
 * @property string|null $drivetrain
 * @property string|null $vin
 * @property string|null $address
 * @property string $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string $country
 * @property string|null $latitude
 * @property string|null $longitude
 * @property int $user_id
 * @property int $category_id
 * @property int|null $location_id
 * @property string $status
 * @property int $is_featured
 * @property int $is_verified
 * @property string|null $features
 * @property string|null $contact_name
 * @property string|null $contact_phone
 * @property string|null $contact_email
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $featured_until
 * @property string|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereBodyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereCondition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereContactEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereDoors($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereDrivetrain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereEngineSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereExteriorColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereFeaturedUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereFuelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereInteriorColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereIsVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereMake($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereMileage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereMileageUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereTransmission($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereTrim($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereVin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereYear($value)
 */
	class Car extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $icon
 * @property string $color
 * @property string $type
 * @property int|null $parent_id
 * @property int $sort_order
 * @property bool $is_active
 * @property array<array-key, mixed>|null $meta_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Car> $cars
 * @property-read int|null $cars_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Contractor> $contractors
 * @property-read int|null $contractors_count
 * @property-read mixed $depth
 * @property-read mixed $full_name
 * @property-read mixed $icon_url
 * @property-read Category|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Property> $properties
 * @property-read int|null $properties_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category forType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category mainCategories()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category subCategories()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereMetaData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $business_name
 * @property string $slug
 * @property string $description
 * @property string $services_offered
 * @property string|null $hourly_rate
 * @property string|null $min_project_budget
 * @property string|null $max_project_budget
 * @property string $currency
 * @property int|null $years_experience
 * @property string|null $license_number
 * @property int $is_licensed
 * @property int $is_insured
 * @property string|null $insurance_provider
 * @property string|null $insurance_expiry
 * @property string|null $business_hours
 * @property int $available_weekends
 * @property int $emergency_services
 * @property string $address
 * @property string $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string $country
 * @property string|null $latitude
 * @property string|null $longitude
 * @property int|null $service_radius
 * @property string|null $service_areas
 * @property int $user_id
 * @property int $category_id
 * @property int|null $location_id
 * @property string $status
 * @property int $is_featured
 * @property int $is_verified
 * @property int $background_checked
 * @property string|null $certifications
 * @property string|null $specializations
 * @property string|null $contact_phone
 * @property string|null $contact_email
 * @property string|null $website
 * @property string|null $portfolio_images
 * @property string|null $previous_work_description
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $featured_until
 * @property string|null $verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereAvailableWeekends($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereBackgroundChecked($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereBusinessHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereBusinessName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereCertifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereContactEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereEmergencyServices($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereFeaturedUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereHourlyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereInsuranceExpiry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereInsuranceProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereIsInsured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereIsLicensed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereIsVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereLicenseNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereMaxProjectBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereMinProjectBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor wherePortfolioImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor wherePreviousWorkDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereServiceAreas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereServiceRadius($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereServicesOffered($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereSpecializations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereWebsite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contractor whereYearsExperience($value)
 */
	class Contractor extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $favoritable_type
 * @property int $favoritable_id
 * @property string|null $notes
 * @property string|null $tags
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Favorite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Favorite newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Favorite query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Favorite whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Favorite whereFavoritableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Favorite whereFavoritableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Favorite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Favorite whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Favorite whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Favorite whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Favorite whereUserId($value)
 */
	class Favorite extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property int|null $parent_id
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string|null $country_code
 * @property string|null $state_code
 * @property string|null $postal_code
 * @property string|null $description
 * @property bool $is_active
 * @property int $sort_order
 * @property array<array-key, mixed>|null $bounds
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Car> $cars
 * @property-read int|null $cars_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Location> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Contractor> $contractors
 * @property-read int|null $contractors_count
 * @property-read mixed $full_name
 * @property-read Location|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Property> $properties
 * @property-read int|null $properties_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserProfile> $userProfiles
 * @property-read int|null $user_profiles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location byCountry($countryCode)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location byState($stateCode)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location cities()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location countries()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location states()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereBounds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereStateCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereUpdatedAt($value)
 */
	class Location extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property string $type
 * @property string $property_type
 * @property numeric $price
 * @property string $currency
 * @property numeric|null $area
 * @property string $area_unit
 * @property int|null $bedrooms
 * @property int|null $bathrooms
 * @property int|null $parking_spaces
 * @property int|null $year_built
 * @property string $address
 * @property string $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string $country
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property int $user_id
 * @property int $category_id
 * @property int|null $location_id
 * @property string $status
 * @property bool $is_featured
 * @property bool $is_verified
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\PropertyView> $views
 * @property array<array-key, mixed>|null $features
 * @property array<array-key, mixed>|null $amenities
 * @property string|null $contact_name
 * @property string|null $contact_phone
 * @property string|null $contact_email
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property array<array-key, mixed>|null $meta_keywords
 * @property \Illuminate\Support\Carbon|null $featured_until
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Favorite> $favorites
 * @property-read int|null $favorites_count
 * @property-read mixed $average_rating
 * @property-read mixed $formatted_area
 * @property-read mixed $formatted_price
 * @property-read mixed $full_address
 * @property-read int|null $inquiries_count
 * @property-read mixed $inquiries_this_month
 * @property-read mixed $is_favorited_by_user
 * @property-read int|null $reviews_count
 * @property-read mixed $total_views
 * @property-read int|null $unique_views_count
 * @property-read mixed $views_this_month
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PropertyImage> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PropertyInquiry> $inquiries
 * @property-read \App\Models\Location|null $location
 * @property-read \App\Models\PropertyImage|null $primaryImage
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Review> $reviews
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PropertyView> $uniqueViews
 * @property-read \App\Models\User $user
 * @property-read int|null $views_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property byPropertyType($types)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property featured()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property forRent()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property forSale()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property inPriceRange($min = null, $max = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property nearLocation($lat, $lng, $radius = 10)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereAmenities($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereArea($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereAreaUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereBathrooms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereBedrooms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereContactEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereFeaturedUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereIsVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereMetaKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereParkingSpaces($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property wherePropertyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereViews($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereYearBuilt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property withBathrooms($bathrooms)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property withBedrooms($bedrooms)
 */
	class Property extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $property_id
 * @property string $file_name
 * @property string $file_path
 * @property string|null $file_url
 * @property string|null $alt_text
 * @property string|null $caption
 * @property string $type
 * @property string|null $mime_type
 * @property int|null $file_size
 * @property int|null $width
 * @property int|null $height
 * @property int $sort_order
 * @property bool $is_primary
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $formatted_size
 * @property-read mixed $thumbnail_url
 * @property-read mixed $url
 * @property-read \App\Models\Property $property
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage primary()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage secondary()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage whereAltText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage whereCaption($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage whereFileUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage wherePropertyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyImage whereWidth($value)
 */
	class PropertyImage extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $property_id
 * @property int|null $user_id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string $message
 * @property string $inquiry_type
 * @property string $status
 * @property string|null $response
 * @property \Illuminate\Support\Carbon|null $responded_at
 * @property string|null $ip_address
 * @property string|null $source
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $formatted_type
 * @property-read \App\Models\Property $property
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry responded()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereInquiryType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry wherePropertyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereRespondedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyInquiry whereUserId($value)
 */
	class PropertyInquiry extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $property_id
 * @property int|null $user_id
 * @property string $ip_address
 * @property string|null $user_agent
 * @property string|null $referrer
 * @property string|null $session_id
 * @property \Illuminate\Support\Carbon $viewed_at
 * @property int|null $time_spent
 * @property string $device_type
 * @property array<array-key, mixed>|null $location_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Property $property
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView unique()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView whereDeviceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView whereLocationData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView wherePropertyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView whereReferrer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView whereTimeSpent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView whereViewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyView withinDateRange($startDate, $endDate)
 */
	class PropertyView extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $reviewable_type
 * @property int $reviewable_id
 * @property int|null $reviewed_user_id
 * @property int $rating
 * @property string|null $title
 * @property string $comment
 * @property string|null $pros
 * @property string|null $cons
 * @property int $is_verified
 * @property int $is_approved
 * @property int $is_featured
 * @property string|null $approved_at
 * @property int|null $approved_by
 * @property int $helpful_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereCons($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereHelpfulCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereIsApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereIsVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review wherePros($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereReviewableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereReviewableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereReviewedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereUserId($value)
 */
	class Review extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property array<array-key, mixed> $search_criteria
 * @property bool $email_alerts
 * @property string $alert_frequency
 * @property \Illuminate\Support\Carbon|null $last_alert_sent
 * @property bool $is_active
 * @property int $results_count
 * @property \Illuminate\Support\Carbon|null $last_results_check
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $formatted_criteria
 * @property-read mixed $search_url
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedSearch active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedSearch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedSearch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedSearch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedSearch readyForAlert()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedSearch whereAlertFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedSearch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedSearch whereEmailAlerts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedSearch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedSearch whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedSearch whereLastAlertSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedSearch whereLastResultsCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedSearch whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedSearch whereResultsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedSearch whereSearchCriteria($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedSearch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedSearch whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedSearch withEmailAlerts()
 */
	class SavedSearch extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $type
 * @property string $filters
 * @property string|null $location_filters
 * @property string|null $min_price
 * @property string|null $max_price
 * @property int $email_alerts
 * @property string $alert_frequency
 * @property string|null $last_alert_sent
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchFilter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchFilter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchFilter query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchFilter whereAlertFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchFilter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchFilter whereEmailAlerts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchFilter whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchFilter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchFilter whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchFilter whereLastAlertSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchFilter whereLocationFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchFilter whereMaxPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchFilter whereMinPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchFilter whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchFilter whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchFilter whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchFilter whereUserId($value)
 */
	class SearchFilter extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Car> $cars
 * @property-read int|null $cars_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Contractor> $contractors
 * @property-read int|null $contractors_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Property> $favoriteProperties
 * @property-read int|null $favorite_properties_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Favorite> $favorites
 * @property-read int|null $favorites_count
 * @property-read mixed $active_saved_searches_count
 * @property-read mixed $avatar_url
 * @property-read mixed $full_name
 * @property-read mixed $my_properties_count
 * @property-read mixed $recent_activity
 * @property-read mixed $total_inquiries_received
 * @property-read mixed $total_property_views
 * @property-read int|null $unread_notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\UserProfile|null $profile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Property> $properties
 * @property-read int|null $properties_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PropertyInquiry> $propertyInquiries
 * @property-read int|null $property_inquiries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PropertyView> $propertyViews
 * @property-read int|null $property_views_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SavedSearch> $savedSearches
 * @property-read int|null $saved_searches_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SearchFilter> $searchFilters
 * @property-read int|null $search_filters_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserNotification> $unreadNotifications
 * @property-read \App\Models\UserProfile|null $userProfile
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $title
 * @property string $message
 * @property array<array-key, mixed>|null $data
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property string|null $action_url
 * @property string $priority
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $formatted_type
 * @property-read mixed $priority_color
 * @property-read mixed $time_ago
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification highPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification read()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification unread()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereActionUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereUserId($value)
 */
	class UserNotification extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $phone
 * @property string|null $bio
 * @property string|null $avatar
 * @property \Illuminate\Support\Carbon|null $date_of_birth
 * @property string|null $gender
 * @property string $role
 * @property string|null $company_name
 * @property string|null $website
 * @property string|null $license_number
 * @property string|null $address
 * @property int|null $location_id
 * @property int $is_verified
 * @property int $is_featured
 * @property string|null $social_links
 * @property string|null $preferences
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\UserProfileFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereIsVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereLicenseNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile wherePreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereSocialLinks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereWebsite($value)
 */
	class UserProfile extends \Eloquent {}
}

