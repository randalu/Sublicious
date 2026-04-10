<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('unit')->default('pcs'); // kg, g, L, ml, pcs
            $table->decimal('current_stock', 10, 3)->default(0);
            $table->decimal('low_stock_threshold', 10, 3)->default(0);
            $table->decimal('cost_per_unit', 10, 2)->default(0);
            $table->timestamps();

            $table->index('business_id');
        });

        Schema::create('menu_item_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity_used', 10, 3)->default(1);

            $table->unique(['menu_item_id', 'inventory_item_id']);
        });

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['restock', 'deduction', 'adjustment', 'waste']);
            $table->decimal('quantity', 10, 3);
            $table->decimal('quantity_before', 10, 3);
            $table->decimal('quantity_after', 10, 3);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['business_id', 'created_at']);
            $table->index(['inventory_item_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('menu_item_inventory');
        Schema::dropIfExists('inventory_items');
    }
};
