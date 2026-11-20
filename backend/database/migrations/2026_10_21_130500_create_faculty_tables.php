<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faculty', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->text('summary')->nullable();
            $table->longText('bio')->nullable();
            $table->json('qualifications')->nullable();
            $table->string('research_interests', 1000)->nullable();
            $table->string('profile_url', 2048)->nullable();
            $table->foreignId('photo_id')->nullable()->constrained('media')->nullOnDelete();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->publishable();
            $table->seo();
        });

        Schema::create('faculty_program', function (Blueprint $table) {
            $table->foreignId('faculty_id')->constrained('faculty')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('role')->nullable();
            $table->primary(['faculty_id', 'program_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faculty_program');
        Schema::dropIfExists('faculty');
    }
};
