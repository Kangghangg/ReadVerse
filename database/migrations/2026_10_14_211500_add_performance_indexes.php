<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ebooks', function (Blueprint $table) {
            $table->index(['status', 'category_id'], 'idx_ebooks_status_category');
            $table->index(['status', 'author_id'], 'idx_ebooks_status_author');
            $table->index(['status', 'price'], 'idx_ebooks_status_price');
            $table->index(['status', 'view_count'], 'idx_ebooks_status_views');
            $table->index(['status', 'created_at'], 'idx_ebooks_status_created');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'status'], 'idx_users_role_status');
        });
    }

    public function down(): void
    {
        Schema::table('ebooks', function (Blueprint $table) {
            $table->dropIndex('idx_ebooks_status_category');
            $table->dropIndex('idx_ebooks_status_author');
            $table->dropIndex('idx_ebooks_status_price');
            $table->dropIndex('idx_ebooks_status_views');
            $table->dropIndex('idx_ebooks_status_created');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role_status');
        });
    }
};
