<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('leads', 'assigned_to_user_id')) {
            return;
        }

        Schema::table('leads', function (Blueprint $table): void {
            $table->dropIndex(['dealership_id', 'assigned_to_user_id']);
            $table->dropConstrainedForeignId('assigned_to_user_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('leads', 'assigned_to_user_id')) {
            return;
        }

        Schema::table('leads', function (Blueprint $table): void {
            $table
                ->foreignId('assigned_to_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->index(['dealership_id', 'assigned_to_user_id']);
        });
    }
};
