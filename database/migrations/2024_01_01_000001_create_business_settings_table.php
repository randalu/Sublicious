<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();

            $table->unique(['business_id', 'key']);
            $table->index(['business_id', 'group']);
        });

        Schema::create('business_operating_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Sunday, 6=Saturday
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->unique(['business_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_operating_hours');
        Schema::dropIfExists('business_settings');
    }
};
