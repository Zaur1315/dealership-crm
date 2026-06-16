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
        Schema::table('dealership_email_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'resend_api_key',
                'resend_webhook_secret',
                'titan_api_key',
                'sending_provider',
            ]);

            $table->renameColumn('titan_email', 'email_address');

            $table->string('imap_host')->nullable()->after('email_address');
            $table->unsignedInteger('imap_port')->nullable()->after('imap_host');
            $table->string('imap_encryption')->nullable()->after('imap_port');
            $table->string('imap_username')->nullable()->after('imap_encryption');
            $table->text('imap_password')->nullable()->after('imap_username');

            $table->string('smtp_host')->nullable()->after('imap_password');
            $table->unsignedInteger('smtp_port')->nullable()->after('smtp_host');
            $table->string('smtp_encryption')->nullable()->after('smtp_port');
            $table->string('smtp_username')->nullable()->after('smtp_encryption');
            $table->text('smtp_password')->nullable()->after('smtp_username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dealership_email_settings', function (Blueprint $table): void {
            $table->text('resend_api_key')->nullable();
            $table->text('resend_webhook_secret')->nullable();
            $table->text('titan_api_key')->nullable();
            $table->string('sending_provider')->default('resend');

            $table->renameColumn('email_address', 'titan_email');

            $table->dropColumn([
                'imap_host',
                'imap_port',
                'imap_encryption',
                'imap_username',
                'imap_password',
                'smtp_host',
                'smtp_port',
                'smtp_encryption',
                'smtp_username',
                'smtp_password',
            ]);
        });
    }
};
