<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\KbArticle;
use App\Models\KbArticleVote;
use App\Models\KbCategory;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request)
    {
        // Public surface: only published PUBLIC articles. Never leak
        // customer/employee/internal content via listing, search, or counts.
        $query = KbArticle::published()->public()->with('category', 'author');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('content', 'like', "%{$request->search}%");
            });
        }

        if ($request->category) {
            $query->where('category_id', $request->category);
        }

        $articles = $query->latest()->paginate(12);
        $categories = KbCategory::where('is_active', true)
            ->whereHas('articles', fn ($q) => $q->published()->public())
            ->withCount(['articles' => fn ($q) => $q->published()->public()])
            ->get();

        return view('public.kb.index', compact('articles', 'categories'));
    }

    public function show($slug)
    {
        $article = KbArticle::published()->public()->where('slug', $slug)->with('category', 'author')->firstOrFail();
        $article->incrementViewCount();

        $related = KbArticle::published()->public()
            ->where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->limit(5)
            ->get();

        return view('public.kb.show', compact('article', 'related'));
    }

    public function vote(Request $request, $slug)
    {
        $article = KbArticle::published()->public()->where('slug', $slug)->firstOrFail();

        $request->validate([
            'helpful' => 'required|boolean',
        ]);

        $userId = auth()->id();
        $sessionId = $request->cookie('kb_session') ?? session()->getId();

        // Check for existing vote
        $existingVote = KbArticleVote::where('article_id', $article->id)
            ->where(function ($q) use ($userId, $sessionId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('session_id', $sessionId);
                }
            })
            ->first();

        if ($existingVote) {
            // Update existing vote if it changed
            if ($existingVote->is_helpful !== $request->boolean('helpful')) {
                if ($existingVote->is_helpful) {
                    $article->decrement('helpful_count');
                    $article->increment('not_helpful_count');
                } else {
                    $article->decrement('not_helpful_count');
                    $article->increment('helpful_count');
                }
                $existingVote->update(['is_helpful' => $request->boolean('helpful')]);
            }
        } else {
            // Create new vote
            KbArticleVote::create([
                'article_id' => $article->id,
                'user_id' => $userId,
                'session_id' => $userId ? null : $sessionId,
                'is_helpful' => $request->boolean('helpful'),
            ]);

            if ($request->boolean('helpful')) {
                $article->markHelpful();
            } else {
                $article->markNotHelpful();
            }
        }

        return response()->json(['success' => true, 'message' => 'Thank you for your feedback!']);
    }
}
