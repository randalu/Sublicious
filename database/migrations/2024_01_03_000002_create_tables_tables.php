<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('business_id');
        });

        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('table_sections')->nullOnDelete();
            $table->string('table_number');
            $table->string('name')->nullable();
            $table->unsignedInteger('capacity')->default(4);
            $table->string('qr_code_token')->unique()->nullable();
            $table->enum('status', ['available', 'occupied', 'reserved', 'cleaning'])->default('available');
            $table->timestamps();

            $table->index(['business_id', 'status']);
            $table->unique(['business_id', 'table_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_tables');
        Schema::dropIfExists('table_sections');
    }
};
