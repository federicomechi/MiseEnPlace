<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['allergens', 'equipment'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
                $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
                $table->string('name');
                $table->string('code', 64)->nullable();
                $table->string('category', 100)->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
                $table->index(['client_id', 'name']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
        Schema::dropIfExists('allergens');
    }
};
