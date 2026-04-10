<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('default');
            $table->string('stripe_id')->unique()->nullable();
            $table->string('stripe_status')->nullable();
            $table->string('stripe_price')->nullable();
            $table->integer('quantity')->nullable();
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'stripe_status']);
        });

        Schema::create('subscription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_id')->unique();
            $table->string('stripe_product')->nullable();
            $table->string('stripe_price');
            $table->integer('quantity')->nullable();
            $table->timestamps();
        });

        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stripe_invoice_id')->nullable();
            $table->unsignedInteger('amount'); // in cents
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['draft', 'open', 'paid', 'uncollectible', 'void'])->default('draft');
            $table->timestamp('paid_at')->nullable();
            $table->string('invoice_pdf_url')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');
    }
};
