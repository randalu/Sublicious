<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('role')->nullable(); // Job title/role label
            $table->enum('salary_type', ['monthly', 'hourly', 'commission_only', 'daily'])->default('monthly');
            $table->decimal('salary_amount', 10, 2)->default(0);
            $table->date('hire_date')->nullable();
            $table->string('id_number')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'is_active']);
        });

        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'date']);
            $table->index(['employee_id', 'date']);
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->timestamp('in_time')->nullable();
            $table->timestamp('out_time')->nullable();
            $table->decimal('hours_worked', 5, 2)->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'leave'])->default('present');
            $table->text('notes')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['business_id', 'employee_id', 'date']);
            $table->index(['business_id', 'date']);
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category'); // Rent, Utilities, Supplies, Salaries, etc.
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->date('date');
            $table->string('receipt_image')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->index(['business_id', 'date']);
            $table->index(['business_id', 'category']);
            $table->index(['business_id', 'is_approved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('employees');
    }
};
