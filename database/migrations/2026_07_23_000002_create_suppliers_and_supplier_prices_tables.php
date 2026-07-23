<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table): void {
            $table->unsignedBigInteger('filemaker_id')->nullable()->change();
            $table->string('code', 64)->nullable()->unique()->after('filemaker_id');
            $table->string('category', 80)->nullable()->index()->after('name');
        });

        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 64)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('supplier_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->string('supplier_code', 64)->nullable();
            $table->string('package_name')->nullable();
            $table->decimal('package_quantity', 12, 3)->nullable();
            $table->string('package_unit', 32)->nullable();
            $table->decimal('package_price', 12, 4);
            $table->date('valid_from');
            $table->boolean('is_current')->default(true)->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['ingredient_id', 'is_current']);
            $table->index(['supplier_id', 'valid_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_prices');
        Schema::dropIfExists('suppliers');

        Schema::table('ingredients', function (Blueprint $table): void {
            $table->dropUnique(['code']);
            $table->dropIndex(['category']);
            $table->dropColumn(['code', 'category']);
            $table->unsignedBigInteger('filemaker_id')->nullable(false)->change();
        });
    }
};
