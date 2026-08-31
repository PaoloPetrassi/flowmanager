<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('is_template')->default(false)->after('priority');
            $table->unsignedTinyInteger('progress_override')->nullable()->after('is_template');
            $table->unsignedInteger('estimated_minutes')->nullable()->after('budget');
        });

        Schema::create('project_user', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 80)->nullable();
            $table->timestamps();
            $table->primary(['project_id', 'user_id']);
        });

        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name', 180);
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['project_id', 'due_date']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('project_id')->constrained('tasks')->nullOnDelete();
            $table->foreignId('milestone_id')->nullable()->after('parent_id')->constrained('milestones')->nullOnDelete();
            $table->unsignedInteger('estimated_minutes')->nullable()->after('due_date');
        });

        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('depends_on_task_id')->constrained('tasks')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['task_id', 'depends_on_task_id']);
        });

        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('minutes')->default(0);
            $table->string('note', 500)->nullable();
            $table->timestamps();
            $table->index(['task_id', 'started_at']);
            $table->index(['user_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
        Schema::dropIfExists('task_dependencies');

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['milestone_id']);
            $table->dropColumn(['parent_id', 'milestone_id', 'estimated_minutes']);
        });

        Schema::dropIfExists('milestones');
        Schema::dropIfExists('project_user');

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['is_template', 'progress_override', 'estimated_minutes']);
        });
    }
};
