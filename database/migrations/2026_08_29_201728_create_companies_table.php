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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150);
            $table->string('legal_name', 200)->nullable();

            $table->string('type', 30);
            $table->string('status', 30);

            $table->string('vat_number', 50)
                ->nullable()
                ->unique();

            $table->string('tax_code', 50)
                ->nullable()
                ->unique();

            $table->string('email', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('website', 255)->nullable();

            $table->string('industry', 100)->nullable();

            $table->unsignedInteger('employees')
                ->nullable();

            $table->string('address', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 20)->nullable();

            $table->char('country_code', 2)
                ->default('IT');

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('type');
            $table->index('status');
            $table->index('city');
            $table->index('industry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};