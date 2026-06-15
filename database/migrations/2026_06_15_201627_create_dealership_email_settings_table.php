<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dealership_email_settings', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('dealership_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('domain')->nullable();

            $table->string('domain_provider')->default('namesilo');
            $table->string('mailbox_provider')->default('titan');
            $table->string('sending_provider')->default('resend');

            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();

            $table->text('resend_api_key')->nullable();
            $table->text('resend_webhook_secret')->nullable();

            $table->string('titan_email')->nullable();
            $table->string('titan_account_reference')->nullable();
            $table->text('titan_api_key')->nullable();

            $table->string('dns_status')->default('not_configured');
            $table->string('mailbox_status')->default('not_configured');
            $table->string('sending_status')->default('not_configured');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique('dealership_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dealership_email_settings');
    }
};
