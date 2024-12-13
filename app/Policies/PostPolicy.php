<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{

    public function update(User $user, Post $post)
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->id === $post->author_id;
    }

    public function delete(User $user, Post $post)
    {
        if ($user->role === 'admin') {
            return true;
        }
        return $user->id === $post->author_id;
    }

    public function show(User $user, Post $post)
    {
        if ($user->role === 'admin') {
            return true;
        }
        return $user->id === $post->author_id;
    }
}
