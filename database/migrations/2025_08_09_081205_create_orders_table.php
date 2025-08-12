<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('code')->unique();
            $table->enum('payment_method', ['vnpay', 'cash_at_counter']);
            $table->enum('payment_status', ['unpaid', 'paid', 'failed'])->default('unpaid');
            $table->enum('status', ['pending', 'completed', 'canceled'])->default('pending');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);
            $table->datetime('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->enum('type', ['sale', 'refund'])->default('sale');
            $table->foreignId('parent_order_id')->nullable()->constrained('orders')->onDelete('cascade')->after('type');
            $table->enum('refund_reason', [
                'customer_request',
                'damaged_product',
                'wrong_product',
                'quality_issue',
                'other'
            ])->nullable();
            $table->text('refund_reason_detail')->nullable();
            $table->timestamps();

            $table->index(['code']);
            $table->index(['status']);
            $table->index(['payment_status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
