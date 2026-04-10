<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_riders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('vehicle_type')->nullable(); // motorcycle, bicycle, car
            $table->string('vehicle_number')->nullable();
            $table->enum('commission_type', ['per_delivery', 'percentage'])->default('per_delivery');
            $table->decimal('commission_value', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('total_deliveries')->default(0);
            $table->decimal('total_commission_earned', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['business_id', 'is_active']);
        });

        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('polygon')->nullable(); // array of {lat, lng} points
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('minimum_order_amount', 10, 2)->default(0);
            $table->unsignedInteger('estimated_minutes')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['business_id', 'is_active']);
        });

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rider_id')->nullable()->constrained('delivery_riders')->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained('delivery_zones')->nullOnDelete();
            $table->text('pickup_address')->nullable();
            $table->text('delivery_address');
            $table->decimal('delivery_lat', 10, 7)->nullable();
            $table->decimal('delivery_lng', 10, 7)->nullable();
            $table->enum('status', ['pending', 'assigned', 'picked_up', 'delivered', 'failed', 'cancelled'])->default('pending');
            $table->decimal('fee', 10, 2)->default(0);
            $table->decimal('commission_earned', 10, 2)->default(0);
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status']);
            $table->index(['rider_id', 'status']);
            $table->index(['business_id', 'created_at']);
        });

        Schema::create('rider_commission_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rider_id')->constrained('delivery_riders')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedInteger('total_deliveries')->default(0);
            $table->decimal('total_commission', 10, 2)->default(0);
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'rider_id']);
            $table->index(['business_id', 'is_paid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rider_commission_payouts');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('delivery_zones');
        Schema::dropIfExists('delivery_riders');
    }
};
