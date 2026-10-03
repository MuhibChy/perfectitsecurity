<?php

namespace Tests\Feature;

use App\Models\TrainingAssessmentSubmission;
use App\Models\TrainingAssignment;
use App\Models\TrainingCertificate;
use App\Models\TrainingCourse;
use App\Models\TrainingLesson;
use App\Models\TrainingModule;
use App\Models\TrainingPracticalAssessment;
use App\Models\TrainingQuestion;
use App\Models\TrainingQuiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingAcademyTest extends TestCase
{
    use RefreshDatabase;

    protected function seedAcademy(): TrainingCourse
    {
        $this->seed(\Database\Seeders\TrainingAcademySeeder::class);
        return TrainingCourse::where('slug', 'platform-introduction')->firstOrFail();
    }

    protected function staff(string $role = 'employee'): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }

    /** @test */
    public function curriculum_seeds_with_synthetic_markers()
    {
        $this->seed(\Database\Seeders\TrainingAcademySeeder::class);
        $this->assertGreaterThanOrEqual(15, TrainingCourse::count());
        $this->assertGreaterThanOrEqual(20, TrainingLesson::count());
        $this->assertGreaterThanOrEqual(20, TrainingQuestion::count());
        $this->assertGreaterThanOrEqual(3, TrainingPracticalAssessment::count());
        // Synthetic markers present, no real-customer leakage pattern.
        $bodies = TrainingLesson::pluck('body')->join("\n");
        $this->assertStringContainsString('[TRAINING]', $bodies);
    }

    /** @test */
    public function customers_cannot_access_academy()
    {
        $this->seedAcademy();
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $this->actingAs($customer)->get(route('admin.academy.index'))->assertStatus(403);
    }

    /** @test */
    public function employees_learn_but_cannot_manage()
    {
        $this->seedAcademy();
        $employee = $this->staff('employee');
        $this->actingAs($employee)->get(route('admin.academy.index'))->assertStatus(200);
        $this->actingAs($employee)->get(route('admin.training.dashboard'))->assertStatus(403);
    }

    /** @test */
    public function trainer_manages_and_employee_progresses_to_certificate()
    {
        $course = $this->seedAcademy();
        $trainer = $this->staff('admin');
        $employee = $this->staff('employee');

        // Assign.
        $this->actingAs($trainer)->post(route('admin.training.assign', $course), ['user_ids' => [$employee->id]])
            ->assertSessionHas('success');
        $this->assertDatabaseHas('training_assignments', ['course_id' => $course->id, 'user_id' => $employee->id]);

        // Complete all lessons.
        foreach ($course->lessonIds() as $lessonId) {
            $lesson = TrainingLesson::find($lessonId);
            $this->actingAs($employee)->post(route('admin.academy.lesson.complete', $lesson))->assertSessionHas('success');
        }

        // Pass all quizzes with correct answers.
        foreach ($course->quizzes()->published()->with('questions')->get() as $quiz) {
            $payload = [];
            foreach ($quiz->questions as $q) {
                $payload['q_' . $q->id] = $q->type === 'multiple' ? $q->correct : ($q->type === 'ordering' ? implode(',', $q->correct) : $q->correct[0]);
            }
            $this->actingAs($employee)->post(route('admin.academy.quiz.submit', $quiz), $payload)->assertSessionHas('success');
        }

        // No practicals in this course → certificate issued, assignment completed.
        $this->assertDatabaseHas('training_assignments', ['course_id' => $course->id, 'user_id' => $employee->id, 'status' => 'completed']);
        $cert = TrainingCertificate::where('course_id', $course->id)->where('user_id', $employee->id)->first();
        $this->assertNotNull($cert);
        $this->assertStringStartsWith('PTA-', $cert->certificate_no);
    }

    /** @test */
    public function quiz_scoring_enforces_pass_mark_and_attempt_limits()
    {
        $this->seedAcademy();
        $quiz = TrainingQuiz::published()->with('questions')->firstOrFail();
        $employee = $this->staff('employee');

        // All-wrong answers fail.
        $wrong = [];
        foreach ($quiz->questions as $q) {
            $wrong['q_' . $q->id] = $q->type === 'multiple' ? ['__wrong__'] : '__wrong__';
        }
        $this->actingAs($employee)->post(route('admin.academy.quiz.submit', $quiz), $wrong);
        $attempt = $quiz->attempts()->where('user_id', $employee->id)->latest()->first();
        $this->assertFalse((bool) $attempt->passed);

        // Exhaust attempts → quiz page refuses.
        $quiz->update(['max_attempts' => 1]);
        $this->actingAs($employee)->get(route('admin.academy.quiz', $quiz))->assertSessionHas('error');
    }

    /** @test */
    public function practical_review_workflow_updates_status()
    {
        $this->seed(\Database\Seeders\TrainingAcademySeeder::class);
        $course = TrainingCourse::where('slug', 'full-lifecycle-simulation')->firstOrFail();
        $practical = $course->practicals()->where('is_published', true)->firstOrFail();
        $trainer = $this->staff('admin');
        $employee = $this->staff('employee');

        $responses = [];
        foreach (($practical->checklist ?? ['step']) as $i => $item) {
            $responses[$i] = 'Completed with synthetic [TRAINING] evidence and records checked.';
        }
        $this->actingAs($employee)->post(route('admin.academy.practical.submit', $practical), ['responses' => $responses])->assertSessionHas('success');
        $submission = TrainingAssessmentSubmission::where('assessment_id', $practical->id)->where('user_id', $employee->id)->firstOrFail();

        $this->actingAs($trainer)->post(route('admin.training.submissions.review', $submission), ['status' => 'passed', 'score' => 90, 'feedback' => 'Excellent end-to-end evidence.'])->assertSessionHas('success');
        $this->assertEquals('passed', $submission->fresh()->status);
    }

    /** @test */
    public function trainer_notes_and_reset_work()
    {
        $course = $this->seedAcademy();
        $trainer = $this->staff('admin');
        $employee = $this->staff('employee');

        $this->actingAs($trainer)->post(route('admin.training.assign', $course), ['user_ids' => [$employee->id]]);
        $assignment = TrainingAssignment::where('course_id', $course->id)->where('user_id', $employee->id)->firstOrFail();

        $this->actingAs($trainer)->post(route('admin.training.notes.store', $employee), ['note' => 'Great progress, focus on SLA clocks.']);
        $this->assertDatabaseHas('training_trainer_notes', ['user_id' => $employee->id]);

        $this->actingAs($trainer)->post(route('admin.training.assignments.reset', $assignment))->assertSessionHas('success');
        $this->assertEquals('assigned', $assignment->fresh()->status);
    }

    /** @test */
    public function dashboards_and_reports_render()
    {
        $this->seedAcademy();
        $trainer = $this->staff('admin');
        $employee = $this->staff('employee');
        $this->actingAs($trainer)->get(route('admin.training.dashboard'))->assertStatus(200);
        $this->actingAs($trainer)->get(route('admin.training.reports'))->assertStatus(200);
        $this->actingAs($trainer)->get(route('admin.training.submissions'))->assertStatus(200);
        $this->actingAs($trainer)->get(route('admin.training.employees.show', $employee))->assertStatus(200);
        $this->actingAs($employee)->get(route('admin.academy.my'))->assertStatus(200);
        $this->actingAs($employee)->get(route('admin.academy.certificates'))->assertStatus(200);
    }
}
