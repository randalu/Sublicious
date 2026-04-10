<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('order_number');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('table_id')->nullable()->constrained('restaurant_tables')->nullOnDelete();
            $table->foreignId('discount_code_id')->nullable()->constrained('discount_codes')->nullOnDelete();
            $table->enum('order_type', ['dine_in', 'delivery', 'takeaway', 'online'])->default('dine_in');
            $table->enum('status', [
                'pending', 'accepted', 'preparing', 'ready',
                'dispatched', 'delivered', 'completed', 'cancelled', 'refunded'
            ])->default('pending');
            // Customer info (captured at order time)
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            // Delivery info
            $table->text('delivery_address')->nullable();
            $table->decimal('delivery_lat', 10, 7)->nullable();
            $table->decimal('delivery_lng', 10, 7)->nullable();
            $table->string('delivery_instructions')->nullable();
            // Financials
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('service_charge', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            // Payment
            $table->enum('payment_method', ['cash', 'card', 'online', 'pending'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'partially_paid', 'refunded'])->default('unpaid');
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('change_amount', 10, 2)->default(0);
            // Meta
            $table->text('notes')->nullable();
            $table->enum('source', ['pos', 'online', 'qr'])->default('pos');
            $table->string('cancel_reason')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['business_id', 'order_number']);
            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'order_type']);
            $table->index(['business_id', 'payment_status']);
            $table->index(['business_id', 'created_at']);
            $table->index(['table_id', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('item_variant_id')->nullable()->constrained('item_variants')->nullOnDelete();
            $table->string('name'); // snapshot of item name
            $table->string('variant_name')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('subtotal', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['order_id']);
        });

        Schema::create('order_item_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('addon_group_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name'); // snapshot
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);

            $table->index('order_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_addons');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
