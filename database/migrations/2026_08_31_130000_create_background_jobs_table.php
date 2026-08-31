<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('background_jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 80);
            $table->string('name');
            $table->string('queue', 80)->default('default');
            $table->string('status', 24)->default('pending');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->json('payload')->nullable();
            $table->json('result')->nullable();
            $table->text('message')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at'], 'background_jobs_status_created_idx');
            $table->index(['user_id', 'created_at'], 'background_jobs_user_created_idx');
            $table->index(['type', 'status'], 'background_jobs_type_status_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(
                ['notifiable_type', 'notifiable_id', 'read_at'],
                'notifications_owner_read_idx'
            );
        });

        Schema::table('import_runs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'import_runs_user_created_idx');
        });

        Schema::table('webhook_deliveries', function (Blueprint $table) {
            $table->index(['successful', 'delivered_at'], 'webhook_deliveries_status_date_idx');
        });

        Schema::table('scheduled_reports', function (Blueprint $table) {
            $table->index(['is_active', 'next_run_at'], 'scheduled_reports_due_idx');
        });
    }

    public function down(): void
    {
        Schema::table('scheduled_reports', function (Blueprint $table) {
            $table->dropIndex('scheduled_reports_due_idx');
        });

        Schema::table('webhook_deliveries', function (Blueprint $table) {
            $table->dropIndex('webhook_deliveries_status_date_idx');
        });

        Schema::table('import_runs', function (Blueprint $table) {
            $table->dropIndex('import_runs_user_created_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_owner_read_idx');
        });

        Schema::dropIfExists('background_jobs');
    }
};
