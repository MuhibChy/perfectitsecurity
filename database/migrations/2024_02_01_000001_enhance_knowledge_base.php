<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // KB Tags
        Schema::create('kb_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // KB Article Tags pivot
        Schema::create('kb_article_tag', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained('kb_articles')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('kb_tags')->cascadeOnDelete();
            $table->primary(['article_id', 'tag_id']);
        });

        // KB Related Articles pivot
        Schema::create('kb_related_article', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained('kb_articles')->cascadeOnDelete();
            $table->foreignId('related_article_id')->constrained('kb_articles')->cascadeOnDelete();
            $table->primary(['article_id', 'related_article_id']);
        });

        // Enhance KB Articles
        Schema::table('kb_articles', function (Blueprint $table) {
            $table->text('keywords')->nullable()->after('meta_description');
            $table->string('visibility')->default('public')->after('keywords'); // public, customer, employee, admin
            $table->string('language', 5)->default('en')->after('visibility');
            $table->string('difficulty')->default('beginner')->after('language'); // beginner, intermediate, advanced
            $table->integer('helpful_count')->default(0)->after('difficulty');
            $table->integer('not_helpful_count')->default(0)->after('helpful_count');
        });

        // Enhance KB Categories (subcategories)
        Schema::table('kb_categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('kb_categories')->nullOnDelete()->after('id');
            $table->boolean('is_internal')->default(false)->after('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kb_related_article');
        Schema::dropIfExists('kb_article_tag');
        Schema::dropIfExists('kb_tags');

        Schema::table('kb_articles', function (Blueprint $table) {
            $table->dropColumn(['keywords', 'visibility', 'language', 'difficulty', 'helpful_count', 'not_helpful_count']);
        });

        Schema::table('kb_categories', function (Blueprint $table) {
            $table->dropColumn(['parent_id', 'is_internal']);
        });
    }
};
