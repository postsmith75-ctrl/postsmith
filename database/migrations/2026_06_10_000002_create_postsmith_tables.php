<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 100)->nullable();
            $table->string('length', 50)->nullable();
            $table->string('driver', 100)->nullable()->index();
            $table->text('post_text')->nullable();
            $table->unsignedInteger('likes')->default(0);
            $table->unsignedInteger('comments')->default(0);
            $table->unsignedInteger('shares')->default(0);
            $table->text('raw_thought')->nullable();
            $table->string('zernio_post_id')->nullable()->index();
            $table->timestamp('last_synced_at')->nullable();
            $table->string('media_url', 500)->nullable();
            $table->string('media_type', 20)->nullable();
            $table->boolean('is_starred')->default(false);
            $table->timestamp('starred_at')->nullable();
            $table->timestamps();
        });

        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('guest_id')->unique();
            $table->unsignedInteger('generations_used')->default(0);
            $table->string('last_ip', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('generation_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('mode', 50)->nullable();
            $table->text('input_text')->nullable();
            $table->string('platform', 100)->nullable();
            $table->string('length', 50)->nullable();
            $table->longText('generated_json')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('viral_lab_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('post_text');
            $table->string('platform', 100)->nullable();
            $table->unsignedInteger('likes')->default(0);
            $table->unsignedInteger('comments')->default(0);
            $table->unsignedInteger('shares')->default(0);
            $table->unsignedInteger('word_count')->default(0);
            $table->longText('detected_drivers')->nullable();
            $table->boolean('new_driver_flag')->default(false);
            $table->string('new_driver_name')->nullable();
            $table->longText('ai_analysis')->nullable();
            $table->string('status', 50)->default('pending');
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('discovered_drivers', function (Blueprint $table) {
            $table->id();
            $table->string('driver_name')->unique();
            $table->text('description')->nullable();
            $table->text('psychology')->nullable();
            $table->unsignedInteger('submissions_count')->default(0);
            $table->decimal('avg_confidence', 5, 2)->default(0);
            $table->string('status', 50)->default('pending');
            $table->foreignId('discovered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('promoted_at')->nullable();
            $table->timestamp('new_until')->nullable();
            $table->boolean('notification_sent')->default(false);
            $table->timestamps();
        });

        Schema::create('user_driver_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('driver_name');
            $table->string('source', 50)->default('personal');
            $table->timestamps();
            $table->unique(['user_id', 'driver_name']);
        });

        Schema::create('training_examples', function (Blueprint $table) {
            $table->id();
            $table->string('driver_name')->index();
            $table->text('raw_thought')->nullable();
            $table->text('transformed_post')->nullable();
            $table->string('platform', 100)->nullable();
            $table->string('source', 50)->default('curated');
            $table->unsignedInteger('engagement_score')->default(0);
            $table->timestamps();
        });

        Schema::create('rate_limits', function (Blueprint $table) {
            $table->id();
            $table->string('identifier');
            $table->string('action', 50);
            $table->unsignedInteger('request_count')->default(1);
            $table->timestamp('window_start')->useCurrent();
            $table->unique(['identifier', 'action', 'window_start']);
        });

        Schema::create('banner_dismissals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamp('dismissed_until')->nullable();
            $table->timestamps();
            $table->unique('user_id');
        });

        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('feedback')->nullable();
            $table->timestamps();
            $table->index('rating');
        });

        Schema::create('post_training_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->boolean('learned')->default(false);
            $table->timestamp('learned_at')->nullable();
            $table->unique('post_id');
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('badge_key', 100);
            $table->string('badge_label');
            $table->timestamp('awarded_at')->useCurrent();
            $table->timestamps();
            $table->unique(['user_id', 'badge_key']);
        });

        Schema::create('streak_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action', 50);
            $table->date('streak_day');
            $table->timestamps();
            $table->index(['user_id', 'streak_day']);
        });
    }

    public function down(): void
    {
        foreach ([
            'streak_log',
            'user_badges',
            'post_training_log',
            'ratings',
            'banner_dismissals',
            'rate_limits',
            'training_examples',
            'user_driver_collections',
            'discovered_drivers',
            'viral_lab_submissions',
            'generation_history',
            'guests',
            'posts',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
