<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('status', 20)->default('active')->after('role');
            });
        }

        if (!Schema::hasTable('unlocked_ebooks')) {
            Schema::create('unlocked_ebooks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('ebook_id')->constrained()->onDelete('cascade');
                $table->timestamps();
                $table->unique(['user_id', 'ebook_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('unlocked_ebooks');

        if (Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
