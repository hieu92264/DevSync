<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->metadataColumns();
            $table->timestamps();
            $table->string('code', 150)->unique();
            $table->string('name')->unique();
            $table->string('resource', 100)->nullable();
            $table->string('action', 100)->nullable();
            $table->text('remark')->nullable();
            $table->index('resource');
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
