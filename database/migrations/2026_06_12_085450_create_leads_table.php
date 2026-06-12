<?php

declare(strict_types=1);

use App\Enums\LeadPipelineStage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('dealership_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('created_by_name')->nullable();

            $table->string('full_name');
            $table->string('phone_number');
            $table->string('email');
            $table->text('address')->nullable();

            $table->decimal('deal_value', 12, 2)->nullable();

            $table->string('pipeline_stage', 64)
                ->default(LeadPipelineStage::NEW->value);

            $table->timestamp('first_communication_at')->nullable();
            $table->timestamp('won_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->timestamp('not_interested_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['dealership_id', 'pipeline_stage']);
            $table->index(['dealership_id', 'email']);
            $table->index(['dealership_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
