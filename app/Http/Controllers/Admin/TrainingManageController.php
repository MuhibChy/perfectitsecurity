<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingAssessmentSubmission;
use App\Models\TrainingAssignment;
use App\Models\TrainingCertificate;
use App\Models\TrainingCourse;
use App\Models\TrainingLesson;
use App\Models\TrainingLessonProgress;
use App\Models\TrainingModule;
use App\Models\TrainingQuestion;
use App\Models\TrainingQuiz;
use App\Models\TrainingQuizAttempt;
use App\Models\TrainingTrainerNote;
use App\Models\User;
use App\Services\TrainingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * TrainingManageController — trainer / training-manager administration.
 * Guarded by requires.role:isTrainingManager (admins inherit).
 */
class TrainingManageController extends Controller
{
    public function dashboard()
    {
        $staff = User::staff()->active()->get();
        $totalEmployees = $staff->count();
        // Bounded for memory: dashboard aggregates are meaningful within the
        // most recent 2000 assignments (admin-only screen).
        $assignments = TrainingAssignment::with('course', 'user')->latest()->take(2000)->get();
        $inTraining = $assignments->whereNotIn('status', ['completed', 'passed'])->count();
        $completed = $assignments->whereIn('status', ['completed', 'passed'])->count();
        $overdue = $assignments->filter->isOverdue()->count();
        $avgProgress = 0;
        if ($assignments->count()) {
            $sum = 0;
            foreach ($assignments as $a) {
                $sum += TrainingService::courseProgress($a->course, $a->user_id)['percent'];
            }
            $avgProgress = (int) round($sum / $assignments->count());
        }
        $failedAttempts = TrainingQuizAttempt::with('quiz', 'user')->where('passed', false)->latest()->take(8)->get();
        $recentCertificates = TrainingCertificate::with('course', 'user')->latest()->take(8)->get();
        $pendingSubmissions = TrainingAssessmentSubmission::with('assessment.course', 'user')->where('status', 'submitted')->latest()->take(8)->get();
        $byRole = $assignments->groupBy(fn ($a) => $a->user->role ?? 'unknown')->map->count();
        $courses = TrainingCourse::withCount('assignments')->orderBy('title')->get();

        return view('admin.training.dashboard', compact('totalEmployees', 'inTraining', 'completed', 'overdue', 'avgProgress', 'failedAttempts', 'recentCertificates', 'pendingSubmissions', 'byRole', 'courses'));
    }

    // ── Courses ────────────────────────────────────────────────
    public function courses()
    {
        $courses = TrainingCourse::withCount(['modules', 'assignments'])->orderBy('title')->paginate(20);

        return view('admin.training.courses', compact('courses'));
    }

    public function createCourse()
    {
        return view('admin.training.course-form', ['course' => new TrainingCourse()]);
    }

    public function storeCourse(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'role_target' => 'nullable|string|max:100',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'duration_minutes' => 'required|integer|min:5|max:10000',
            'is_mandatory' => 'boolean', 'is_published' => 'boolean',
            'version' => 'required|string|max:20',
        ]);
        $data['slug'] = Str::slug($data['title']).'-'.Str::lower(Str::random(4));
        $data['created_by'] = auth()->id();
        $course = TrainingCourse::create($data);

        return redirect()->route('admin.training.courses.show', $course)->with('success', 'Course created.');
    }

    public function showCourse(TrainingCourse $course)
    {
        $course->load(['modules.lessons', 'quizzes.questions', 'practicals', 'assignments.user']);

        return view('admin.training.course-show', compact('course'));
    }

    public function editCourse(TrainingCourse $course)
    {
        return view('admin.training.course-form', compact('course'));
    }

    public function updateCourse(Request $request, TrainingCourse $course)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'role_target' => 'nullable|string|max:100',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'duration_minutes' => 'required|integer|min:5|max:10000',
            'is_mandatory' => 'boolean', 'is_published' => 'boolean',
            'version' => 'required|string|max:20',
        ]);
        $course->update($data);

        return redirect()->route('admin.training.courses.show', $course)->with('success', 'Course updated to version '.$course->version.'.');
    }

    // ── Modules & lessons ──────────────────────────────────────
    public function storeModule(Request $request, TrainingCourse $course)
    {
        $data = $request->validate(['title' => 'required|string|max:255', 'description' => 'nullable|string']);
        $data['sort_order'] = $course->modules()->count();
        $course->modules()->create($data);

        return back()->with('success', 'Module added.');
    }

    public function destroyModule(TrainingModule $module)
    {
        $course = $module->course;
        $module->delete();

        return redirect()->route('admin.training.courses.show', $course)->with('success', 'Module deleted.');
    }

    public function createLesson(TrainingCourse $course)
    {
        $course->load('modules');

        return view('admin.training.lesson-form', ['course' => $course, 'lesson' => new TrainingLesson()]);
    }

    public function storeLesson(Request $request, TrainingCourse $course)
    {
        $data = $this->lessonData($request);
        $lesson = TrainingLesson::create($data + ['sort_order' => TrainingLesson::where('module_id', $data['module_id'])->count()]);

        return redirect()->route('admin.training.courses.show', $course)->with('success', 'Lesson created.');
    }

    public function editLesson(TrainingLesson $lesson)
    {
        $lesson->load('module.course.modules');

        return view('admin.training.lesson-form', ['course' => $lesson->module->course, 'lesson' => $lesson]);
    }

    public function updateLesson(Request $request, TrainingLesson $lesson)
    {
        $lesson->update($this->lessonData($request));

        return redirect()->route('admin.training.courses.show', $lesson->module->course)->with('success', 'Lesson updated.');
    }

    protected function lessonData(Request $request): array
    {
        $data = $request->validate([
            'module_id' => 'required|exists:training_modules,id',
            'title' => 'required|string|max:255',
            'lesson_type' => 'required|in:guide,procedure,scenario,simulation,reference',
            'body' => 'required|string',
            'objectives' => 'nullable|string', 'steps' => 'nullable|string',
            'why_matters' => 'nullable|string', 'common_mistakes' => 'nullable|string',
            'discussion_questions' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1|max:1000',
            'is_published' => 'boolean', 'version' => 'required|string|max:20',
        ]);
        foreach (['objectives', 'steps', 'why_matters', 'common_mistakes', 'discussion_questions'] as $f) {
            $data[$f] = $data[$f] ? array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $data[$f])))) : null;
        }

        return $data;
    }

    // ── Quizzes ────────────────────────────────────────────────
    public function storeQuiz(Request $request, TrainingCourse $course)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255', 'description' => 'nullable|string',
            'lesson_id' => 'nullable|exists:training_lessons,id',
            'pass_score' => 'required|integer|min:1|max:100', 'max_attempts' => 'required|integer|min:1|max:20',
            'is_published' => 'boolean',
        ]);
        $course->quizzes()->create($data);

        return back()->with('success', 'Quiz created. Add questions below.');
    }

    public function storeQuestion(Request $request, TrainingQuiz $quiz)
    {
        $data = $request->validate([
            'type' => 'required|in:single,multiple,boolean,ordering',
            'prompt' => 'required|string',
            'options' => 'required|string', 'correct' => 'required|string',
            'explanation' => 'nullable|string', 'points' => 'required|integer|min:1|max:10',
        ]);
        $quiz->questions()->create([
            'type' => $data['type'], 'prompt' => $data['prompt'],
            'options' => array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $data['options'])))),
            'correct' => array_values(array_filter(array_map('trim', preg_split('/,/', $data['correct'])))),
            'explanation' => $data['explanation'] ?? null, 'points' => $data['points'],
            'sort_order' => $quiz->questions()->count(),
        ]);

        return back()->with('success', 'Question added. For correct: option indexes (0-based), comma-separated for multiple.');
    }

    public function destroyQuestion(TrainingQuestion $question)
    {
        $question->delete();

        return back()->with('success', 'Question deleted.');
    }

    // ── Practicals ─────────────────────────────────────────────
    public function storePractical(Request $request, TrainingCourse $course)
    {
        $data = $request->validate(['title' => 'required|string|max:255', 'instructions' => 'required|string', 'checklist' => 'nullable|string', 'is_published' => 'boolean']);
        $course->practicals()->create([
            'title' => $data['title'], 'instructions' => $data['instructions'],
            'checklist' => $data['checklist'] ? array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $data['checklist'])))) : null,
            'is_published' => $data['is_published'] ?? false,
        ]);

        return back()->with('success', 'Practical assessment created.');
    }

    // ── Assignments ────────────────────────────────────────────
    public function assign(Request $request, TrainingCourse $course)
    {
        $request->merge(['user_ids' => is_string($request->input('user_ids'))
            ? array_filter(array_map('trim', explode(',', $request->input('user_ids'))))
            : $request->input('user_ids')]);
        $data = $request->validate([
            'user_ids' => 'nullable|array', 'user_ids.*' => 'exists:users,id',
            'role' => 'nullable|string|max:50', 'due_at' => 'nullable|date|after:today',
        ]);
        $targets = collect($data['user_ids'] ?? []);
        if (! empty($data['role'])) {
            $targets = $targets->merge(User::where('role', $data['role'])->pluck('id'));
        }
        $count = 0;
        foreach ($targets->unique() as $uid) {
            $user = User::find($uid);
            if (! $user || ! $user->isStaff()) {
                continue;
            }
            $assignment = TrainingAssignment::firstOrCreate(
                ['course_id' => $course->id, 'user_id' => $uid],
                ['assigned_by' => auth()->id(), 'due_at' => $data['due_at'] ?? null, 'status' => 'assigned']
            );
            if ($assignment->wasRecentlyCreated) {
                $count++;
                TrainingService::notify($uid, 'training_assigned', 'Training assigned', "You were assigned '{$course->title}'.");
            }
        }

        return back()->with('success', "Assigned to {$count} employee(s).");
    }

    public function resetProgress(TrainingAssignment $assignment)
    {
        $course = $assignment->course;
        TrainingLessonProgress::where('user_id', $assignment->user_id)->whereIn('lesson_id', $course->lessonIds())->delete();
        TrainingQuizAttempt::where('user_id', $assignment->user_id)->whereIn('quiz_id', $course->quizzes()->pluck('id'))->delete();
        TrainingAssessmentSubmission::where('user_id', $assignment->user_id)->whereIn('assessment_id', $course->practicals()->pluck('id'))->delete();
        TrainingCertificate::where('course_id', $course->id)->where('user_id', $assignment->user_id)->delete();
        $assignment->update(['status' => 'assigned']);

        return back()->with('success', 'Employee training reset.');
    }

    // ── Review ─────────────────────────────────────────────────
    public function submissions()
    {
        $submissions = TrainingAssessmentSubmission::with('assessment.course', 'user')->latest()->paginate(20);

        return view('admin.training.submissions', compact('submissions'));
    }

    public function reviewSubmission(Request $request, TrainingAssessmentSubmission $submission)
    {
        $data = $request->validate(['status' => 'required|in:passed,needs_improvement', 'score' => 'nullable|integer|min:0|max:100', 'feedback' => 'required|string|min:5']);
        $submission->update($data + ['reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        $course = $submission->assessment->course;
        TrainingService::refreshAssignment($course, $submission->user_id);
        TrainingService::notify($submission->user_id, 'assessment_reviewed', 'Practical assessment reviewed', "Your submission for '{$submission->assessment->title}' was marked {$data['status']}.");

        return back()->with('success', 'Review recorded.');
    }

    public function employee(User $user)
    {
        abort_unless($user->isStaff(), 404);
        $assignments = TrainingAssignment::with('course')->where('user_id', $user->id)->get();
        foreach ($assignments as $a) {
            $a->progress = TrainingService::courseProgress($a->course, $user->id);
        }
        $notes = TrainingTrainerNote::with('author')->where('user_id', $user->id)->latest()->get();
        $attempts = TrainingQuizAttempt::with('quiz.course')->where('user_id', $user->id)->latest()->take(15)->get();
        $certificates = TrainingCertificate::with('course')->where('user_id', $user->id)->get();

        return view('admin.training.employee', compact('user', 'assignments', 'notes', 'attempts', 'certificates'));
    }

    public function storeNote(Request $request, User $user)
    {
        $data = $request->validate(['note' => 'required|string|min:3|max:5000']);
        TrainingTrainerNote::create(['user_id' => $user->id, 'author_id' => auth()->id(), 'note' => $data['note']]);
        TrainingService::notify($user->id, 'trainer_note', 'Trainer message', 'Your trainer left feedback on your training record.');

        return back()->with('success', 'Note added.');
    }

    /** Revoke a certificate (audited; row preserved, verifies as not valid). */
    public function revokeCertificate(Request $request, TrainingCertificate $certificate)
    {
        $data = $request->validate(['reason' => 'required|string|max:1000']);
        abort_unless($certificate->status === 'active', 422, 'Only active certificates can be revoked.');
        $certificate->update([
            'status' => 'revoked', 'revoked_at' => now(),
            'revoked_by' => auth()->id(), 'revoke_reason' => $data['reason'],
        ]);
        \App\Models\AuditLog::log('certificate.revoked', 'training', $certificate, "Certificate {$certificate->certificate_no} revoked by ".auth()->user()->name.": {$data['reason']}");
        TrainingService::notify($certificate->user_id, 'certificate_revoked', 'Certificate revoked', "Your certificate {$certificate->certificate_no} was revoked: {$data['reason']}");

        return back()->with('success', 'Certificate revoked.');
    }

    public function reports()
    {
        $courses = TrainingCourse::with(['assignments.user', 'quizzes.attempts'])->get();
        $rows = [];
        foreach ($courses as $course) {
            $attempts = $course->quizzes->flatMap->attempts;
            $rows[] = [
                'course' => $course,
                'assigned' => $course->assignments->count(),
                'completed' => $course->assignments->whereIn('status', ['completed', 'passed'])->count(),
                'avg_score' => $attempts->count() ? (int) round($attempts->avg(fn ($a) => $a->percent())) : null,
                'failed' => $attempts->where('passed', false)->count(),
                'overdue' => $course->assignments->filter->isOverdue()->count(),
            ];
        }

        return view('admin.training.reports', compact('rows'));
    }

    public function staffList()
    {
        $staff = User::staff()->active()->orderBy('name')->get();
        $courses = TrainingCourse::published()->orderBy('title')->get();

        return view('admin.training.assign', compact('staff', 'courses'));
    }
}
