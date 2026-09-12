<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use App\Models\CareerPost;
use App\Models\PortfolioItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    // ---- Case studies ----
    public function caseStudies()
    {
        $items = CaseStudy::latest()->paginate(20);
        return view('admin.content.case-studies', compact('items'));
    }

    public function storeCaseStudy(Request $request)
    {
        $data = $this->validateContent($request, true);
        $data['slug'] = Str::slug($data['title']) . '-' . Str::random(4);
        if (!empty($data['is_published'])) {
            $data['published_at'] = now();
        }
        CaseStudy::create($data);
        return back()->with('success', 'Case study saved.');
    }

    public function updateCaseStudy(Request $request, CaseStudy $caseStudy)
    {
        $data = $this->validateContent($request, true);
        if (!empty($data['is_published']) && !$caseStudy->published_at) {
            $data['published_at'] = now();
        }
        $caseStudy->update($data);
        return back()->with('success', 'Case study updated.');
    }

    public function destroyCaseStudy(CaseStudy $caseStudy)
    {
        $caseStudy->delete();
        return back()->with('success', 'Case study deleted.');
    }

    // ---- Careers ----
    public function careers()
    {
        $items = CareerPost::latest()->paginate(20);
        return view('admin.content.careers', compact('items'));
    }

    public function storeCareer(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'department' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'employment_type' => 'nullable|string|max:50',
            'summary' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'is_published' => 'nullable|boolean',
        ]);
        $data['is_published'] = $request->boolean('is_published');
        $data['slug'] = Str::slug($data['title']) . '-' . Str::random(4);
        if ($data['is_published']) {
            $data['published_at'] = now();
        }
        CareerPost::create($data);
        return back()->with('success', 'Career post saved.');
    }

    public function updateCareer(Request $request, CareerPost $career)
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'department' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'employment_type' => 'nullable|string|max:50',
            'summary' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'is_published' => 'nullable|boolean',
        ]);
        if ($request->has('is_published')) {
            $data['is_published'] = $request->boolean('is_published');
            if ($data['is_published'] && !$career->published_at) {
                $data['published_at'] = now();
            }
        }
        $career->update($data);
        return back()->with('success', 'Career post updated.');
    }

    public function destroyCareer(CareerPost $career)
    {
        $career->delete();
        return back()->with('success', 'Career post deleted.');
    }

    // ---- Portfolio ----
    public function portfolio()
    {
        $items = PortfolioItem::latest()->paginate(20);
        return view('admin.content.portfolio', compact('items'));
    }

    public function storePortfolio(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'client_name' => 'nullable|string|max:255',
            'summary' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'project_url' => 'nullable|url|max:255',
            'is_published' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);
        $data['is_published'] = $request->boolean('is_published');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['slug'] = Str::slug($data['title']) . '-' . Str::random(4);
        if ($data['is_published']) {
            $data['published_at'] = now();
        }
        PortfolioItem::create($data);
        return back()->with('success', 'Portfolio item saved.');
    }

    public function updatePortfolio(Request $request, PortfolioItem $portfolio)
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'category' => 'nullable|string|max:100',
            'client_name' => 'nullable|string|max:255',
            'summary' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'project_url' => 'nullable|url|max:255',
            'is_published' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);
        if ($request->has('is_published')) {
            $data['is_published'] = $request->boolean('is_published');
        }
        if ($request->has('is_featured')) {
            $data['is_featured'] = $request->boolean('is_featured');
        }
        $portfolio->update($data);
        return back()->with('success', 'Portfolio item updated.');
    }

    public function destroyPortfolio(PortfolioItem $portfolio)
    {
        $portfolio->delete();
        return back()->with('success', 'Portfolio item deleted.');
    }

    protected function validateContent(Request $request, bool $caseStudy = false): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'summary' => 'nullable|string|max:500',
            'challenge' => 'nullable|string',
            'solution' => 'nullable|string',
            'results' => 'nullable|string',
            'is_published' => 'nullable|boolean',
        ]);
        $data['is_published'] = $request->boolean('is_published');
        return $data;
    }
}
