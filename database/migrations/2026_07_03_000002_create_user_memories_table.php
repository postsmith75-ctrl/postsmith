<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_memories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 80)->index();
            $table->string('source', 80)->default('manual')->index();
            $table->string('title')->nullable();
            $table->text('content');
            $table->json('metadata')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();

            $table->index(['user_id', 'active', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_memories');
    }
};
