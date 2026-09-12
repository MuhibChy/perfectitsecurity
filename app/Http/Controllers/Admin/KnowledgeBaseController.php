<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KnowledgeBaseController extends Controller
{
    public function index()
    {
        $articles = KbArticle::with('category', 'author')->latest()->paginate(20);
        return view('admin.kb.index', compact('articles'));
    }

    public function create()
    {
        $categories = KbCategory::where('is_active', true)->get();
        $tags = \App\Models\KbTag::orderBy('name')->get();
        return view('admin.kb.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:kb_categories,id',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'visibility' => 'required|in:public,customer,employee,admin',
            'language' => 'nullable|string|max:5',
            'difficulty' => 'nullable|string|max:50',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:kb_tags,id',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $validated['author_id'] = auth()->id();
        $validated['slug'] = Str::slug($validated['title']);
        $validated['content'] = \App\Services\HtmlSanitizer::clean($validated['content']);
        $validated['is_published'] = $request->boolean('is_published');
        $validated['is_featured'] = $request->boolean('is_featured');

        $tags = $validated['tags'] ?? [];
        unset($validated['tags']);

        // Ensure unique slug.
        $base = $validated['slug'];
        $i = 1;
        while (KbArticle::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $base . '-' . (++$i);
        }

        $article = KbArticle::create($validated);
        $article->tags()->sync($tags);
        return redirect()->route('admin.knowledge-base.index')->with('success', 'Article created!');
    }

    public function edit(KbArticle $knowledgeBase)
    {
        $categories = KbCategory::where('is_active', true)->get();
        $tags = \App\Models\KbTag::orderBy('name')->get();
        $knowledgeBase->load('tags', 'versions');
        return view('admin.kb.edit', ['article' => $knowledgeBase, 'categories' => $categories, 'tags' => $tags]);
    }

    public function update(Request $request, KbArticle $knowledgeBase)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:kb_categories,id',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'visibility' => 'required|in:public,customer,employee,admin',
            'language' => 'nullable|string|max:5',
            'difficulty' => 'nullable|string|max:50',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:kb_tags,id',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $validated['content'] = \App\Services\HtmlSanitizer::clean($validated['content']);
        $validated['is_published'] = $request->boolean('is_published');
        $validated['is_featured'] = $request->boolean('is_featured');

        $tags = $validated['tags'] ?? null;
        unset($validated['tags']);

        $knowledgeBase->update($validated);
        if ($tags !== null) {
            $knowledgeBase->tags()->sync($tags);
        }
        $knowledgeBase->saveVersion($validated, auth()->id(), 'Admin edit');
        return redirect()->route('admin.knowledge-base.index')->with('success', 'Article updated!');
    }

    public function destroy(KbArticle $knowledgeBase)
    {
        $knowledgeBase->delete();
        return redirect()->route('admin.knowledge-base.index')->with('success', 'Article deleted.');
    }
}
