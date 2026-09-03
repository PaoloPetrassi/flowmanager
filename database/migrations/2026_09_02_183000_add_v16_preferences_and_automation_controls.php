<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->json('notification_preferences')->nullable()->after('table_preferences');
        });

        Schema::table('automation_rules', function (Blueprint $table) {
            $table->unsignedInteger('cooldown_minutes')->default(1440)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('automation_rules', function (Blueprint $table) {
            $table->dropColumn('cooldown_minutes');
        });

        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropColumn('notification_preferences');
        });
    }
};
