<?php

declare(strict_types=1);

use App\Enums\CrmNotificationType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_notifications', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('dealership_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('recipient_user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('type', 64)
                ->default(CrmNotificationType::NEW_LEAD->value);

            $table->string('title');
            $table->text('body')->nullable();

            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('target_url')->nullable();

            $table->json('payload')->nullable();

            $table->timestamp('read_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index(['recipient_user_id', 'read_at']);
            $table->index(['recipient_user_id', 'created_at']);
            $table->index(['dealership_id', 'type']);
            $table->index('expires_at');
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_notifications');
    }
};
