<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('staff')->after('password');
        });

        Schema::create('admin_records', function (Blueprint $table) {
            $table->id();
            $table->string('module', 64)->index();
            $table->string('title');
            $table->json('data')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('module', 64);
            $table->string('action', 32);
            $table->unsignedBigInteger('record_id')->nullable();
            $table->string('description');
            $table->json('changes')->nullable();
            $table->timestamps();
            $table->index(['module', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('admin_records');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
