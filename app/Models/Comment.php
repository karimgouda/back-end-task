<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['comment','user_id','post_id'];
    protected $hidden = ['created_at','updated_at','user_id','post_id'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function post()
    {
        return $this->belongsTo(Post::class,'post_id');
    }
}
