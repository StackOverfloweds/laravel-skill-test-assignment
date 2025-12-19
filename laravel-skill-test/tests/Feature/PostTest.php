<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $otherUser;
    protected $activePost;
    protected $draftPost;

    protected function setUp(): void
    {
        parent::setUp();

        /** 
         * Create two users for testing: one author and one non-author 
         */
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();

        /** 
         * Create posts for testing: one active post and one draft 
         */
        $this->activePost = Post::factory()->for($this->user)->create([
            'is_draft' => false,
            'published_at' => now(),
        ]);

        $this->draftPost = Post::factory()->for($this->user)->create([
            'is_draft' => true,
            'published_at' => null,
        ]);
    }

    /** @test */
    public function index_returns_only_active_posts_with_user()
    {
        /** 
         * Test that only active posts (not draft, published) are returned 
         * along with the associated user
         */
        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id' => $this->activePost->id,
                     'user_id' => $this->user->id,
                 ])
                 ->assertJsonMissing([
                     'id' => $this->draftPost->id
                 ]);
    }

    /** @test */
    public function create_route_returns_message()
    {
        /** 
         * Test the /posts/create route returns the expected message 
         */
        $response = $this->getJson('/api/posts/create');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'posts.create']);
    }

    /** @test */
    public function authenticated_user_can_store_post()
    {
        /** 
         * Test authenticated user can create a post successfully 
         */
        $payload = [
            'title' => 'Test Post',
            'content' => 'Content for test post',
            'is_draft' => false,
            'published_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user)
                         ->postJson('/api/posts', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'title' => 'Test Post',
                     'user_id' => $this->user->id,
                 ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function show_returns_active_post_only()
    {
        /** 
         * Test retrieving a single active post returns correct data 
         */
        $response = $this->getJson("/api/posts/{$this->activePost->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id' => $this->activePost->id,
                     'user_id' => $this->user->id,
                 ]);
    }

    /** @test */
    public function show_returns_404_for_draft_or_scheduled_post()
    {
        /** 
         * Test that draft or scheduled posts cannot be retrieved 
         * and return 404
         */
        $response = $this->getJson("/api/posts/{$this->draftPost->id}");

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Post not found']);
    }

    /** @test */
    public function edit_route_returns_message()
    {
        /** 
         * Test /posts/{id}/edit route returns the expected message 
         */
        $response = $this->getJson("/api/posts/{$this->activePost->id}/edit");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'posts.edit']);
    }

    /** @test */
    public function author_can_update_post()
    {
        /** 
         * Test that the post author can successfully update the post 
         */
        $payload = ['title' => 'Updated Title'];

        $response = $this->actingAs($this->user)
                         ->putJson("/api/posts/{$this->activePost->id}", $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Updated Title']);

        $this->assertDatabaseHas('posts', [
            'id' => $this->activePost->id,
            'title' => 'Updated Title',
        ]);
    }

    /** @test */
    public function non_author_cannot_update_post()
    {
        /** 
         * Test that a non-author cannot update another user's post 
         */
        $payload = ['title' => 'Malicious Update'];

        $response = $this->actingAs($this->otherUser)
                         ->putJson("/api/posts/{$this->activePost->id}", $payload);

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Unauthorized']);

        $this->assertDatabaseMissing('posts', [
            'id' => $this->activePost->id,
            'title' => 'Malicious Update',
        ]);
    }

    /** @test */
    public function author_can_delete_post()
    {
        /** 
         * Test that the post author can delete the post successfully 
         */
        $response = $this->actingAs($this->user)
                         ->deleteJson("/api/posts/{$this->activePost->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Post deleted']);

        $this->assertDatabaseMissing('posts', [
            'id' => $this->activePost->id,
        ]);
    }

    /** @test */
    public function non_author_cannot_delete_post()
    {
        /** 
         * Test that a non-author cannot delete another user's post 
         */
        $response = $this->actingAs($this->otherUser)
                         ->deleteJson("/api/posts/{$this->activePost->id}");

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Unauthorized']);

        $this->assertDatabaseHas('posts', [
            'id' => $this->activePost->id,
        ]);
    }
}
