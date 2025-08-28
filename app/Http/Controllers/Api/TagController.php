<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    /**
     * Get all tags
     */
    public function index(): JsonResponse
    {
        $tags = Tag::withCount(['blogPosts' => function ($query) {
            $query->where('status', 'published')
                  ->where('published_at', '<=', now());
        }])->get();

        return response()->json([
            'success' => true,
            'data' => $tags
        ]);
    }

    /**
     * Get single tag by slug
     */
    public function show($slug): JsonResponse
    {
        $tag = Tag::where('slug', $slug)
            ->withCount(['blogPosts' => function ($query) {
                $query->where('status', 'published')
                      ->where('published_at', '<=', now());
            }])
            ->first();

        if (!$tag) {
            return response()->json([
                'success' => false,
                'message' => 'Tag not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tag
        ]);
    }

    /**
     * Get posts by tag slug
     */
    public function posts(Request $request, $slug): JsonResponse
    {
        $tag = Tag::where('slug', $slug)->first();

        if (!$tag) {
            return response()->json([
                'success' => false,
                'message' => 'Tag not found'
            ], 404);
        }

        $posts = $tag->blogPosts()
            ->with(['category', 'user', 'tags'])
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'tag' => $tag,
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ]
        ]);
    }
}