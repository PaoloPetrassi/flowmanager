<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->nullOnDelete();

            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('job_title', 150)->nullable();
            $table->string('department', 100)->nullable();

            $table->string('email', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('mobile', 50)->nullable();

            $table->boolean('is_primary')
                ->default(false);

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('last_name');
            $table->index('email');
            $table->index('is_primary');
            $table->index([
                'company_id',
                'is_primary',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
