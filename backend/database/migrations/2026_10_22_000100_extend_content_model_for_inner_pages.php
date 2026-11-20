<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_sections', function (Blueprint $table) {
            $table->string('sectionable_type', 40)->nullable()->after('id');
            $table->unsignedBigInteger('sectionable_id')->nullable()->after('sectionable_type');
            $table->index(['sectionable_type', 'sectionable_id', 'sort_order']);
        });
        DB::table('page_sections')->update(['sectionable_type' => 'page', 'sectionable_id' => DB::raw('page_id')]);
        Schema::table('page_sections', function (Blueprint $table) {
            $table->dropForeign(['page_id']);
            $table->dropIndex(['page_id', 'sort_order']);
            $table->dropColumn('page_id');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->string('kind', 20)->default('image')->after('uuid')->index();
        });

        Schema::table('faculty', function (Blueprint $table) {
            $table->string('category', 20)->default('residential')->after('school_id')->index();
            $table->json('experience')->nullable()->after('qualifications');
            $table->longText('research')->nullable()->after('experience');
            $table->json('awards')->nullable()->after('research');
        });

        Schema::create('leaders', function (Blueprint $table) {
            $table->id();
            $table->string('group', 30)->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('designation')->nullable();
            $table->string('organisation')->nullable();
            $table->text('summary')->nullable();
            $table->longText('bio')->nullable();
            $table->json('achievements')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('photo_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->publishable();
            $table->seo();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('category', 30)->index();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('centre_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->text('summary')->nullable();
            $table->longText('overview')->nullable();
            $table->json('outcomes')->nullable();
            $table->json('speaker')->nullable();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->nullable();
            $table->string('venue')->nullable();
            $table->string('registration_url', 2048)->nullable();
            $table->foreignId('image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->publishable();
            $table->seo();
        });

        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 500);
            $table->string('slug')->unique();
            $table->string('authors')->nullable();
            $table->string('publisher')->nullable();
            $table->date('published_on')->nullable()->index();
            $table->string('topic', 100)->nullable()->index();
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();
            $table->string('url', 2048)->nullable();
            $table->foreignId('image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->publishable();
            $table->seo();
        });

        Schema::create('jobs_openings', function (Blueprint $table) {
            $table->id();
            $table->string('category', 30)->index();
            $table->string('department')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->date('closes_on')->nullable()->index();
            $table->string('mode')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->publishable();
            $table->seo();
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_opening_id')->nullable()->constrained('jobs_openings')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->index();
            $table->string('phone', 40)->nullable();
            $table->string('qualification')->nullable();
            $table->string('qualification_year', 10)->nullable();
            $table->string('institution')->nullable();
            $table->string('experience_years', 20)->nullable();
            $table->string('current_company')->nullable();
            $table->string('location')->nullable();
            $table->string('resume_path')->nullable();
            $table->string('resume_name')->nullable();
            $table->boolean('consent')->default(false);
            $table->string('status', 20)->default('new')->index();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->text('notification_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('jobs_openings');
        Schema::dropIfExists('publications');
        Schema::dropIfExists('events');
        Schema::dropIfExists('leaders');
        Schema::table('faculty', fn (Blueprint $table) => $table->dropColumn(['category', 'experience', 'research', 'awards']));
        Schema::table('media', fn (Blueprint $table) => $table->dropColumn('kind'));
        Schema::table('page_sections', function (Blueprint $table) {
            $table->foreignId('page_id')->nullable()->constrained()->cascadeOnDelete();
        });
        DB::table('page_sections')->where('sectionable_type', 'page')->update(['page_id' => DB::raw('sectionable_id')]);
        Schema::table('page_sections', fn (Blueprint $table) => $table->dropColumn(['sectionable_type', 'sectionable_id']));
    }
};
