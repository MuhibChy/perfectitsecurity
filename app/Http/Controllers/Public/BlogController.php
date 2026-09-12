<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogComment;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::published()->with('author', 'category', 'tags')->latest('published_at')->paginate(12);
        $categories = BlogCategory::withCount('posts')->get();

        return view('public.blog.index', compact('posts', 'categories'));
    }

    public function show($slug)
    {
        $post = BlogPost::published()->where('slug', $slug)->with('author', 'category', 'tags')->firstOrFail();
        $post->increment('views_count');

        $related = BlogPost::published()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->limit(3)
            ->get();

        return view('public.blog.show', compact('post', 'related'));
    }

    public function comment(Request $request, $slug)
    {
        $post = BlogPost::published()->where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required_without:auth|string|max:255',
            'email' => 'required_without:auth|email',
            'comment' => 'required|string|max:2000',
        ]);

        BlogComment::create([
            'post_id' => $post->id,
            'user_id' => auth()->id(),
            'name' => $validated['name'] ?? auth()->user()->name ?? null,
            'email' => $validated['email'] ?? auth()->user()->email ?? null,
            'comment' => $validated['comment'],
            'is_approved' => false,
        ]);

        return redirect()->back()->with('success', 'Comment submitted for moderation.');
    }
}
