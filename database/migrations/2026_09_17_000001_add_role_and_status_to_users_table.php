<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Role di sini cuma buat level GLOBAL:
     * - admin  -> bisa akses panel admin, kelola semua user
     * - user   -> user biasa, role di dalam workspace (owner/member)
     *             ditentukan terpisah lewat tabel workspace_members
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'user'])->default('user')->after('email');
            $table->boolean('status')->default(true)->after('role'); // true = aktif
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'status']);
        });
    }
};
