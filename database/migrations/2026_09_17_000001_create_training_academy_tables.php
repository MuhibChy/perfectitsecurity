<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_courses', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('role_target')->nullable()->index();
            $table->string('difficulty')->default('beginner');
            $table->unsignedInteger('duration_minutes')->default(30);
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_published')->default(false);
            $table->string('version', 20)->default('1.0');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('training_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('training_courses')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('training_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('training_modules')->cascadeOnDelete();
            $table->string('title');
            $table->string('lesson_type')->default('guide');
            $table->longText('body');
            $table->json('objectives')->nullable();
            $table->json('steps')->nullable();
            $table->json('why_matters')->nullable();
            $table->json('common_mistakes')->nullable();
            $table->json('discussion_questions')->nullable();
            $table->unsignedInteger('duration_minutes')->default(10);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(false);
            $table->string('version', 20)->default('1.0');
            $table->timestamps();
        });

        Schema::create('training_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('training_courses')->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained('training_lessons')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('pass_score')->default(70);
            $table->unsignedTinyInteger('max_attempts')->default(3);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('training_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('training_quizzes')->cascadeOnDelete();
            $table->string('type')->default('single');
            $table->text('prompt');
            $table->json('options')->nullable();
            $table->json('correct')->nullable();
            $table->text('explanation')->nullable();
            $table->unsignedTinyInteger('points')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('training_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('training_quizzes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('max_score')->default(0);
            $table->boolean('passed')->default(false);
            $table->json('answers')->nullable();
            $table->timestamps();
            $table->index(['quiz_id', 'user_id']);
        });

        Schema::create('training_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('training_courses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('due_at')->nullable();
            $table->string('status')->default('assigned');
            $table->timestamps();
            $table->unique(['course_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('training_lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('training_lessons')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['lesson_id', 'user_id']);
        });

        Schema::create('training_practical_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('training_courses')->cascadeOnDelete();
            $table->string('title');
            $table->longText('instructions');
            $table->json('checklist')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('training_assessment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('training_practical_assessments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->json('responses')->nullable();
            $table->string('status')->default('submitted');
            $table->unsignedTinyInteger('score')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('feedback')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['assessment_id', 'user_id']);
        });

        Schema::create('training_trainer_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->text('note');
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('training_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('training_courses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->nullable();
            $table->string('certificate_no')->unique();
            $table->string('course_version', 20)->default('1.0');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['course_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_certificates');
        Schema::dropIfExists('training_trainer_notes');
        Schema::dropIfExists('training_assessment_submissions');
        Schema::dropIfExists('training_practical_assessments');
        Schema::dropIfExists('training_lesson_progress');
        Schema::dropIfExists('training_assignments');
        Schema::dropIfExists('training_quiz_attempts');
        Schema::dropIfExists('training_questions');
        Schema::dropIfExists('training_quizzes');
        Schema::dropIfExists('training_lessons');
        Schema::dropIfExists('training_modules');
        Schema::dropIfExists('training_courses');
    }
};
