<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    protected $fillable = ['title','content','author_id','category'];
    protected $hidden = ['author_id','updated_at','created_at'];

    public function author()
    {
        return $this->belongsTo(User::class ,'author_id');
    }

    public function scopeForUser($query, $user)
    {
        if ($user->role === 'admin') {
            return $query->with('author');
        } elseif ($user->role === 'author') {
            return $query->where('author_id', $user->id);
        }
    }

}
