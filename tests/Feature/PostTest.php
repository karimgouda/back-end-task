<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_retrieves_posts_and_caches_the_result()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        Post::factory()->count(5)->create(['author_id' => $user->id]);

        Cache::shouldReceive('remember')
            ->once()
            ->with(
                "posts_for_user_{$user->id}",
                Mockery::on(function ($time) {
                    return $time instanceof Carbon;
                }),
                Mockery::on(function ($closure) {
                    return is_callable($closure);
                })
            )
            ->andReturn(Post::forUser($user)->get());

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function it_retrieves_a_single_post_and_caches_the_result()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $post = Post::factory()->create(['author_id' => $user->id]);

        Cache::shouldReceive('remember')
            ->once()
            ->with(
                "post_{$post->id}",
                Mockery::on(function ($time) {
                    return $time instanceof Carbon;
                }),
                Mockery::on(function ($closure) {
                    return is_callable($closure);
                })
            )
            ->andReturn($post);

        $response = $this->getJson("/api/show-post/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $post->id);
    }

    /** @test */
    public function it_creates_a_post_and_clears_the_cache()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        Cache::shouldReceive('forget')
            ->once()
            ->with("posts_for_user_{$user->id}");

        $response = $this->postJson('/api/create', [
            'title' => 'PHP',
            'content' => 'PHP Content',
            'category' => 'Technology',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'PHP');

        $this->assertDatabaseHas('posts', ['title' => 'PHP']);
    }

    /** @test */
    public function it_updates_a_post_and_clears_the_cache()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $post = Post::factory()->create(['author_id' => $user->id]);

        Cache::shouldReceive('forget')
            ->once()
            ->with("post_{$post->id}")
            ->andReturnTrue();
        Cache::shouldReceive('forget')
            ->once()
            ->with("posts_for_user_{$user->id}")
            ->andReturnTrue();

        $response = $this->putJson("/api/update/{$post->id}", [
            'title' => 'Updated Post',
            'content' => $post->content,
            'category' => $post->category,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Post');

        $this->assertDatabaseHas('posts', ['title' => 'Updated Post']);
    }

    /** @test */
    public function it_deletes_a_post_and_clears_the_cache()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $post = Post::factory()->create(['author_id' => $user->id]);

        Cache::shouldReceive('forget')
            ->once()
            ->with("post_{$post->id}")
            ->andReturnTrue();
        Cache::shouldReceive('forget')
            ->once()
            ->with("posts_for_user_{$user->id}")
            ->andReturnTrue();

        $response = $this->deleteJson("/api/delete/{$post->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 200,
                'message' => 'Post Deleted Successfully',
            ]);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

}
