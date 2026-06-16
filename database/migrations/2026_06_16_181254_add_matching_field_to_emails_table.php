<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emails', function (Blueprint $table): void {
            $table->boolean('is_matched_to_lead')
                ->default(false)
                ->after('lead_id');

            $table->boolean('needs_manual_review')
                ->default(false)
                ->after('is_matched_to_lead');

            $table->index(['dealership_id', 'needs_manual_review']);
        });
    }

    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table): void {
            $table->dropIndex(['dealership_id', 'needs_manual_review']);
            $table->dropColumn([
                'is_matched_to_lead',
                'needs_manual_review',
            ]);
        });
    }
};
