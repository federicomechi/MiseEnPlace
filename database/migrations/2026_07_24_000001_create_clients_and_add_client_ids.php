<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table): void {
            $table->id();
            $table->string('filemaker_id', 80)->nullable()->unique();
            $table->string('name');
            $table->string('location_id', 40)->nullable();
            $table->string('location_name')->nullable();
            $table->string('email')->nullable();
            $table->date('expires_at')->nullable();
            $table->timestamps();
        });

        foreach (['users', 'recipes', 'ingredients', 'recipe_steps', 'recipe_step_ingredients', 'suppliers', 'supplier_prices', 'development_entries', 'menu_items'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->foreignId('client_id')->nullable()->after('id')->constrained('clients')->nullOnDelete();
                $table->index('client_id');
            });
        }
    }

    public function down(): void
    {
        foreach (['users', 'recipes', 'ingredients', 'recipe_steps', 'recipe_step_ingredients', 'suppliers', 'supplier_prices', 'development_entries', 'menu_items'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->dropForeign(['client_id']);
                $table->dropIndex([$tableName.'_client_id_index']);
                $table->dropColumn('client_id');
            });
        }

        Schema::dropIfExists('clients');
    }
};
