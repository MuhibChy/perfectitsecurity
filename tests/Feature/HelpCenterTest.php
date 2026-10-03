<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\TrainingCenterLessons;
use App\Support\TrainingCenterProblems;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function staff(string $role = 'employee'): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }

    /** @test */
    public function staff_can_open_training_and_problem_centers_but_customers_cannot()
    {
        $employee = $this->staff('employee');
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        foreach (['admin.help.training.index', 'admin.help.problems.index'] as $route) {
            $this->actingAs($employee)->get(route($route))->assertStatus(200);
            $this->actingAs($customer)->get(route($route))->assertStatus(403);
        }
    }

    /** @test */
    public function sidebar_contains_training_and_problem_solution_buttons()
    {
        $content = $this->actingAs($this->staff('support_agent'))->get(route('admin.dashboard'))->getContent();
        $this->assertStringContainsString('>Training<', $content);
        $this->assertStringContainsString('>Problem &amp; Solution<', $content);
        $this->assertStringContainsString(route('admin.help.training.index'), $content);
        $this->assertStringContainsString(route('admin.help.problems.index'), $content);
    }

    /** @test */
    public function every_lesson_page_renders_with_structure_and_valid_links()
    {
        $employee = $this->staff('employee');
        $ordered = TrainingCenterLessons::orderedSlugs();
        $this->assertGreaterThanOrEqual(26, count($ordered));

        foreach (TrainingCenterLessons::all() as $lesson) {
            $response = $this->actingAs($employee)->get(route('admin.help.training.show', $lesson['slug']));
            $response->assertStatus(200);
            $content = $response->getContent();
            foreach (['Step-by-step procedure', 'Real-world example', 'Common mistakes', 'Next step', 'Print Training', 'On This Page'] as $marker) {
                $this->assertStringContainsString($marker, $content, "Lesson {$lesson['slug']} missing: {$marker}");
            }
            foreach ($lesson['problems'] ?? [] as $pslug) {
                $this->assertNotNull(TrainingCenterProblems::find($pslug), "Lesson {$lesson['slug']} links unknown problem {$pslug}");
            }
        }
    }

    /** @test */
    public function every_problem_article_renders_with_format_and_valid_training_links()
    {
        $employee = $this->staff('support_agent');
        $all = TrainingCenterProblems::all();
        $this->assertGreaterThanOrEqual(25, count($all));

        foreach ($all as $article) {
            $response = $this->actingAs($employee)->get(route('admin.help.problems.show', $article['slug']));
            $response->assertStatus(200);
            $content = $response->getContent();
            foreach (['Symptoms', 'Possible Causes', 'Verification', 'Escalation', 'Related Training'] as $marker) {
                $this->assertStringContainsString($marker, $content, "Article {$article['slug']} missing: {$marker}");
            }
            foreach ($article['related'] ?? [] as $lslug) {
                $this->assertNotNull(TrainingCenterLessons::find($lslug), "Article {$article['slug']} links unknown lesson {$lslug}");
            }
        }
    }

    /** @test */
    public function search_and_filters_work_on_both_centers()
    {
        $employee = $this->staff('employee');

        $this->actingAs($employee)->get(route('admin.help.training.index', ['q' => 'payment']))
            ->assertStatus(200)->assertSee('Payment Verification', false);
        $this->actingAs($employee)->get(route('admin.help.training.index', ['category' => 'finance']))
            ->assertStatus(200)->assertSee('Finance Overview', false);
        $this->actingAs($employee)->get(route('admin.help.problems.index', ['q' => 'Outlook']))
            ->assertStatus(200)->assertSee('Outlook send/receive', false);
        $this->actingAs($employee)->get(route('admin.help.problems.index', ['filter' => 'finance']))
            ->assertStatus(200)->assertSee('Remaining balance', false);
    }

    /** @test */
    public function unknown_slugs_return_404()
    {
        $employee = $this->staff('employee');
        $this->actingAs($employee)->get(route('admin.help.training.show', 'no-such-lesson'))->assertStatus(404);
        $this->actingAs($employee)->get(route('admin.help.problems.show', 'no-such-problem'))->assertStatus(404);
    }
}
