<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
        // Validasi input
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_draft' => 'sometimes|boolean',
            'published_at' => 'sometimes|date|nullable',
        ]);

        // Buat post baru
        $post = Post::create([
            'user_id' => $request->user()->id,            // Get from session
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_draft' => $validated['is_draft'] ?? true, // default draft true
            'published_at' => $validated['published_at'] ?? null,
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

    /**
     * Show the form for editing a post.
     *
     * GET /api/posts/{id}/edit
     *
     * For API, this can just return a simple message.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function edit($id)
    {
        return response()->json(['message' => 'posts.edit'], 200);
    }

    /**
     * Update the specified post.
     *
     * PUT/PATCH /api/posts/{id}
     *
     * Only the post's author can update the post.
     * Validates submitted data before updating the post.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        // Checkk author
        if ($request->user()->id !== $post->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validation data
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'is_draft' => 'sometimes|boolean',
            'published_at' => 'sometimes|date|nullable',
        ]);

        // Update only field
        $post->update($request->only([
            'title', 'content', 'is_draft', 'published_at'
        ]));

        return response()->json($post, 200);
    }

    /**
     * Remove the specified post.
     *
     * DELETE /api/posts/{id}
     *
     * Only the post's author can delete the post.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        // Cek author
        if ($request->user()->id !== $post->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted'], 200);
    }







}
