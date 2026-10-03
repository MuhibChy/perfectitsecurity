<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiSkillController extends Controller
{
    public function index()
    {
        $skills = AiSkill::orderBy('priority')->latest('id')->paginate(20);

        return view('admin.ai.skills-index', compact('skills'));
    }

    public function create()
    {
        return view('admin.ai.skills-form', ['skill' => new AiSkill(['status' => 'enabled', 'priority' => 100])]);
    }

    public function store(Request $request)
    {
        $v = $this->validated($request);
        $v['slug'] = Str::slug($v['slug'] ?? $v['name']);
        $v['created_by'] = auth()->id();
        AiSkill::create($v);

        return redirect()->route('admin.ai.skills.index')->with('success', 'Skill created.');
    }

    public function edit(AiSkill $skill)
    {
        return view('admin.ai.skills-form', compact('skill'));
    }

    public function update(Request $request, AiSkill $skill)
    {
        $v = $this->validated($request, $skill->id);
        $v['slug'] = Str::slug($v['slug'] ?? $v['name']);
        $v['updated_by'] = auth()->id();
        $v['version'] = $skill->version + 1;
        $skill->update($v);

        return redirect()->route('admin.ai.skills.index')->with('success', 'Skill updated.');
    }

    public function destroy(AiSkill $skill)
    {
        $skill->delete();

        return redirect()->route('admin.ai.skills.index')->with('success', 'Skill deleted.');
    }

    public function duplicate(AiSkill $skill)
    {
        $copy = $skill->replicate(['slug']);
        $copy->slug = $skill->slug.'-copy-'.Str::random(4);
        $copy->name = $skill->name.' (Copy)';
        $copy->status = 'disabled';
        $copy->version = 1;
        $copy->created_by = auth()->id();
        $copy->save();

        return redirect()->route('admin.ai.skills.edit', $copy)->with('success', 'Skill duplicated as disabled draft.');
    }

    public function toggle(AiSkill $skill)
    {
        $skill->update([
            'status' => $skill->status === 'enabled' ? 'disabled' : 'enabled',
            'updated_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Skill status updated.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $v = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:ai_skills,slug'.($ignoreId ? ",{$ignoreId}" : ''),
            'description' => 'nullable|string|max:2000',
            'system_instructions' => 'required|string|max:8000',
            'trigger_keywords' => 'nullable|string|max:2000',
            'allowed_roles' => 'nullable|array',
            'allowed_roles.*' => 'string|max:50',
            'priority' => 'required|integer|min:0|max:10000',
            'status' => 'required|in:enabled,disabled',
            'category' => 'nullable|string|max:100',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:64|max:8000',
        ]);
        // Comma-separated admin input → stored JSON array.
        $v['trigger_keywords'] = collect(preg_split('/[\r\n,]+/', $v['trigger_keywords'] ?? ''))
            ->map(fn ($k) => trim($k))->filter()->values()->all();
        $v['allowed_roles'] = array_values($v['allowed_roles'] ?? []);

        return $v;
    }
}
