<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('recurrence', 20)->default('none')->after('priority');
            $table->unsignedSmallInteger('recurrence_interval')->default(1)->after('recurrence');
            $table->date('recurrence_ends_at')->nullable()->after('recurrence_interval');
            $table->foreignId('recurrence_source_id')->nullable()->after('recurrence_ends_at')->constrained('tasks')->nullOnDelete();
            $table->foreignId('next_recurrence_id')->nullable()->after('recurrence_source_id')->constrained('tasks')->nullOnDelete();
            $table->timestamp('due_reminder_sent_at')->nullable()->after('completed_at');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('sla_due_at')->nullable()->after('priority');
            $table->timestamp('sla_breached_at')->nullable()->after('sla_due_at');
            $table->timestamp('sla_reminder_sent_at')->nullable()->after('sla_breached_at');
            $table->timestamp('first_response_at')->nullable()->after('sla_reminder_sent_at');
        });

        DB::table('tickets')
            ->select(['id', 'priority', 'created_at'])
            ->orderBy('id')
            ->chunkById(100, function ($tickets) {
                foreach ($tickets as $ticket) {
                    $hours = match ($ticket->priority) {
                        'urgent' => 4,
                        'high' => 8,
                        'low' => 48,
                        default => 24,
                    };

                    DB::table('tickets')
                        ->where('id', $ticket->id)
                        ->update([
                            'sla_due_at' => Carbon::parse($ticket->created_at)->addHours($hours),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'sla_due_at',
                'sla_breached_at',
                'sla_reminder_sent_at',
                'first_response_at',
            ]);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['recurrence_source_id']);
            $table->dropForeign(['next_recurrence_id']);
            $table->dropColumn([
                'recurrence',
                'recurrence_interval',
                'recurrence_ends_at',
                'recurrence_source_id',
                'next_recurrence_id',
                'due_reminder_sent_at',
            ]);
        });
    }
};
