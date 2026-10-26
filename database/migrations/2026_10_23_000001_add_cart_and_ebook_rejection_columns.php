<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ebooks')) {
            Schema::table('ebooks', function (Blueprint $table) {
                if (!Schema::hasColumn('ebooks', 'rejection_reason')) {
                    $table->string('rejection_reason')->nullable()->after('status');
                }
                if (!Schema::hasColumn('ebooks', 'rejection_note')) {
                    $table->text('rejection_note')->nullable()->after('rejection_reason');
                }
                if (!Schema::hasColumn('ebooks', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('rejection_note');
                }
            });
        }

        if (!Schema::hasTable('cart_items')) {
            Schema::create('cart_items', function (Blueprint $table) {
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
        Schema::dropIfExists('cart_items');

        if (Schema::hasTable('ebooks')) {
            Schema::table('ebooks', function (Blueprint $table) {
                $cols = [];
                foreach (['rejection_reason', 'rejection_note', 'rejected_at'] as $col) {
                    if (Schema::hasColumn('ebooks', $col)) {
                        $cols[] = $col;
                    }
                }
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
