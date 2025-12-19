<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{

        public function __construct()
    {
        // Just user has been login that can access
        $this->middleware('auth')->only(['store', 'update', 'destroy']);
    }

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
        return response()->json(['message' => 'posts.create'], 200);
    }

    /**
     * Store a newly created post.
     *
     * POST /api/posts
     *
     * Only authenticated users can create posts (session-based auth).
     * Validates submitted data before creating the post.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_draft' => 'sometimes|boolean',
            'published_at' => 'sometimes|date|nullable',
        ]);

        $post = Post::create([
            'user_id' => $request->user()->id, // get from session
            'title' => $request->title,
            'content' => $request->content,
            'is_draft' => $request->input('is_draft', true),
            'published_at' => $request->published_at,
        ]);

        return response()->json($post, 201);
    }

    /**
     * Display a single post.
     *
     * GET /api/posts/{id}
     *
     * Only active posts (not draft, already published) are retrievable.
     * Returns 404 if the post does not exist, is a draft, or scheduled.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $post = Post::with('user')
            ->where('id', $id)
            ->where('is_draft', false)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now())
            ->first();

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        return response()->json($post, 200);
    }




}
