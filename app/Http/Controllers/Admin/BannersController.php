<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Feed;
use App\Models\FeedCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BannersController extends Controller
{
    /**
     * Get all banners/feeds
     */
    public function index(Request $request)
    {
        try {
            $query = Feed::with('category');

            // Filter by category
            if ($request->has('category') && $request->category && $request->category !== 'All') {
                $query->where('category_id', $request->category);
            }

            // Search
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where('caption', 'like', "%{$search}%");
            }

            // Filter by date
            if ($request->has('date') && $request->date) {
                $query->whereDate('created_at', $request->date);
            }

            $perPage = $request->get('per_page', 20);
            $banners = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $transformedBanners = $banners->getCollection()->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'caption' => $banner->caption ?? 'Get the best Service',
                    'description' => $banner->description ?? 'Edit by Mercy is the best photo editing platform, we offer various services and give the best prices',
                    'featured_image' => $banner->featured_image ? asset('storage/' . $banner->featured_image) : null,
                    'category' => $banner->category ? $banner->category->name : 'Uncategorized',
                    'category_id' => $banner->category_id,
                    'likes_count' => $banner->likes_count ?? 0,
                    'created_at' => $banner->created_at->format('m/d/y - h:i A'),
                ];
            });

            // Get categories for filter
            $categories = FeedCategory::all();

            return ResponseHelper::success([
                'banners' => $transformedBanners,
                'categories' => $categories,
                'pagination' => [
                    'current_page' => $banners->currentPage(),
                    'last_page' => $banners->lastPage(),
                    'per_page' => $banners->perPage(),
                    'total' => $banners->total(),
                ]
            ], 'Banners fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to fetch banners: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new banner
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'caption' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'banner_image' => 'required|image|max:10240', // 10MB max
                'banner_link' => 'nullable|url',
                'category_id' => 'nullable|exists:feed_categories,id',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            // Upload banner image
            $imagePath = $request->file('banner_image')->store('feeds', 'public');

            // Create banner/feed
            $banner = Feed::create([
                'admin_id' => Auth::id(),
                'category_id' => $request->category_id,
                'caption' => $request->caption ?? 'Get the best Service',
                'description' => $request->description ?? 'Edit by Mercy is the best photo editing platform',
                'featured_image' => $imagePath,
                'link' => $request->banner_link,
            ]);

            return ResponseHelper::success([
                'id' => $banner->id,
                'caption' => $banner->caption,
                'featured_image' => asset('storage/' . $banner->featured_image),
                'link' => $banner->link,
            ], 'Banner created successfully', 201);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to create banner: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single banner details
     */
    public function show($id)
    {
        try {
            $banner = Feed::with('category')->findOrFail($id);

            $data = [
                'id' => $banner->id,
                'caption' => $banner->caption,
                'description' => $banner->description,
                'featured_image' => $banner->featured_image ? asset('storage/' . $banner->featured_image) : null,
                'link' => $banner->link ?? null,
                'category' => $banner->category ? $banner->category->name : null,
                'category_id' => $banner->category_id,
                'likes_count' => $banner->likes_count ?? 0,
                'created_at' => $banner->created_at->format('m/d/y - h:i A'),
            ];

            return ResponseHelper::success($data, 'Banner details fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Banner not found: ' . $e->getMessage(), 404);
        }
    }

    /**
     * Update banner
     */
    public function update(Request $request, $id)
    {
        try {
            $banner = Feed::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'caption' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'banner_image' => 'sometimes|image|max:10240',
                'banner_link' => 'nullable|url',
                'category_id' => 'nullable|exists:feed_categories,id',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            // Update fields
            if ($request->has('caption')) $banner->caption = $request->caption;
            if ($request->has('description')) $banner->description = $request->description;
            if ($request->has('banner_link')) $banner->link = $request->banner_link;
            if ($request->has('category_id')) $banner->category_id = $request->category_id;

            // Update image if provided
            if ($request->hasFile('banner_image')) {
                // Delete old image
                if ($banner->featured_image && Storage::disk('public')->exists($banner->featured_image)) {
                    Storage::disk('public')->delete($banner->featured_image);
                }

                $imagePath = $request->file('banner_image')->store('feeds', 'public');
                $banner->featured_image = $imagePath;
            }

            $banner->save();

            return ResponseHelper::success([
                'id' => $banner->id,
                'caption' => $banner->caption,
                'featured_image' => asset('storage/' . $banner->featured_image),
            ], 'Banner updated successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to update banner: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete banner
     */
    public function destroy($id)
    {
        try {
            $banner = Feed::findOrFail($id);

            // Delete image
            if ($banner->featured_image && Storage::disk('public')->exists($banner->featured_image)) {
                Storage::disk('public')->delete($banner->featured_image);
            }

            $banner->delete();

            return ResponseHelper::success(null, 'Banner deleted successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to delete banner: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get banner categories
     */
    public function getCategories()
    {
        try {
            $categories = FeedCategory::all();

            return ResponseHelper::success($categories, 'Categories fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to fetch categories: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new category
     */
    public function createCategory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:feed_categories,name',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $category = FeedCategory::create([
                'name' => $request->name,
            ]);

            return ResponseHelper::success($category, 'Category created successfully', 201);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to create category: ' . $e->getMessage(), 500);
        }
    }
}


