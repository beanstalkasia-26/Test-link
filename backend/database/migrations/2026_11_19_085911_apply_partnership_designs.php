<?php

use App\Services\PartnershipPageDesign;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(PartnershipPageDesign::class)->apply();
    }

    public function down(): void {}
};
