<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('filemaker_id')->unique();
            $table->date('source_created_at')->nullable();
            $table->string('name');
            $table->string('print_name')->nullable();
            $table->string('tag')->nullable()->index();
            $table->decimal('yield_quantity', 12, 3)->nullable();
            $table->string('yield_unit', 32)->nullable();
            $table->decimal('multiplier_quantity', 12, 3)->nullable();
            $table->text('presentation')->nullable();
            $table->string('season')->nullable();
            $table->unsignedInteger('total_minutes')->nullable();
            $table->unsignedInteger('preparation_minutes')->nullable();
            $table->unsignedInteger('cooking_minutes')->nullable();
            $table->unsignedInteger('shelf_life_days')->nullable();
            $table->text('storage_instructions')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('filemaker_id')->unique();
            $table->string('name')->index();
            $table->string('unit', 32)->nullable();
            $table->decimal('package_quantity', 12, 3)->nullable();
            $table->decimal('unit_cost', 12, 4)->nullable();
            $table->date('cost_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('available_for_bar')->default(false);
            $table->timestamps();
        });

        Schema::create('recipe_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('filemaker_id')->unique();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->decimal('humidity', 8, 3)->nullable();
            $table->decimal('temperature', 8, 2)->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->timestamps();

            $table->index(['recipe_id', 'sort_order']);
        });

        Schema::create('recipe_step_ingredients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('filemaker_id')->unique();
            $table->foreignId('recipe_step_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 12, 3)->nullable();
            $table->string('unit', 32)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['recipe_step_id', 'ingredient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_step_ingredients');
        Schema::dropIfExists('recipe_steps');
        Schema::dropIfExists('ingredients');
        Schema::dropIfExists('recipes');
    }
};
