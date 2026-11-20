<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('path')->unique();
            $table->string('template', 40)->default('default');
            $table->text('excerpt')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->publishable();
            $table->seo();
            $table->unique(['parent_id', 'slug']);
        });

        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('type', 60);
            $table->string('label')->nullable();
            $table->string('anchor', 80)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('data');
            $table->timestamps();
            $table->index(['page_id', 'sort_order']);
        });

        Schema::create('content_revisions', function (Blueprint $table) {
            $table->id();
            $table->morphs('revisionable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind', 20)->default('snapshot');
            $table->string('note')->nullable();
            $table->json('data');
            $table->timestamps();
            $table->index(['revisionable_type', 'revisionable_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_revisions');
        Schema::dropIfExists('page_sections');
        Schema::dropIfExists('pages');
    }
};
