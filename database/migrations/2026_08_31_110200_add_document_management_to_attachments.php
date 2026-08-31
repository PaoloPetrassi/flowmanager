<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->string('document_category', 60)->nullable()->after('mime_type');
            $table->string('document_status', 30)->default('active')->after('document_category');
            $table->unsignedInteger('version')->default(1)->after('document_status');
            $table->foreignId('parent_attachment_id')->nullable()->after('version')->constrained('attachments')->nullOnDelete();
            $table->date('expires_at')->nullable()->after('parent_attachment_id');
            $table->foreignId('approved_by')->nullable()->after('expires_at')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->string('checksum', 64)->nullable()->after('approved_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropForeign(['parent_attachment_id']);
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['checksum']);
            $table->dropColumn(['document_category', 'document_status', 'version', 'parent_attachment_id', 'expires_at', 'approved_by', 'approved_at', 'checksum']);
        });
    }
};
