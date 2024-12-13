<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\CommentRequest;
use App\Http\Traits\apiResponse;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use apiResponse;
    public function comment($id , CommentRequest $request)
    {
        $post = Post::find($id);
        if (!$post){
            return $this->apiResponse(404,'Post Not Found');
        }
        $comment = Comment::updateOrcreate([
           'post_id' => $post->id,
           'user_id'=>auth('api')->id()
        ],[
            'comment'=>$request->comment
        ]);

        return $this->apiResponse(200,'Add Comment Successfully',null,$comment->load('post','user'));
    }
}
