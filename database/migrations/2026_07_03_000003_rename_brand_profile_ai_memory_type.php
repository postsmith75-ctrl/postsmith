<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('user_memories')
            ->where('type', 'brand_profile')
            ->where('source', 'onboarding')
            ->update([
                'type' => 'profile',
                'title' => 'Profile',
            ]);
    }

    public function down(): void
    {
        DB::table('user_memories')
            ->where('type', 'profile')
            ->where('source', 'onboarding')
            ->update([
                'type' => 'brand_profile',
                'title' => 'Brand profile',
            ]);
    }
};
