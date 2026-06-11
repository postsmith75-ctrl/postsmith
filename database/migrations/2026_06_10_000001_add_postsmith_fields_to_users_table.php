<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique();
            $table->timestamp('username_changed_at')->nullable();
            $table->string('tier', 20)->default('free')->index();
            $table->string('role', 20)->default('user');
            $table->boolean('email_verified')->default(true);
            $table->string('verification_code')->nullable();
            $table->timestamp('verification_sent_at')->nullable();
            $table->string('reset_code')->nullable();
            $table->timestamp('reset_expires_at')->nullable();
            $table->unsignedInteger('generations_used')->default(0);
            $table->timestamp('generations_reset_at')->nullable();
            $table->timestamp('pro_expires_at')->nullable();
            $table->timestamp('viral_reset_at')->nullable();
            $table->timestamp('downgraded_at')->nullable();
            $table->unsignedInteger('followup_count')->default(0);
            $table->timestamp('last_followup_sent')->nullable();
            $table->timestamp('last_generation_at')->nullable();
            $table->timestamp('sent_renewal_reminder_at')->nullable();
            $table->timestamp('sent_onboarding_email_at')->nullable();
            $table->string('google_id')->nullable()->index();
            $table->unsignedInteger('current_streak')->default(0);
            $table->unsignedInteger('longest_streak')->default(0);
            $table->date('last_streak_date')->nullable();
            $table->string('zernio_api_key')->nullable();
            $table->timestamp('zernio_connected_at')->nullable();
            $table->date('last_weekly_digest')->nullable();
            $table->boolean('badge_display')->default(true);
            $table->string('z_rss_token')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'username_changed_at',
                'tier',
                'role',
                'email_verified',
                'verification_code',
                'verification_sent_at',
                'reset_code',
                'reset_expires_at',
                'generations_used',
                'generations_reset_at',
                'pro_expires_at',
                'viral_reset_at',
                'downgraded_at',
                'followup_count',
                'last_followup_sent',
                'last_generation_at',
                'sent_renewal_reminder_at',
                'sent_onboarding_email_at',
                'google_id',
                'current_streak',
                'longest_streak',
                'last_streak_date',
                'zernio_api_key',
                'zernio_connected_at',
                'last_weekly_digest',
                'badge_display',
                'z_rss_token',
            ]);
        });
    }
};
