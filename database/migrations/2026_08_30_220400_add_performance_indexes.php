<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->index(['manager_id', 'status'], 'projects_manager_status_idx');
            $table->index(['due_date', 'status'], 'projects_due_status_idx');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['due_date', 'status', 'assigned_to'], 'tasks_due_status_assignee_idx');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->index(['sla_due_at', 'status'], 'tickets_sla_status_idx');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'audit_user_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', fn (Blueprint $table) => $table->dropIndex('audit_user_created_idx'));
        Schema::table('tickets', fn (Blueprint $table) => $table->dropIndex('tickets_sla_status_idx'));
        Schema::table('tasks', fn (Blueprint $table) => $table->dropIndex('tasks_due_status_assignee_idx'));
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_manager_status_idx');
            $table->dropIndex('projects_due_status_idx');
        });
    }
};
