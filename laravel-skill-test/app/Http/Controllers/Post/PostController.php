<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a paginated list of active posts.
     *
     * GET /api/posts
     *
     * Only posts that are not drafts and already published are included.
     * Includes the user data associated with each post.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $posts = Post::with('user') // eager load user
            ->where('is_draft', false)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now())
            ->orderBy('published_at', 'desc')
            ->paginate(20);

        return response()->json($posts, 200);
    }

    /**
 * Show the form for creating a new post.
 *
 * GET /api/posts/create
 *
 * For API, this can just return a simple message.
 *
 * @return \Illuminate\Http\JsonResponse|string
 */
public function create()
{
    // Bisa return string atau JSON
    return response()->json(['message' => 'posts.create'], 200);
    // Atau cukup:
    // return 'posts.create';
}

}
