<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('table_id')->nullable()->constrained('restaurant_tables')->nullOnDelete();
            $table->string('bill_number');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('service_charge', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->enum('payment_method', ['cash', 'card', 'online', 'split'])->default('cash');
            $table->enum('payment_status', ['unpaid', 'paid', 'partially_paid', 'refunded'])->default('unpaid');
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('change_amount', 10, 2)->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['business_id', 'bill_number']);
            $table->index(['business_id', 'payment_status']);
            $table->index(['business_id', 'created_at']);
        });

        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->unsignedInteger('sort_order')->default(0);

            $table->index('bill_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_items');
        Schema::dropIfExists('bills');
    }
};
