<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingAssessmentSubmission;
use App\Models\TrainingAssignment;
use App\Models\TrainingCertificate;
use App\Models\TrainingCourse;
use App\Models\TrainingLesson;
use App\Models\TrainingLessonProgress;
use App\Models\TrainingPracticalAssessment;
use App\Models\TrainingQuiz;
use App\Models\TrainingQuizAttempt;
use App\Models\TrainingTrainerNote;
use App\Services\TrainingService;
use Illuminate\Http\Request;

/**
 * LearningController — employee learning experience (all staff).
 * Completion status always derives from records; employees can mark
 * lessons read but can never self-issue completion.
 */
class LearningController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $paths = TrainingService::learningPaths()[$user->role] ?? TrainingService::learningPaths()['employee'];
        $required = TrainingCourse::published()->whereIn('slug', $paths)->orderBy('title')->get();
        $featured = TrainingCourse::published()->whereNotIn('slug', $paths)->orderBy('title')->take(6)->get();
        $myAssignments = TrainingAssignment::with('course')->where('user_id', $user->id)->latest()->take(5)->get();
        $certificates = TrainingCertificate::with('course')->where('user_id', $user->id)->latest()->take(5)->get();
        foreach ($myAssignments as $a) {
            $a->progress = TrainingService::courseProgress($a->course, $user->id);
        }

        return view('admin.academy.index', compact('required', 'featured', 'myAssignments', 'certificates', 'paths'));
    }

    public function my()
    {
        $user = auth()->user();
        $assignments = TrainingAssignment::with('course')->where('user_id', $user->id)->latest()->get();
        foreach ($assignments as $a) {
            $a->progress = TrainingService::courseProgress($a->course, $user->id);
        }
        $certificates = TrainingCertificate::with('course')->where('user_id', $user->id)->latest()->get();
        $notes = TrainingTrainerNote::with('author')->where('user_id', $user->id)->latest()->take(10)->get();

        return view('admin.academy.my', compact('assignments', 'certificates', 'notes'));
    }

    public function course(TrainingCourse $course)
    {
        abort_unless($course->is_published, 404);
        $userId = auth()->id();
        $course->load(['modules.lessons' => fn ($q) => $q->published()->orderBy('sort_order'), 'quizzes' => fn ($q) => $q->published(), 'practicals' => fn ($q) => $q->where('is_published', true)]);
        $completedLessons = TrainingLessonProgress::where('user_id', $userId)->where('is_completed', true)->pluck('lesson_id')->all();
        $quizBest = [];
        foreach ($course->quizzes as $quiz) {
            $quizBest[$quiz->id] = $quiz->bestAttemptFor($userId);
        }
        $progress = TrainingService::courseProgress($course, $userId);
        $assignment = TrainingAssignment::where('course_id', $course->id)->where('user_id', $userId)->first();

        return view('admin.academy.course', compact('course', 'completedLessons', 'quizBest', 'progress', 'assignment'));
    }

    public function lesson(TrainingLesson $lesson)
    {
        abort_unless($lesson->is_published, 404);
        $lesson->load('module.course');
        $userId = auth()->id();
        $completed = $lesson->isCompletedBy($userId);
        // Prev/next within course for presentation mode.
        $ids = $lesson->module->course->lessons()->published()->orderBy('training_modules.sort_order')->orderBy('training_lessons.sort_order')->pluck('training_lessons.id')->all();
        $pos = array_search($lesson->id, $ids);
        $prev = $pos > 0 ? TrainingLesson::find($ids[$pos - 1]) : null;
        $next = ($pos !== false && $pos < count($ids) - 1) ? TrainingLesson::find($ids[$pos + 1]) : null;
        $quizzes = TrainingQuiz::published()->where('lesson_id', $lesson->id)->with('questions')->get();

        return view('admin.academy.lesson', compact('lesson', 'completed', 'prev', 'next', 'quizzes', 'pos', 'ids'));
    }

    public function completeLesson(Request $request, TrainingLesson $lesson)
    {
        abort_unless($lesson->is_published, 404);
        $userId = auth()->id();
        TrainingLessonProgress::updateOrCreate(
            ['lesson_id' => $lesson->id, 'user_id' => $userId],
            ['is_completed' => true, 'completed_at' => now()]
        );
        $course = $lesson->module->course;
        TrainingService::refreshAssignment($course, $userId);

        return back()->with('success', 'Lesson marked as studied.');
    }

    public function quiz(TrainingQuiz $quiz)
    {
        abort_unless($quiz->is_published, 404);
        $quiz->load('questions', 'course');
        $userId = auth()->id();
        if ($quiz->max_attempts > 0 && $quiz->attemptsUsedBy($userId) >= $quiz->max_attempts && ! $quiz->bestAttemptFor($userId)?->passed) {
            return redirect()->route('admin.academy.course', $quiz->course)->with('error', 'You have used all attempts for this quiz. Ask your trainer for a reset.');
        }

        return view('admin.academy.quiz', compact('quiz'));
    }

    public function submitQuiz(Request $request, TrainingQuiz $quiz)
    {
        abort_unless($quiz->is_published, 404);
        $userId = auth()->id();
        if ($quiz->max_attempts > 0 && $quiz->attemptsUsedBy($userId) >= $quiz->max_attempts) {
            return redirect()->route('admin.academy.course', $quiz->course)->with('error', 'No attempts remaining.');
        }
        $quiz->load('questions', 'course');
        $score = 0;
        $max = 0;
        $answers = [];
        foreach ($quiz->questions as $q) {
            $max += $q->points;
            $given = $request->input('q_'.$q->id);
            $answers[$q->id] = $given;
            $score += $q->grade($given);
        }
        $percent = $max > 0 ? (int) round($score * 100 / $max) : 0;
        $attempt = TrainingQuizAttempt::create([
            'quiz_id' => $quiz->id, 'user_id' => $userId,
            'score' => $score, 'max_score' => $max,
            'passed' => $percent >= $quiz->pass_score, 'answers' => $answers,
        ]);
        TrainingService::refreshAssignment($quiz->course, $userId);
        TrainingService::notify($userId, 'quiz_result', $attempt->passed ? 'Assessment passed' : 'Assessment needs improvement', "Quiz '{$quiz->title}': {$percent}% (pass mark {$quiz->pass_score}%).");

        return redirect()->route('admin.academy.quiz.result', $attempt)->with('success', $attempt->passed ? "Passed with {$percent}%." : "Scored {$percent}%. Review the material and retry.");
    }

    public function quizResult(TrainingQuizAttempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        $attempt->load('quiz.questions', 'quiz.course');

        return view('admin.academy.quiz-result', compact('attempt'));
    }

    public function practical(TrainingPracticalAssessment $practical)
    {
        abort_unless($practical->is_published, 404);
        $practical->load('course');
        $submission = $practical->submissions()->where('user_id', auth()->id())->latest()->first();

        return view('admin.academy.practical', compact('practical', 'submission'));
    }

    public function submitPractical(Request $request, TrainingPracticalAssessment $practical)
    {
        abort_unless($practical->is_published, 404);
        $practical->load('course');
        $rules = [];
        foreach (($practical->checklist ?? []) as $i => $item) {
            $rules['responses.'.$i] = 'required|string|min:10|max:5000';
        }
        $data = $request->validate($rules ?: ['responses' => 'required|array']);
        TrainingAssessmentSubmission::create([
            'assessment_id' => $practical->id, 'user_id' => auth()->id(),
            'responses' => $data['responses'] ?? [], 'status' => 'submitted', 'submitted_at' => now(),
        ]);
        $course = $practical->course;
        $assignment = TrainingAssignment::where('course_id', $course->id)->where('user_id', auth()->id())->first();
        if ($assignment && $assignment->status !== 'completed') {
            $assignment->update(['status' => 'assessment_pending']);
        }

        return redirect()->route('admin.academy.course', $course)->with('success', 'Practical exercise submitted for trainer review.');
    }

    public function certificates()
    {
        $certificates = TrainingCertificate::with('course')->where('user_id', auth()->id())->latest()->get();

        return view('admin.academy.certificates', compact('certificates'));
    }

    public function certificate(TrainingCertificate $certificate)
    {
        abort_unless((int) $certificate->user_id === (int) auth()->id() || auth()->user()->isTrainer(), 403);
        $certificate->load('course', 'user');

        return view('admin.academy.certificate', compact('certificate'));
    }
}
