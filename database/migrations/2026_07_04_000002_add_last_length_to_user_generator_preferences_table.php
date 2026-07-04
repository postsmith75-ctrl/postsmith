<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_generator_preferences', function (Blueprint $table) {
            if (! Schema::hasColumn('user_generator_preferences', 'last_length')) {
                $table->string('last_length', 100)->nullable()->after('last_goal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_generator_preferences', function (Blueprint $table) {
            if (Schema::hasColumn('user_generator_preferences', 'last_length')) {
                $table->dropColumn('last_length');
            }
        });
    }
};
