<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table): void {
            $table->unsignedBigInteger('filemaker_id')->nullable()->after('client_id');
            $table->index(['client_id', 'filemaker_id']);
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table): void {
            $table->dropIndex(['equipment_client_id_filemaker_id_index']);
            $table->dropColumn('filemaker_id');
        });
    }
};
