<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Plans must exist before businesses
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('price_monthly')->default(0); // in cents
            $table->unsignedInteger('price_yearly')->default(0);
            $table->unsignedInteger('max_orders_per_month')->default(50);
            $table->unsignedInteger('max_staff')->default(5);
            $table->unsignedInteger('max_menu_items')->default(50);
            $table->unsignedInteger('max_delivery_zones')->default(3);
            $table->json('features')->nullable(); // feature flags JSON
            $table->string('stripe_price_id_monthly')->nullable();
            $table->string('stripe_price_id_yearly')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Businesses table
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('US');
            $table->string('postal_code')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->text('description')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('timezone')->default('UTC');
            $table->string('website')->nullable();
            $table->decimal('minimum_delivery_order', 10, 2)->default(0);
            $table->enum('subscription_status', ['active', 'trialing', 'past_due', 'suspended', 'cancelled', 'incomplete'])->default('trialing');
            $table->timestamp('trial_ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->string('stripe_id')->nullable()->index();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four')->nullable();
            $table->timestamp('trial_ends_at_stripe')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Users table (FK to businesses)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->enum('role', ['super_admin', 'admin', 'manager', 'cashier', 'kitchen', 'rider'])->default('admin');
            $table->boolean('is_active')->default(true);
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['business_id', 'role']);
            $table->index(['business_id', 'is_active']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('businesses');
        Schema::dropIfExists('plans');
    }
};
