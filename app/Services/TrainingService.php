<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\TrainingAssignment;
use App\Models\TrainingCertificate;
use App\Models\TrainingCourse;
use App\Models\TrainingLessonProgress;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * TrainingService — Academy progress rules (visual/domain helper only;
 * no business-logic changes to orders, payments or finance).
 *
 * Completion rule: ALL published lessons completed + every published quiz
 * passed + every published practical passed (or none exist) => certificate.
 * Employees can never self-complete; status derives from records.
 */
class TrainingService
{
    public static function courseProgress(TrainingCourse $course, int $userId): array
    {
        $lessonIds = $course->lessonIds();
        $total = count($lessonIds);
        $done = $total ? TrainingLessonProgress::where('user_id', $userId)
            ->whereIn('lesson_id', $lessonIds)->where('is_completed', true)->count() : 0;

        $quizzes = $course->quizzes()->published()->with('questions')->get();
        $quizzesPassed = 0;
        foreach ($quizzes as $quiz) {
            $best = $quiz->bestAttemptFor($userId);
            if ($best && $best->passed) $quizzesPassed++;
        }

        $practicals = $course->practicals()->where('is_published', true)->get();
        $practicalsPassed = 0;
        foreach ($practicals as $p) {
            if ($p->submissions()->where('user_id', $userId)->where('status', 'passed')->exists()) {
                $practicalsPassed++;
            }
        }

        $percent = $total ? (int) round($done * 100 / $total) : 100;
        $complete = $total > 0 && $done >= $total
            && $quizzesPassed >= $quizzes->count()
            && $practicalsPassed >= $practicals->count();

        return [
            'lessons_done' => $done,
            'lessons_total' => $total,
            'percent' => $percent,
            'quizzes_passed' => $quizzesPassed,
            'quizzes_total' => $quizzes->count(),
            'practicals_passed' => $practicalsPassed,
            'practicals_total' => $practicals->count(),
            'complete' => $complete,
        ];
    }

    public static function refreshAssignment(TrainingCourse $course, int $userId): ?TrainingAssignment
    {
        $assignment = TrainingAssignment::where('course_id', $course->id)->where('user_id', $userId)->first();
        if (!$assignment) return null;

        $progress = self::courseProgress($course, $userId);
        if ($progress['complete']) {
            if (!in_array($assignment->status, ['completed'], true)) {
                $assignment->update(['status' => 'completed']);
                self::issueCertificate($course, $userId);
                self::notify($userId, 'training_completed', 'Training completed', "You completed '{$course->title}'. Your completion record is ready.");
            }
        } elseif ($progress['lessons_done'] > 0 || $progress['quizzes_passed'] > 0) {
            if ($assignment->status === 'assigned') $assignment->update(['status' => 'in_progress']);
        }

        return $assignment->fresh();
    }

    public static function issueCertificate(TrainingCourse $course, int $userId): TrainingCertificate
    {
        $existing = TrainingCertificate::where('course_id', $course->id)->where('user_id', $userId)->first();
        if ($existing) return $existing;

        $best = $course->quizzes()->published()->get()
            ->map(fn ($q) => $q->bestAttemptFor($userId))
            ->filter()->map->percent()->avg();

        return TrainingCertificate::create([
            'course_id' => $course->id,
            'user_id' => $userId,
            'score' => $best !== null ? (int) round($best) : null,
            'certificate_no' => 'PTA-' . date('Y') . '-' . strtoupper(Str::random(8)),
            'course_version' => $course->version,
            'completed_at' => now(),
        ]);
    }

    /**
     * Preference-respecting notifier (Phase 9): delegates to the central
     * ServiceTrackingService::notify() so NotificationPreference rows are
     * honoured. Best-effort — training records remain authoritative.
     */
    public static function notify(int $userId, string $type, string $title, string $message): void
    {
        try {
            ServiceTrackingService::notify($userId, $type, $title, $message);
        } catch (\Throwable $e) {
            // Notifications are best-effort; training records are authoritative.
        }
    }

    /** Role learning paths: which course slugs each staff role should take. */
    public static function learningPaths(): array
    {
        return [
            'sales_agent' => ['platform-introduction', 'user-roles-and-rbac', 'customer-journey', 'lead-and-crm', 'quotations-and-proposals', 'orders-and-payments', 'customer-communication', 'daily-workflow-sales'],
            'support_agent' => ['platform-introduction', 'user-roles-and-rbac', 'tickets-and-sla', 'customer-communication', 'progress-updates-and-notes', 'it-service-delivery', 'security-and-data-protection', 'daily-workflow-support'],
            'support_manager' => ['platform-introduction', 'user-roles-and-rbac', 'tickets-and-sla', 'customer-communication', 'progress-updates-and-notes', 'escalation-playbook', 'security-and-data-protection'],
            'project_manager' => ['platform-introduction', 'user-roles-and-rbac', 'projects-and-tasks', 'progress-updates-and-notes', 'customer-communication', 'account-closure', 'full-lifecycle-simulation'],
            'employee' => ['platform-introduction', 'user-roles-and-rbac', 'projects-and-tasks', 'tickets-and-sla', 'customer-communication', 'daily-workflow-support'],
            'finance_manager' => ['platform-introduction', 'user-roles-and-rbac', 'orders-and-payments', 'finance-academy', 'invoices-and-receipts', 'account-closure'],
            'training_manager' => ['platform-introduction', 'user-roles-and-rbac', 'trainer-playbook'],
            'super_admin' => ['platform-introduction', 'user-roles-and-rbac', 'trainer-playbook', 'website-content-and-kb'],
            'admin' => ['platform-introduction', 'user-roles-and-rbac', 'trainer-playbook', 'website-content-and-kb'],
        ];
    }
}
