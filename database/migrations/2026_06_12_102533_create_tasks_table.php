<?php

declare(strict_types=1);

use App\Enums\TaskStatus;
use App\Enums\TaskType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('dealership_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('lead_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('created_by_name')->nullable();

            $table->foreignId('completed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('type', 32)->default(TaskType::GENERAL->value);
            $table->string('status', 32)->default(TaskStatus::ACTIVE->value);

            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expired_at')->nullable();

            $table->foreignId('verified_email_id')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['dealership_id', 'status']);
            $table->index(['dealership_id', 'due_at']);
            $table->index(['lead_id', 'status']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
