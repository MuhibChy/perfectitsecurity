<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\TrainingCenterLessons;
use App\Support\TrainingCenterProblems;
use Illuminate\Http\Request;

/**
 * HelpCenterController — website-based HTML Training Center and
 * Problem & Solution Center. Static curated pages (fast, readable,
 * printable) complementing the interactive Academy (courses/quizzes).
 * Staff-only: internal procedures stay behind auth + staff + mfa.
 */
class HelpCenterController extends Controller
{
    public function trainingIndex(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $category = (string) $request->input('category', 'all');
        $lessons = TrainingCenterLessons::all();
        if ($category !== 'all') {
            $lessons = array_values(array_filter($lessons, fn ($l) => $l['category'] === $category));
        }
        if ($q !== '') {
            $lessons = array_values(array_filter($lessons, fn ($l) =>
                stripos($l['title'] . ' ' . $l['summary'] . ' ' . $l['what'], $q) !== false));
        }
        return view('admin.help.training-index', [
            'lessons' => $lessons,
            'categories' => TrainingCenterLessons::categories(),
            'q' => $q, 'category' => $category,
            'allCount' => count(TrainingCenterLessons::all()),
        ]);
    }

    public function trainingShow(string $slug)
    {
        $lesson = TrainingCenterLessons::find($slug);
        abort_unless($lesson, 404);
        $ordered = TrainingCenterLessons::orderedSlugs();
        $pos = array_search($slug, $ordered);
        $prev = $pos > 0 ? TrainingCenterLessons::find($ordered[$pos - 1]) : null;
        $next = $pos < count($ordered) - 1 ? TrainingCenterLessons::find($ordered[$pos + 1]) : null;
        $relatedProblems = array_values(array_filter(array_map(
            fn ($s) => TrainingCenterProblems::find($s), $lesson['problems'] ?? []
        )));
        return view('admin.help.training-show', compact('lesson', 'prev', 'next', 'relatedProblems', 'pos', 'ordered'));
    }

    public function problemsIndex(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $filter = (string) $request->input('filter', 'all');
        $articles = TrainingCenterProblems::all();
        if ($filter !== 'all') {
            $articles = array_values(array_filter($articles, fn ($a) => $a['filter'] === $filter));
        }
        if ($q !== '') {
            $articles = array_values(array_filter($articles, fn ($a) =>
                stripos($a['title'] . ' ' . implode(' ', $a['symptoms']) . ' ' . $a['category'], $q) !== false));
        }
        $quick = ['cannot-login', 'payment-not-showing', 'order-missing', 'cannot-access-task', 'customer-update-not-visible'];
        $quickCards = array_values(array_filter(array_map(fn ($s) => TrainingCenterProblems::find($s), $quick)));
        return view('admin.help.problems-index', [
            'articles' => $articles,
            'filters' => TrainingCenterProblems::filters(),
            'categories' => TrainingCenterProblems::categories(),
            'q' => $q, 'filter' => $filter, 'quickCards' => $quickCards,
        ]);
    }

    public function problemShow(string $slug)
    {
        $article = TrainingCenterProblems::find($slug);
        abort_unless($article, 404);
        $relatedLessons = array_values(array_filter(array_map(
            fn ($s) => TrainingCenterLessons::find($s), $article['related'] ?? []
        )));
        return view('admin.help.problem-show', compact('article', 'relatedLessons'));
    }
}
