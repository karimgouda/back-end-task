<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\PostRequest;
use App\Http\Traits\apiResponse;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{
    use apiResponse;

    public function posts()
    {
        $user = auth()->user();
        $posts = Cache::remember("posts_for_user_{$user->id}", now()->addMinutes(10), function () use ($user) {
            return Post::forUser($user)->get();
        });
        return $this->apiResponse(200, 'All Posts', null, $posts);
    }

    public function showPost($id)
    {
        $post = Cache::remember("post_{$id}", now()->addMinutes(10), function () use ($id) {
            return Post::find($id);
        });

        $this->authorize('show', $post);
        return $this->apiResponse(200, 'Single Post', null, $post->load('author'));
    }

    public function create(PostRequest $request)
    {
        $requestData = $request->validated();
        $requestData['author_id'] = auth('api')->id();
        $post = Post::create($requestData);
        Cache::forget("posts_for_user_" . auth('api')->id());
        return $this->apiResponse(200, 'Post Created Successfully', null, $post->load('author'));
    }

    public function update($id, PostRequest $request)
    {
        $post = Post::find($id);

        $this->authorize('update', $post);
        $requestData = $request->validated();
        $post->update($requestData);
        Cache::forget("post_{$id}");
        Cache::forget("posts_for_user_" . auth('api')->id());
        return $this->apiResponse(200, 'Post Updated Successfully', null, $post->load('author'));
    }

    public function delete($id)
    {
        $post = Post::find($id);
        $this->authorize('delete', $post);
        $post->delete();
        Cache::forget("post_{$id}");
        Cache::forget("posts_for_user_" . auth('api')->id());
        return $this->apiResponse(200, 'Post Deleted Successfully');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $posts = Post::with('author')
            ->where('title', 'LIKE', "%{$query}%")
            ->orWhereHas('author', function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->orWhere('category', 'LIKE', "%{$query}%")
            ->get();

        if ($posts->isEmpty()) {
            return $this->apiResponse(404, 'No posts found');
        }

        return $this->apiResponse(200, 'Search Results', null, $posts);
    }

    public function filterPosts(Request $request)
    {
        $validated = $request->validate([
            'category' => 'nullable|string|in:Technology,Lifestyle,Education',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $cacheKey = 'filtered_posts_' . md5(json_encode($request->all()));

        $posts = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($validated) {
            $query = Post::query();

            if (isset($validated['category'])) {
                $query->where('category', $validated['category']);
            }

            if (isset($validated['start_date']) && isset($validated['end_date'])) {
                $query->whereBetween('created_at', [
                    Carbon::parse($validated['start_date'])->startOfDay(),
                    Carbon::parse($validated['end_date'])->endOfDay(),
                ]);
            }

            return $query->with('author')->get();
        });

        return $this->apiResponse(200, 'Filtered Posts', null, $posts);
    }
}
