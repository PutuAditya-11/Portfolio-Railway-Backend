<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogPostController extends Controller
{
    public function index()
    {
        try {
            $posts = BlogPost::with(['category', 'tags', 'user'])
                ->published()
                ->orderBy('published_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $posts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($identifier)
    {
        try {
            // Try to find by slug first, then by ID
            $post = BlogPost::with(['category', 'tags', 'user'])
                ->published()
                ->where(function($query) use ($identifier) {
                    $query->where('slug', $identifier)
                          ->orWhere('id', $identifier);
                })
                ->first();

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $post
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // New method: Get post by slug specifically
    public function getBySlug($slug)
    {
        try {
            $post = BlogPost::with(['category', 'tags', 'user'])
                ->published()
                ->bySlug($slug)
                ->first();

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $post
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get related posts (same category)
    public function getRelated($identifier, Request $request)
    {
        try {
            // Find the main post
            $post = BlogPost::where('slug', $identifier)
                           ->orWhere('id', $identifier)
                           ->first();

            if (!$post || !$post->category_id) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $limit = $request->get('limit', 3);

            $relatedPosts = BlogPost::with(['category', 'tags', 'user'])
                ->published()
                ->where('category_id', $post->category_id)
                ->where('id', '!=', $post->id)
                ->orderBy('published_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $relatedPosts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch related posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}