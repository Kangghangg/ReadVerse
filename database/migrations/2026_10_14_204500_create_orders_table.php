<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_code')->unique();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('ebook_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 10, 2)->default(0.00);
                $table->string('payment_method', 50)->default('qr_transfer');
                $table->string('status', 30)->default('completed');
                $table->timestamps();

                $table->index(['user_id', 'ebook_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
