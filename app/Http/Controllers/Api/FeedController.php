<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feed;
use App\Models\FeedLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedController extends Controller
{
    public function toggleLike($feedId)
    {
        $user = Auth::user();
        $feed = Feed::findOrFail($feedId);

        $like = FeedLike::where('feed_id', $feed->id)->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
            $feed->decrement('likes_count');
            return response()->json(['liked' => false]);
        } else {
            FeedLike::create(['feed_id' => $feed->id, 'user_id' => $user->id]);
            $feed->increment('likes_count');
            return response()->json(['liked' => true]);
        }
    }
    public function index(Request $request)
    {
        $feeds = Feed::with('category', 'likes')->latest()->get();

        $feeds = $feeds->map(function ($feed) use ($request) {
            return [
                'id' => $feed->id,
                'caption' => $feed->caption,
                'featured_image' => asset($feed->featured_image),
                'likes_count' => $feed->likes_count,
                'category' => $feed->category->name ?? null,
                'is_liked' => $feed->isLikedBy($request->user()->id),
            ];
        });

        return response()->json(['feeds' => $feeds]);
    }

    public function store(Request $request)
    {
       
        $validated = $request->validate([
            'category_id' => 'nullable|exists:feed_categories,id',
            'caption' => 'nullable|string|max:255',
            'featured_image' => 'nullable',
        ]);

        // Save image
        $beforePath = $request->file('featured_image')->store('feeds', 'public');
        // $afterPath = $request->file('after_image')->store('feeds', 'public');

        $feed = Feed::create([
            'admin_id' => Auth::id(),
            'category_id' => $validated['category_id'],
            'caption' => $validated['caption'],
            'featured_image' => $beforePath,
            // 'before_image' => $beforePath,
            // 'after_image' => $afterPath,
        ]);

        return response()->json(['feed' => $feed], 201);
    }
}
