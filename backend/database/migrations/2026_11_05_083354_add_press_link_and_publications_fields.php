<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('external_url', 2048)->nullable()->after('body');
            $table->foreignId('document_id')->nullable()->after('external_url')->constrained('media')->nullOnDelete();
        });

        Schema::table('faculty', function (Blueprint $table) {
            $table->longText('publications')->nullable()->after('research');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('document_id');
            $table->dropColumn('external_url');
        });

        Schema::table('faculty', function (Blueprint $table) {
            $table->dropColumn('publications');
        });
    }
};
