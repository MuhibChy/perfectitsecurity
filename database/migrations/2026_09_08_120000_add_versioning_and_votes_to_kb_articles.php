<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kb_article_versions')) {
            Schema::create('kb_article_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('article_id')->constrained('kb_articles')->cascadeOnDelete();
                $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('title');
                $table->text('content');
                $table->integer('version_number')->default(1);
                $table->text('edit_summary')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kb_article_votes')) {
            Schema::create('kb_article_votes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('article_id')->constrained('kb_articles')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('session_id', 64)->nullable();
                $table->boolean('is_helpful');
                $table->timestamps();

                $table->unique(['article_id', 'user_id']);
                $table->unique(['article_id', 'session_id']);
            });
        }

        // Add new columns to kb_articles (only if they don't already exist)
        Schema::table('kb_articles', function (Blueprint $table) {
            if (!Schema::hasColumn('kb_articles', 'view_count')) {
                $table->unsignedInteger('view_count')->default(0);
            }
            if (!Schema::hasColumn('kb_articles', 'current_version')) {
                $table->unsignedInteger('current_version')->default(1);
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('kb_article_versions')) {
            Schema::dropIfExists('kb_article_versions');
        }
        if (Schema::hasTable('kb_article_votes')) {
            Schema::dropIfExists('kb_article_votes');
        }

        Schema::table('kb_articles', function (Blueprint $table) {
            if (Schema::hasColumn('kb_articles', 'view_count')) {
                $table->dropColumn('view_count');
            }
            if (Schema::hasColumn('kb_articles', 'current_version')) {
                $table->dropColumn('current_version');
            }
        });
    }
};
